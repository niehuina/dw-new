<?php

namespace app\admin\controller;

use app\admin\model\TaskType;
use app\admin\common\Constant;

class TaskTypeController extends BaseController
{
    public function index()
    {
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('t.name');
        $map['t.deleted'] = '0';

        $order = 't.code asc';
        $recordCount = db('task_type t')
            ->join('task_type pt', 'pt.id=t.parent_id', 'LEFT')
            ->where($map)->count();
        $records = db('task_type t')->where($map)
            ->join('task_type pt', 'pt.id=t.parent_id', 'LEFT')
            ->field('t.id,t.code,t.name,t.parent_id,pt.name as p_name')
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
            $model = TaskType::get($id);
            $edit_state = true;
        }
        $map['deleted'] = 0;
        if (!empty($id)) {
            $map['id'] = ['<>', $id];
        }
        $task_type_list = TaskType::all($map);
        $this->assign('task_type_list', $task_type_list);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function save()
    {
        $data = input('post.');
        if ($this->is_exist('code', $data['code'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '分类编号已存在'
            ));
        }
        if ($this->is_exist('name', $data['name'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '分类名称已存在'
            ));
        }
        if (empty($data['id'])) {
            $model = new TaskType ();
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = TaskType::get($data['id']);
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
        $model = TaskType::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }

        $count = db('task_type')->where(['deleted'=>0,'parent_id'=>$id])->count();
        if($count > 0){
            return json(array(
                'status' => 0,
                'message' => '该分类下有相关子分类，不能删除'
            ));
        }

        $count = db('task')->where(['type_id'=>$id,'deleted'=>0])->count();
        if($count > 0){
            return json(array(
                'status' => 0,
                'message' => '该分类下有相关联任务，不能删除'
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

    private function is_exist($key, $value, $id = '')
    {
        $where[$key] = $value;
        $where['deleted'] = 0;
        if (!empty($id)) {
            $where['id'] = array('<>', $id);
        }
        $list = db('task_type')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }
}
