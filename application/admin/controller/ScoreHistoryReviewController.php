<?php

namespace app\admin\controller;

use app\admin\common\Cache;
use app\admin\model\PartyMember;
use app\admin\model\ScoreHistory;
use app\admin\common\Constant;
use app\admin\model\ScoreItem;
use think\Db;
use think\Exception;

class ScoreHistoryReviewController extends BaseController
{
    public function index()
    {
        //$role_list = db('role')->where(['deleted'=>0])->column('name', 'id')
        $score_item_list = db('score_item')->where(['deleted' => 0])->column('id,name');
        $this->assign('score_item_list', $score_item_list);

        $review_status_list = Constant::REVIEW_STATUS;
        $this->assign('review_status_list', $review_status_list);
        $this->assign('review_status_wating',Constant::REVIEW_STATUS_WATING);
        return view();
    }

    public function get_list()
    {
        //$search_year = $this->request->param('query');
        $search_score_item = $this->request->param('search_score_item');
        $search_review_status = $this->request->param('search_review_status');

        $start = $this->request->param('start');
        $length = $this->request->param('length');

        $map = $this->process_query('wu.name');
        $map['score_history.deleted'] = '0';
        $map['year(score_history.created_time)']=date('Y');

        if(!empty($search_score_item)){
            $map['score_history.score_item_id'] = $search_score_item;
        }

        if(!empty($search_review_status)){
            $map['score_history.review_status'] = $search_review_status;
        }

        $order = 'score_history.review_status asc,score_history.review_time desc,score_history.created_time asc';

        $recordCount = db('score_history')
            ->join('score_item', 'score_item.id = score_history.score_item_id','LEFT')
            ->join('party_member pm','pm.web_user_id=score_history.web_user_id and pm.deleted=0')
            ->join('web_user wu','wu.id=pm.web_user_id and wu.deleted=0')
            ->where($map)->count();

        $records = db('score_history')->where($map)
            ->field('score_history.id,score_history.web_user_id,score_history.score_item_id,
                            score_history.review_user_id,score_history.review_time,score_history.review_status,
                            score_history.created_time,score_history.updated_time,
                            score_item.name as score_item_name,score_item.score as score_item_score,score_item.created_time as get_time,
                            wu.name as web_user_name')
            ->join('score_item', 'score_item.id = score_history.score_item_id','LEFT')
            ->join('party_member pm','pm.web_user_id=score_history.web_user_id and pm.deleted=0')
            ->join('web_user wu','wu.id=pm.web_user_id and wu.deleted=0')
            ->limit($start, $length)->order($order)->select();

        //$web_user_list = Cache::key_value('web_user');
        $admin_user_list = Cache::key_value('user');
        foreach ($records as $key => $item) {
            //$records[$key]['web_user_id'] = $web_user_list[$item['web_user_id']];
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

    public function _review()
    {
        $id = $this->request->param('id');
        $model = null;
        $edit_state = false;
        if (!empty($id)) {
            $model = ScoreHistory::get($id);
            $edit_state = true;
        }
        if($model['review_status'] == Constant::REVIEW_STATUS_WATING){
            $model['review_status'] = Constant::REVIEW_STATUS_SUCCESS;
        }
        $review_status_list = Constant::REVIEW_ACTION;
        $this->assign('review_status_list', $review_status_list);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
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

        $tf_success = false;
        try {
            if (!empty($data['id'])) {
                $model = ScoreHistory::get($data['id']);
                if (empty($model)) {
                    return json(array(
                        'status' => 0,
                        'message' => '记录不存在'
                    ));
                }
                $data['review_time'] = date('Y-m-d H:i:s');
                $data['review_user_id'] = $this->userId;

                $model->data($data);
                $model->save();

            }
            $tf_success = true;
        } catch (Exception $e) {
        }

        if ($tf_success) {
            return json(array(
                'status' => 1,
                'message' => '保存成功'
            ));
        } else {
            return json(array(
                'status' => 0,
                'message' => '保存失败'
            ));
        }
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

    //重置党员积分
    public function reset_score($web_user_id)
    {
        $status_success = Constant::REVIEW_STATUS_SUCCESS;
        $sql = "  UPDATE party_member LEFT JOIN (
                        SELECT web_user_id,SUM(si.score) AS total_score  
                        FROM score_history s
                        INNER JOIN score_item si ON s.score_item_id = si.id
                        WHERE s.web_user_id = {$web_user_id} AND s.review_status = {$status_success} AND year(s.created_time)=year(CURRENT_DATE)
                        GROUP BY web_user_id
                    ) score
                    ON party_member.web_user_id = score.web_user_id
                SET party_member.score = ifnull(score.total_score, 0)
                where party_member.web_user_id={$web_user_id}";
        return $sql;
    }
}
