<?php

namespace app\admin\controller;

use app\admin\model\Notification;
use app\admin\model\Organization;
use app\admin\model\Task;
use app\admin\common\Constant;
use app\admin\model\TaskItem;
use app\admin\model\TaskType;
use app\admin\model\TaskUser;
use app\admin\model\WebUser;
use think\Db;

class TaskController extends BaseController
{
    public function index()
    {
        $type_list = db('task_type')->where(['deleted' => 0])->field('id,name')->select();
        $this->assign('task_type_list', $type_list);
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('task.title');
        $task_type = $this->request->param('task_type');
        if ($task_type) {
            $map['task.type_id'] = $task_type;
        }

        $map['task.deleted'] = '0';

        $order = 'task.publish_time desc';
        $recordCount = db('task')
            ->join('task_type type', 'type.id=task.type_id')
            ->join('user', 'user.id=task.publish_user_id')->where($map)->count();
        $records = db('task')
            ->join('task_type type', 'type.id=task.type_id')
            ->join('user', 'user.id=task.publish_user_id')
            ->where($map)
            ->field('task.id,type.name as type_name,task.publish_time,task.title,task.end_time, task.status
                ,user.name as pub_user_name')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $records[$key]['status_name'] = get_value($item['status'], Constant::TASK_STATUS);
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
            $model = Task::get($id);
            $to_user_list = db('web_user')->where(['id' => ['in', $model['to_user_id']]])->column('name', 'id');
            $model['to_user_name'] = implode(',', array_values(array_column($to_user_list, 'name')));

            switch ($model['to_user_type']) {
                case 'all':
                    break;
                case 'dep':
                    $dep = Organization::where(['id' => ['in', explode(',', $model['to_type_id'])]])->column('name');
                    $model['dep_organ_name'] = implode(', ', $dep);
                    $model['to_dep_ids'] = $model['to_type_id'];
                    break;
                case 'party':
                    $party = Organization::where(['id' => ['in', explode(',', $model['to_type_id'])]])->column('name');
                    $model['organ_name'] = implode(', ', $party);
                    $model['to_organ_ids'] = $model['to_type_id'];
                    break;
                case 'user':
                    $to_user_list = WebUser::where(['id' => ['in', $model['to_user_id']]])->column('name', 'id');
                    $model['to_user_name'] = implode(',', $to_user_list);
                    break;
            }

            $edit_state = true;
        } else {
            $model['to_user_id'] = '';
            $model['to_dep_ids'] = '';
            $model['to_organ_ids'] = '';
            $model['to_user_type'] = '';
        }
        $task_type_list = TaskType::all(['deleted' => 0]);
        $this->assign('task_type_list', $task_type_list);
        $this->assign('to_user_type_list', Constant::TO_USER_TYPE);
        //$to_user_list = db('web_user')->where(['id' => ['in', $model['to_user_id']]])->field('id,name')->select();
        $user_list = db('web_user')->where(['deleted' => 0])->column('name', 'id');
        $this->assign('to_user_list', $user_list);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function save()
    {
        $data = input('post.');
        if ($this->is_exist($data['title'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '任务名称已存在'
            ));
        }

        Db::startTrans();
        try {
            if (empty($data['id'])) {
                $model = new Task ();
                $data['deleted'] = 0;
                $data['publish_user_id'] = $this->userId;
                $data['created_user_id'] = $this->userId;
                $data['created_time'] = date('Y-m-d H:i:s');
            } else {
                $model = Task::get($data['id']);
                if (empty($model)) {
                    return json(array(
                        'status' => 0,
                        'message' => '记录不存在'
                    ));
                }
                $data['updated_time'] = date('Y-m-d H:i:s');
                $data['updated_user_id'] = $this->userId;
            }

            $to_user_data['to_user_type'] = empty($data['to_user_type']) ? '' : $data['to_user_type'];
            $to_user_data['to_dep_id'] = empty($data['to_dep_id']) ? '' : $data['to_dep_id'];
            $to_user_data['to_organ_id'] = empty($data['to_organ_id']) ? '' : $data['to_organ_id'];
            $to_user_data['to_user_id'] = empty($data['to_user_id']) ? '' : implode(",", $data['to_user_id']);
            $to_user_data['node_name'] = empty($data['node_name']) ? '' : $data['node_name'];
            $to_user_data['dep_node_name'] = empty($data['dep_node_name']) ? '' : $data['dep_node_name'];

            unset($data['to_dep_id']);
            unset($data['to_organ_id']);
            unset($data['to_user_id']);
            unset($data['node_name']);
            unset($data['dep_node_name']);

            $model->data($data);
            $model->save();

            if(empty($data['id'])){
                //保存分配用户表
                $to_user_data['id'] = $model['id'];
                $this->save_to_user_data($to_user_data);
            }

            Db::commit();
            return json(array(
                'id'=>$model['id'],
                'status' => 1,
                'message' => '任务保存成功'
            ));
        } catch (\Exception $e) {
            Db::rollback();
            return api_exception($e);
        }
    }

    public function get_task_item_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('task_item.name');
        $task_id = $this->request->param('task_id');
        $map['task_item.task_id'] = $task_id;
        $map['task_item.deleted'] = '0';

        $order = 'task_item.sort asc';
        $recordCount = db('task_item')
            ->where($map)->count();
        $records = db('task_item')
            ->where($map)
            ->field('task_item.id,task_item.name,task_item.sort')
            ->limit($start, $length)->order($order)->select();

        return json(array(
            'draw' => $this->request->param('draw'),
            'recordsTotal' => $recordCount,
            'recordsFiltered' => $recordCount,
            'data' => $records
        ));
    }

