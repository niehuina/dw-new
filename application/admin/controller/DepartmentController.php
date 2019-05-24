<?php


namespace app\admin\controller;


use app\admin\model\Organization;
use app\admin\model\WebUser;

class DepartmentController extends BaseController
{
    public static $type = 1;//部门组织机构

    public function index()
    {
        return view();
    }

    public function getOrganTree()
    {
        $data = array();

        $first['id'] = 0;
        $first['parent'] = "#";
        $first['text'] = "部门组织机构";
        $first['type'] = "dep";
        $first['state'] = ['opened'=>true];
        $data[] = $first;

        $organ_map['deleted'] = 0;
        $organ_map['type'] = self::$type;
        $organ_list = Organization::all($organ_map);
        foreach ($organ_list as $key => $item) {
            $arr['id'] = $item['id'];
            $arr['parent'] = $item['parent_id'];
            $arr['text'] = $item['name'];
            $arr['type'] = "dep";
            $arr['state'] = ['opened'=>true];
            $data[] = $arr;
        }

        return json($data);
    }

    public function _item_maintain()
    {
        $id = $this->request->param('id');

        $model = null;
        $model['type'] = self::$type;

        $edit_state = false;
        $organ_map = null;
        if (!empty($id)) {
            $model = Organization::get($id);
            $parent_model = Organization::get($model['parent_id']);
            $model['parent_name'] = $parent_model['name'];
            $edit_state = true;
        } else {
            $parent_id = $this->request->param('parent_id');
            $parent_model = Organization::get($parent_id);
            $model['parent_id'] = $parent_id;
            $model['parent_name'] = $parent_model['name'];
        }

        $organ_map['deleted'] = 0;
        $organ_map['type'] = self::$type;
        if (!empty($id)) {
            $organ_map['id'] = ['<>', $id];
        }
        $organ_list = Organization::all($organ_map);
        $this->assign('organ_list', $organ_list);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function save()
    {
        $data = input('post.');
        if ($this->is_exist('number', $data['number'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '组织机构编号已存在'
            ));
        }
        if ($this->is_exist('name', $data['name'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '组织机构名称已存在'
            ));
        }
        if (empty($data['id'])) {
            $model = new Organization ();
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = Organization::get($data['id']);
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
        $model = Organization::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }

        $organ_map['deleted'] = 0;
        $organ_map['parent_id'] = $id;
        $organ_list = Organization::all($organ_map);
        if(count($organ_list)){
            return json(array(
                'status' => 0,
                'message' => '请先删除子节点'
            ));
        }

        $p_map['deleted'] = 0;
        $p_map['organ_id'] = $id;
        $w_user = WebUser::all($p_map);
        if(count($w_user) > 0){
            return json(array(
                'status' => 0,
                'message' => '该组织机构下有关联的前台用户，不能删除'
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
        $where['type'] = self::$type;
        if (!empty($id)) {
            $where['id'] = array('<>', $id);
        }
        $list = db('organization')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }
}