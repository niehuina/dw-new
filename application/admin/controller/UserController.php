<?php

namespace app\admin\controller;

use app\admin\common\Cache;
use app\admin\common\Constant;
use app\admin\model\College;
use app\admin\model\User;
use app\admin\model\Role;
use think\Db;
use think\Exception;

class UserController extends BaseController
{
    public function index()
    {
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('user.user_name|user.name');
        $map['user.deleted'] = '0';

        $order = 'CONVERT(user.user_name USING gbk) asc';
        $recordCount = db('user')
            ->join('role','role.id=user.role_id','left')
            ->where($map)->count();

        $records = db('user')
            ->join('role','role.id=user.role_id and role.deleted=0','left')
            ->where($map)
            ->field('user.id,user.user_name,user.name,user.created_time,user.created_user_id,user.active,role.name as role_name')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $records[$key]['active_name'] = get_value($item['active'], Constant::ACTIVE_LIST);
            //$records[$key]['role_ids'] = get_value($item['role_ids'], $role_list);
        }
        return json(array(
            'draw' => $this->request->param('draw'),
            "recordsTotal" => $recordCount,
            "recordsFiltered" => $recordCount,
            "data" => $records
        ));
    }

    public function _item_maintain()
    {
        $id = $this->request->param('id');
        $model = null;
        $edit_state = false;
        if (!empty($id)) {
            $model = User::get($id);
            $edit_state = true;
        }
        $map['deleted'] = 0;
        $role_list = Role::all($map);

        $this->assign('active_list', Constant::ACTIVE_LIST);
        $this->assign('role_list', $role_list);
        $this->assign('edit_state', $edit_state);
        $this->assign('model', $model);
        return view();
    }

    public function _reset_password()
    {
        $id = $this->request->param('id');
        $model = User::get($id);

        $this->assign('model', $model);
        return view();
    }

    public function _reset_role()
    {
        $id = $this->request->param('id');
        $model = User::get($id);
        $role_list = Role::all(['deleted'=>0]);

        $this->assign('role_list', $role_list);
        $this->assign('model', $model);
        return view();
    }

    public function reset_role(){
        $data = input('post.');

        $model = User::get($data['id']);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                "message" => "用户不存在"
            ));
        }

        $model->role_id = $data['role_id'];
        $model->save();

        return json(array(
            'status' => 1,
            "message" => "修改成功"
        ));
    }

    public function save()
    {
        $data = input('post.');
        if ($this->is_exist($data['user_name'], $data['id'])) {
            return json(array(
                'status' => 0,
                "message" => '用户名已存在'
            ));
        }
        if (empty($data['id'])) {
            $model = new User();
            $data['password'] = md5($data['password']);
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = User::get($data['id']);
            $data['updated_user_id'] = $this->userId;
            $data['updated_time'] = date('Y-m-d H:i:s');
        }

        unset($data['confirmpassword']);
        $model->data($data)->save();
        Cache::clear('user');
        return json(array(
            'status' => 1,
            "message" => "保存成功"
        ));
    }

    public function disabled(){
        $id = $this->request->param('id');
        $model = User::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                "message" => "用户不存在"
            ));
        }
        if($id == session(Constant::SESSION_USER_ID)){
            return json(array(
                'status' => 0,
                "message" => "不能禁用本人账号"
            ));
        }
        $model->active = 0;

        $model->save();

        Cache::clear('user');
        return json(array(
            'status' => 1,
            "message" => "禁用成功"
        ));
    }

    public function enabled(){
        $id = $this->request->param('id');
        $model = User::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                "message" => "用户不存在"
            ));
        }
        if($id == session(Constant::SESSION_USER_ID)){
            return json(array(
                'status' => 0,
                "message" => "不能启用本人账号"
            ));
        }
        $model->active = 1;

        $model->save();

        Cache::clear('user');
        return json(array(
            'status' => 1,
            "message" => "启用成功"
        ));
    }

    public function delete()
    {
        $id = $this->request->param('id');
        $model = User::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                "message" => "用户不存在"
            ));
        }
        if($id == session(Constant::SESSION_USER_ID)){
            return json(array(
                'status' => 0,
                "message" => "不能删除本人账号"
            ));
        }
        $model->deleted = 1;
        $model->deleted_user_id = $this->userId;
        $model->deleted_time = date('Y-m-d H:i:s');

        $model->save();

        Cache::clear('user');
        return json(array(
            'status' => 1,
            "message" => "删除成功"
        ));
    }

    private function is_exist($key, $id = '')
    {
        $where['user_name'] = $key;
        $where['deleted'] = 0;
        if (!empty($id)) {
            $where['id'] = array('<>', $id);
        }
        $list = db('user')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }

    public function reset_password()
    {
        $data = input('post.');

        if (empty($data['id'])) {
            return json(array(
                'status' => 0,
                "message" => '用户不存在'
            ));
        }
        if (empty($data['password'])) {
            return json(array(
                'status' => 0,
                "message" => '请输入密码'
            ));
        }
        $model = User::get($data['id']);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                "message" => "用户不存在"
            ));
        }
        $model->password = md5($data['password']);
        $model->save();

        return json(array(
            'status' => 1,
            "message" => "修改成功"
        ));
    }
}
