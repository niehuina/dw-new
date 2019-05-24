<?php

namespace app\admin\controller;

use app\admin\common\Cache;
use app\admin\model\ScoreItem;
use app\admin\common\Constant;

class ScoreItemController extends BaseController
{
    public function index()
    {
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('name');
        $map['score_item.deleted'] = '0';

        $order = 'score_item.id asc';
        $recordCount = db('score_item')->where($map)->count();
        $records = db('score_item')->where($map)
            ->field('score_item.id,score_item.name,score_item.score')
            ->limit($start, $length)->order($order)->select();

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
        $model = null;
        $edit_state = false;
        if (!empty($id)) {
            $model = ScoreItem::get($id);
            $edit_state = true;
        }
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function save()
    {
        $data = input('post.');
        if ($this->is_exist($data['name'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '积分项目名称已存在'
            ));
        }
        if (empty($data['id'])) {
            $model = new ScoreItem ();
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = ScoreItem::get($data['id']);
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
        Cache::clear("score_item");
        return json(array(
            'status' => 1,
            'message' => '保存成功'
        ));
    }

    public function delete()
    {
        $id = $this->request->param('id');
        $model = ScoreItem::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }

        $count = db('score_history')->where(['deleted'=>0,'score_item_id'=>$id])->count();
        if($count > 0){
            return json(array(
                'status' => 0,
                'message' => '该积分项目下有相关联的积分记录，不能删除'
            ));
        }

        $model->deleted = 1;
        $model->deleted_user_id = $this->userId;
        $model->deleted_time = date('Y-m-d H:i:s');
        $model->save();
        Cache::clear("score_item");
        return json(array(
            'status' => 1,
            'message' => '删除成功'
        ));
    }

    private function is_exist($key, $id = '')
    {
        $where['name'] = $key;
        $where['deleted'] = 0;
        if (!empty($id)) {
            $where['id'] = array('<>', $id);
        }
        $list = db('score_item')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }
}
