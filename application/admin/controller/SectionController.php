<?php

namespace app\admin\controller;

use app\admin\model\Role;
use app\admin\model\Section;
use app\admin\common\Constant;
use app\admin\model\SectionRoles;
use think\Model;

class SectionController extends BaseController
{
    public function index()
    {
        $tf_show_index_list=Constant::YES_NO;
        $section_list = Section::all(['parent_id' => 0]);
        $first_section=array(array("id"=>0,"name"=>"一级栏目"));
        $section_list=array_merge($first_section,$section_list);
        $this->assign('section_list', $section_list);
        $this->assign('tf_show_index_list', $tf_show_index_list);
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $parent_id = $this->request->param('parent_id');
        $tf_show_index = $this->request->param('tf_show_index');
        $map = $this->process_query('name');
        $map['section.deleted'] = '0';

        if ($parent_id!="") {
            $map['section.parent_id'] = $parent_id;
        }

        if(in_array($tf_show_index,['0','1'])){
            $map['section.tf_show_index'] = $tf_show_index;
        }

        $order = 'parent_id,sort asc';
        $recordCount = db('section')->where($map)->count();
        $records = db('section')->where($map)
            ->field('section.id,section.parent_id,section.name,section.tf_show_index,section.sort,section.url,section.role_ids')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $list = db('section')->where(['deleted' => 0, 'id' => $item['parent_id']])->field('name')->select();
            $records[$key]['parent_id'] = array_column($list, 'name');
            $records[$key]['tf_show_index'] = $item['tf_show_index'] == 0 ? '否' : '是';
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
        $section_map = null;

        if (!empty($id)) {
            $model = Section::get($id);
            $edit_state = true;
        }

        $section_map['deleted'] = 0;
        $section_map['parent_id'] = 0;
        $section_map['id'] = ['not in', '2,3,4,5,7'];
        if (!empty($id)) {
            $section_map['id'] = ['<>', $id];
        }
        $section_list = Section::all($section_map);

        $yn_list = Constant::YES_NO;

        $default_yn=0;
        if(!empty($model)){
            $default_yn=$model['tf_show_index'];
        }

        $this->assign('yn_list', $yn_list);
        $this->assign('section_list', $section_list);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        $this->assign('default_yn', $default_yn);
        return view();
    }

    public function save()
    {
        $data = input('post.');
        if ($this->is_exist($data['name'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '栏目名称已存在'
            ));
        }
        if (empty($data['id'])) {
            $model = new Section ();
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = Section::get($data['id']);
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

    public function _set_roles()
    {
        $id = $this->request->param('id');
        $model = null;
        $edit_state = false;
        $section_map = null;

        $section_roles = null;
        if (!empty($id)) {
            $model = Section::get($id);
            $selected_roles = SectionRoles::where(['section_id' => $id])->column('role_id');
            $selected_roles = implode(',', $selected_roles);
            $edit_state = true;
        }
        $role_list = Role::where(['deleted' => 0])->column('id,name','id');

        $this->assign('role_list', $role_list);
        $this->assign('selected_roles', $selected_roles);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function save_roles()
    {
        $data = input('post.');

        $roles = array();
        if (!empty($data['roles'])) {
            $roles = $data['roles'];
            unset($data['roles']);
        }

        SectionRoles::where(['section_id' => $data['section_id']])->delete();
        foreach ($roles as $m) {
            $model = new SectionRoles();
            $data['section_id'] = $data['section_id'];
            $data['role_id'] = $m;
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');

            $model->data($data);
            $model->save();
        }

        return json(array(
            'status' => 1,
            'message' => '保存成功'
        ));
    }


    public function delete()
    {
        $id = $this->request->param('id');
        $model = Section::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }

        $count = db('section')->where(['deleted'=>0,'parent_id'=>$id])->count();
        if($count > 0){
            return json(array(
                'status' => 0,
                'message' => '该信息栏目下有相关子栏目，不能删除'
            ));
        }

        $count1 = db('section_info')->where(['deleted'=>0,'section_id'=>$id])->count();
        if($count1 > 0){
            return json(array(
                'status' => 0,
                'message' => '该信息栏目下有发布信息，不能删除'
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
        $list = db('section')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }
}
