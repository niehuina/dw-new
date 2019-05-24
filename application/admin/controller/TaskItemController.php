<?php

namespace app\admin\controller;

use app\admin\model\Task;
use app\admin\model\TaskItem;
use app\admin\common\Constant;
use app\admin\model\TaskUser;

class TaskItemController extends BaseController
{
    public function index()
    {
        $type_list = db('task_type')->where(['deleted'=>0])->field('id,name')->select();
        $this->assign('task_type_list',$type_list);
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('task.title|task_item.name');
        $task_type =  $this->request->param('task_type');
        if($task_type){
            $map['task.type_id'] = $task_type;
        }

        $map['task_item.deleted'] = '0';

        $order = 'type.code asc,task.publish_time desc,task_item.sort asc';
        $recordCount = db('task_item')
            ->join('task', 'task.id=task_item.task_id')
            ->join('task_type type', 'type.id=task.type_id')
            ->join('user', 'user.id=task_item.review_user_id', 'LEFT')
            ->where($map)->count();
        $records = db('task_item')
            ->join('task', 'task.id=task_item.task_id')
            ->join('task_type type', 'type.id=task.type_id')
            ->join('user', 'user.id=task_item.review_user_id', 'LEFT')
            ->where($map)
            ->field('task_item.id,type.name as type_name,task.title,task_item.name,task_item.status,
                user.name as reviewer,task_item.review_time,task_item.review_status,task_item.sort')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $records[$key]['status'] = get_value($item['status'], Constant::TASK_STATUS);
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
        $model = null;
        $edit_state = false;
        if (!empty($id)) {
            $model = TaskItem::get($id);
            $edit_state = true;
        }

        $map['deleted'] = 0;
        $task_list = Task::all($map);
        $this->assign('task_list', $task_list);
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
                'message' => '该项目已存在'
            ));
        }
        if (empty($data['id'])) {
            $model = new TaskItem ();
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = TaskItem::get($data['id']);
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
        $model = TaskItem::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }

        //$list = TaskUser::all(['task_id' => $model['task_id']]);
        $status = db('task_item')
            ->where(['task_item.id'=>$id,'task_item.deleted'=>0])->sum('task_item.status');
        if ($status > 0) {
            return json(array(
                'status' => 0,
                'message' => '该任务项目进行中或已完成，不能删除'
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
        $where['name'] = $key;
        $where['deleted'] = 0;
        if (!empty($id)) {
            $where['id'] = array('<>', $id);
        }
        $list = db('task_item')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }
}
