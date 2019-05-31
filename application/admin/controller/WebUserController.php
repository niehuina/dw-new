<?php

namespace app\admin\controller;

use app\admin\common\Cache;
use app\admin\model\Organization;
use app\admin\model\PartyMember;
use app\admin\model\Position;
use app\admin\model\Setting;
use app\admin\model\WebUser;
use app\admin\common\Constant;

class WebUserController extends BaseController
{
    public function index()
    {
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('web_user.user_name|web_user.name|web_user.id_number|web_user.phone');
        $map['web_user.deleted'] = '0';

        $order = 'CONVERT(org.name USING gbk) asc, web_user.user_type asc, CONVERT(web_user.name USING gbk) asc';
        $recordCount = db('web_user')
            ->join('organization org', 'org.id=web_user.organ_id')->where($map)->count();
        $records = db('web_user')
            ->join('organization org', 'org.id=web_user.organ_id')
            ->join('setting set', 'set.type = "user_type" and set.id=web_user.user_type', 'LEFT')
            ->where($map)
            ->field('web_user.id,web_user.user_name,web_user.password,web_user.name,web_user.id_number
                ,web_user.phone,web_user.sex,web_user.birthday,web_user.entry_time,set.value as user_type
                ,org.name as depart_name,web_user.position_ids,web_user.created_time,web_user.updated_time')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $records[$key]['sex'] = get_value($item['sex'], Constant::GENDER_LIST);
            $list = db('position')->where(['deleted' => 0, 'id' => ['in', $item['position_ids']]])->field('name')->select();
            $records[$key]['position_ids'] = implode(' | ', array_column($list, 'name'));
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
            $model = WebUser::get($id);
            $organ = Organization::get($model['organ_id']);
            if ($organ) {
                $model['organ_name'] = $organ['name'];
            } else {
                $model['organ_name'] = '';
            }
            $edit_state = true;
        }
        $this->assign('user_type_list', Setting::all(['type' => 'user_type']));
        $this->assign('position_list', Position::all(['deleted' => 0]));
        $this->assign('active_list', Constant::ACTIVE_LIST);
        $this->assign('gender_list', Constant::GENDER_LIST);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function save()
    {
        $data = input('post.');
        unset($data['node_name']);
        if ($this->is_exist('user_name', $data['user_name'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '用户名已存在'
            ));
        }
        $data['position_ids'] = implode(',', $data['position_ids']);
        if (empty($data['id'])) {
            $model = new WebUser ();
            $data['password'] = md5($data['password']);
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = WebUser::get($data['id']);
            if (empty($model)) {
                return json(array(
                    'status' => 0,
                    'message' => '记录不存在'
                ));
            }
            $data['updated_time'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->userId;
        }
        unset($data['confirmpassword']);
        $model->data($data);
        $model->save();
        Cache::clear('web_user');
        return json(array(
            'status' => 1,
            'message' => '保存成功'
        ));
    }

    public function delete()
    {
        $id = $this->request->param('id');
        $model = WebUser::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }

        $count = db('party_member')->where(['deleted'=>0,'web_user_id'=>$id])->count();
        if($count > 0){
            return json(array(
                'status' => 0,
                'message' => '该前台用户已是党员，不能删除'
            ));
        }

        $count1 = db('vacation_record')->where(['deleted'=>0,'web_user_id'=>$id])->count();
        if($count1 > 0){
            return json(array(
                'status' => 0,
                'message' => '该前台用户已有休假记录，不能删除'
            ));
        }

        $count2 = db('case_info')->where(['web_user_id'=>$id])->count();
        if($count2 > 0){
            return json(array(
                'status' => 0,
                'message' => '该前台用户已有检察官办案信息，不能删除'
            ));
        }

        $count3 = db('task_user')->where(['web_user_id'=>$id])->count();
        if($count3 > 0){
            return json(array(
                'status' => 0,
                'message' => '该前台用户已分配有任务，不能删除'
            ));
        }

        $model->deleted = 1;
        $model->deleted_user_id = $this->userId;
        $model->deleted_time = date('Y-m-d H:i:s');
        $model->save();
        Cache::clear('web_user');
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
        $list = db('web_user')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }

    public function _reset_password()
    {
        $id = $this->request->param('id');
        $model = WebUser::get($id);

        $this->assign('model', $model);
        return view();
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
        $model = WebUser::get($data['id']);
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