    public function add_item()
    {
        $data = input('post.');
        if ($this->is_task_item_exist($data['name'], $data['id'],$data['task_id'])) {
            return json(array(
                'status' => 0,
                'message' => '该项目已存在'
            ));
        }
        if (empty($data['id'])) {
            Db::startTrans();
            try {
                $model = new TaskItem ();
                $data['deleted'] = 0;
                $data['created_user_id'] = $this->userId;
                $data['created_time'] = date('Y-m-d H:i:s');
                $model->data($data);
                $model->save();

                $task_item_users = [];
                $web_user_list = Db('task_user')->where(['task_id' => $model['task_id']])->column('web_user_id');
                foreach ($web_user_list as $key=>$web_user_id)
                {
                    $array = [];
                    $array['task_id'] = $model['task_id'];
                    $array['task_item_id'] = $model['id'];
                    $array['web_user_id'] = $web_user_id;
                    $task_item_users[] = $array;
                }
                //如果任务已经分配，新增项目时会添加task_item_user
                if(count($task_item_users) > 0) {
                    Db('task_item_user')->insertAll($task_item_users);
                }

                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return api_exception($e);
            }
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
            $model->data($data);
            $model->save();
        }
        return json(array(
            'status' => 1,
            'message' => '保存成功'
        ));
    }

    private function is_task_item_exist($task_item_name, $id = '',$task_id)
    {
        $where['name'] = $task_item_name;
        $where['deleted'] = 0;
        $where['task_id'] = $task_id;
        if (!empty($id)) {
            $where['id'] = array('<>', $id);
        }
        $list = db('task_item')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }

    public function delete_item()
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
        $status = db('task_item_user')
            ->where(['task_item_id'=>$id,'task_id'=>$model['task_id']])->sum('status');
        if ($status > 0) {
            return json(array(
                'status' => 0,
                'message' => '该任务项目进行中或已完成，不能删除'
            ));
        }

