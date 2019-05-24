<?php

namespace app\admin\controller;

use app\admin\common\Common;
use app\admin\common\Constant;
use app\admin\model\Permission;
use app\admin\model\Role;
use app\admin\model\User;

class IndexController extends BaseController
{
    public function unauthorized()
    {
        return view();
    }

    public function index()
    {
        return view();
    }

    public function login()
    {
        if ($_POST) {
            $data = input('post.');

            if (empty($data['user_name'])) {
                return json(array(
                    'status' => 0,
                    "message" => '请输入用户名'
                ));
            }
            if (empty($data['password'])) {
                return json(array(
                    'status' => 0,
                    "message" => '请输入密码'
                ));
            }
            $model = User::get(['user_name' => $data['user_name']]);
            if (empty($model)) {
                return json(array(
                    'status' => 0,
                    "message" => '用户不存在'
                ));
            }
            if ($model['password'] != md5($data['password'])) {
                return json(array(
                    'status' => 0,
                    "message" => '密码不正确'
                ));
            }
            if (!($model['active'] == 1)) {
                return json(array(
                    'status' => 0,
                    "message" => '用户已被禁用'
                ));
            }
            $role = Role::get(['deleted'=>0,'id'=>$model['role_id']]);
            if (!$role) {
                return json(array(
                    'status' => 0,
                    "message" => '用户角色不存在，不能登录'
                ));
            }
            $permission_list = Permission::where(['role_id' => $model->role_id])->column('name');
            if ($model['role_id'] != Constant::ROLE_ADMIN && empty($permission_list)) {
                return json(array(
                    'status' => 0,
                    "message" => "没有授权"
                ));
            } else {
                $permission['menu_permission'] = $permission_list;
                foreach ($permission_list as $key => $value) {
                    $permission_list[$key] = str_replace('system.', '', $value);
                }
                session(Constant::SESSION_USER_PERMISSION, $permission);
                session(Constant::SESSION_USER_ID, $model['id']);
                session(Constant::SESSION_USER_ROLES, $model['role_id']);
                $url = $model['role_id'] != Constant::ROLE_ADMIN ? $permission_list[0] : 'index';
                return json(array(
                    'status' => 1,
                    "message" => "登录成功",
                    'url' => $url
                ));
            }
        }
        return view();
    }

    public function logout()
    {
        session('user_id', null);
        $this->redirect(url('index/login'));
    }

}
