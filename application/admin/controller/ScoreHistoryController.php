<?php

namespace app\admin\controller;

use app\admin\common\Cache;
use app\admin\model\ScoreHistory;
use app\admin\common\Constant;
use think\Db;
use think\Request;

class ScoreHistoryController extends BaseController
{
    public function index()
    {
        $score_item_list = db('score_item')->where(['deleted' => 0])->column('id,name');
        $this->assign('score_item_list', $score_item_list);

        $this->assign('web_user_id', $_GET['web_user_id']);
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $search_score_item = $this->request->param('search_score_item');
        $web_user_id = $this->request->param('web_user_id');
        //$map = $this->process_query('name|true_name');
        if(!empty($search_score_item)){
            $map['score_history.score_item_id'] = $search_score_item;
        }
        $map['score_history.deleted'] = '0';
        $map['web_user_id'] = $web_user_id;
        $map['year(score_history.created_time)']=date('Y');

        $order = 'CONVERT(wu.name USING gbk) asc';
        $recordCount = db('score_history')->where($map)->count();
        $records = db('score_history')
            ->join('web_user wu', 'wu.deleted=0 and wu.id=score_history.web_user_id', 'left')
            ->where($map)
            ->field('score_history.id,score_history.web_user_id,score_history.score_item_id,
                        score_history.review_user_id,score_history.review_time,score_history.review_status,
                        score_history.created_time as get_time,score_history.updated_time, wu.name as web_user_name,
                        score_item.name as score_item_name,score_item.score as score_item_score,score_item.created_time as score_item_created_time')
            ->join('score_item', 'score_item.id = score_history.score_item_id','LEFT')
            ->limit($start, $length)->order($order)->select();

        $admin_user_list = Cache::key_value('user');
        foreach ($records as $key => $item) {
            if (!empty($item['review_user_id'])) {
                $records[$key]['review_user_id'] = $admin_user_list[$item['review_user_id']];
            }
            $records[$key]['review_status_name'] = get_value($item['review_status'], Constant::REVIEW_STATUS);
        }

        return json(array(
            'draw' => $this->request->param('draw'),
            'recordsTotal' => $recordCount,
            'recordsFiltered' => $recordCount,
            'data' => $records
        ));
    }

    public function _item_maintain()
    {
        $id = $this->request->param('id');
        $web_user_id = $this->request->param('web_user_id');
        $model = null;
        $edit_state = false;
        $web_user_list = Cache::key_value('web_user');
        if (!empty($id)) {
            $model = ScoreHistory::get($id);
            $edit_state = true;
        } else if (!empty($web_user_id)) {
            $model['web_user_id'] = $web_user_id;
        }

        $score_item_list = Cache::key_value('score_item');
        $model['web_user_name'] = get_value($model['web_user_id'],$web_user_list);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        $this->assign('score_item_list', $score_item_list);
        return view();
    }

    public function save()
    {
        $data = input('post.');
        if ($this->is_exist($data['id'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '记录重复'
            ));
        }
        if (empty($data['id'])) {
            $model = new ScoreHistory ();
            $data['review_status'] = Constant::REVIEW_STATUS_WATING;
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = ScoreHistory::get($data['id']);
            if (empty($model)) {
                return json(array(
                    'status' => 0,
                    'message' => '记录不存在'
                ));
            }
            $data['updated_time'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->userId;
        }
        $model->data($data);
        $model->save();
        return json(array(
            'status' => 1,
            'message' => '保存成功'
        ));
    }

    public function delete()
    {
        $id = $this->request->param('id');
        $model = ScoreHistory::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }
        $model->deleted = 1;
        $model->deleted_user_id = $this->userId;
        $model->deleted_time = date('Y-m-d H:i:s');
        $model->save();
        return json(array(
            'status' => 1,
            'message' => '删除成功'
        ));
    }

    private function is_exist($key, $id = '')
    {
        return false;
        $where['key'] = $key;
        $where['deleted'] = 0;
        if (!empty($id)) {
            $where['id'] = array('<>', $id);
        }
        $list = db('score_history')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }
}