        Db::startTrans();
        try {
            $model->deleted = 1;
            $model->deleted_user_id = $this->userId;
            $model->deleted_time = date('Y-m-d H:i:s');
            $model->save();

            //删除项目分配给的用户
            Db('task_item_user')->where(['task_id' => $model['task_id'], 'task_item_id' => $id])->delete();

            Db::commit();
            return json(array(
                'status' => 1,
                'message' => '删除成功'
            ));
        } catch (\Exception $e) {
            Db::rollback();
            return api_exception($e);
        }
    }

    public function _item_to_user()
    {
        $id = $this->request->param('id');
        $model = null;
        if (!empty($id)) {
            $model = Task::get($id);
            switch ($model['to_user_type']) {
                case 'all':
                    break;
                case 'dep':
                    $dep = Organization::where(['id' => ['in', explode(',', $model['to_type_id'])]])->column('name');
                    $model['dep_organ_name'] = implode(', ', $dep);
                    $model['to_dep_ids'] = $model['to_type_id'];
                    break;
                case 'party':
                    $party = Organization::where(['id' => ['in', explode(',', $model['to_type_id'])]])->column('name');
                    $model['organ_name'] = implode(', ', $party);
                    $model['to_organ_ids'] = $model['to_type_id'];
                    break;
                case 'user':
                    $to_user_list = WebUser::where(['id' => ['in', $model['to_user_id']]])->column('name', 'id');
                    $model['to_user_name'] = implode(',', $to_user_list);
                    break;
            }
        } else {
            $model['to_dep_ids'] = '';
            $model['to_organ_ids'] = '';
            $model['to_user_id'] = '';
        }
        $user_list = db('web_user')->where(['deleted' => 0])->column('name', 'id');
        $task_type_list = TaskType::all(['deleted' => 0]);
        $this->assign('task_type_list', $task_type_list);
        $this->assign('to_user_type_list', Constant::TO_USER_TYPE);
        $this->assign('to_user_list', $user_list);
        $this->assign('model', $model);
        return view();
    }

    public function save_to_user()
    {
        $data = input('post.');
        $model = Task::get($data['id']);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }

        Db::startTrans();
        try {
            $this->save_to_user_data($data);
            Db::commit();
            return json(array(
                'status' => 1,
                'message' => '任务分配成功'
            ));
        } catch (\Exception $e) {
            Db::rollback();
            return api_exception($e);
        }
    }

    public function save_to_user_data($data = null)
    {
        $model = Task::get($data['id']);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }

        $model['to_user_type'] = $data['to_user_type'];
        if ($data['to_user_type']) {
            $list = [];
            switch ($data['to_user_type']) {
                case 'all':
                    $model['to_type_id'] = null;
                    $model['to_user_id'] = null;
                    $list = db('web_user')->where(['deleted' => 0])
                        ->column('id,user_name,name,phone', 'id');
                    break;
                case 'dep':
                    $dep_id = $data['to_dep_id'];
                    $model['to_type_id'] = $dep_id;
                    $model['to_user_id'] = null;
                    $list = db('web_user')->where(['deleted' => 0, 'organ_id' => ['in', explode(',', $dep_id)]])
                        ->column('id,user_name,name,phone', 'id');
                    break;
                case 'party':
                    $organ_id = $data['to_organ_id'];
                    $model['to_type_id'] = $organ_id;
                    $model['to_user_id'] = null;
                    $list = db('party_member')->where(['deleted' => 0, 'organ_id' => ['in', explode(',', $organ_id)]])
                        ->column('web_user_id,organ_id', 'web_user_id');
                    break;
                case 'user':
                    $model['to_type_id'] = null;
                    $list = db('web_user')->where(['deleted' => 0, 'id' => ['in', $data['to_user_id']]])
                        ->column('id,user_name,name,phone', 'id');
                    $model['to_user_id'] = is_array($data['to_user_id']) ? implode(',', $data['to_user_id']) : $data['to_user_id'];
                    break;
            }

            try {
                $result = $model->save();

                $task_id = $model['id'];
                $task_users = [];
                $task_item_users = [];
                $notifications = [];
                foreach ($list as $key => $item) {
                    $array = [];
                    $array['noti_type'] = 'task';
                    $array['web_user_id'] = $key;
                    $array['subject'] = "任务分配消息";
                    $array['content'] = "接收到任务：{$model['title']}，任务结束时间为{$model['end_time']}，请尽快到 <a href=\"/home/user/information.html\">个人中心</a> --> <a href=\"/home/user/task.html\">任务完成情况</a> 功能下进行操作处理。";
                    $array['task_id'] = $task_id;
                    $array['created_user_id'] = $this->userId;
                    $notifications[] = $array;

                    $arr_user = [];
                    $arr_user['task_id'] = $task_id;
                    $arr_user['web_user_id'] = $key;
                    $task_users[] = $arr_user;

                    $array_item = [];
                    $array_item['task_id'] = $task_id;
                    $array_item['web_user_id'] = $key;
                    $task_item_users[] = $array_item;
                }
                $task_items = Db('task_item')->where(['task_id' => $task_id, 'deleted'=>0])->field('id')->select();
                if(count($task_items) > 0){
                    foreach ($task_items as $item){
                        foreach ($task_item_users as $key=>$item_user){
                            $task_item_users[$key]['task_item_id'] = $item['id'];
                        }
                    }
                }else{
                    $task_item_users = [];
                }
                Db('notification')->where(['task_id' => $task_id])->delete();
                Db('task_user')->where(['task_id' => $task_id])->delete();
                Db('task_item_user')->where(['task_id' => $task_id])->delete();
                //添加notifications
                if(count($notifications) > 0) {
                    Db::table('notification')->insertAll($notifications);
                }
                //添加task_user
                if(count($task_users) > 0) {
                    Db::table('task_user')->insertAll($task_users);
                }
                //添加task_item_user
                if(count($task_item_users) > 0) {
                    Db('task_item_user')->insertAll($task_item_users);
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }else{
            TaskUser::where(['task_id' => $model->id])->delete();
        }
    }

    public function delete()
    {
        $id = $this->request->param('id');
        $model = Task::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }

        $list = TaskItem::all(['deleted' => 0, 'task_id' => $id]);
        if (count($list) > 0) {
            return json(array(
                'status' => 0,
                'message' => '该任务下关联有任务项目，不能删除'
            ));
        }

        $status = db('task_user')
            ->join('task_item', 'task_user.task_id=task_item.task_id and task_item.deleted=0')
            ->where(['task_user.task_id'=>$id])->sum('task_item.status');

        //$list = TaskUser::all(['task_id' => $id]);
        if ($status > 0) {
            return json(array(
                'status' => 0,
                'message' => '该任务已分配并有任务项目正在进行中或已完成，不能删除'
            ));
        }else{
            TaskUser::where(['task_id' => $id])->delete();
            TaskItem::where(['task_id' => $id])->delete();
        }

        $model->deleted = 1;
        $model->deleted_user_id = $this->userId;
        $model->deleted_time = date('Y-m-d H:i:s');

        $del_result1 = Notification::destroy(['task_id' => $model['id']]);
        $del_result2 = TaskUser::destroy(['task_id' => $model['id']]);

        $model->save();
        return json(array(
            'status' => 1,
            'message' => '删除成功'
        ));
    }

    private function is_exist($key, $id = '')
    {
        $where['title'] = $key;
        $where['deleted'] = 0;
        if (!empty($id)) {
            $where['id'] = array('<>', $id);
        }
        $list = db('task')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }

    public function get_web_users()
    {
        $query = $this->request->param('filter');
        $start = $this->request->param('skipCount');
        $length = $this->request->param('maxResultCount');
        $map['name'] = ['like', '%' . $query . '%'];
        $result_count = db('web_user')->where($map)->count();
        $result = db('web_user')->where($map)
            ->limit($start, $length)
            ->field('id,name')
            ->select();
        return json(['total_count' => $result_count, 'items' => $result]);
    }
}
