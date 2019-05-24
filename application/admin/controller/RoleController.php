<?php

namespace app\admin\controller;

use app\admin\common\Cache;
use app\admin\common\Common;
use app\admin\common\Constant;
use app\admin\model\Role;
use app\admin\model\Permission;
use app\admin\model\Section;
use app\admin\model\SectionRoles;
use think\Db;
use think\Url;

class RoleController extends BaseController
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
        $map['deleted'] = '0';

        $order = 'CONVERT(name USING gbk) asc';
        $recordCount = db('role')->where($map)->count();
        $records = db('role')->where($map)
            ->field('id,name,type')
            ->limit($start, $length)->order($order)->select();
        foreach ($records as $key => $item) {
            $records[$key]['type'] = get_value($item['type'], Constant::ROLE_TYPE_LIST);
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
            $model = Role::get($id);
            $edit_state = true;
        }
        $this->assign('type_list', Constant::ROLE_TYPE_LIST);
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
                "message" => '名称已存在'
            ));
        }
        if (empty($data['id'])) {
            $model = new Role ();
            $model->name = $data['name'];
            $model->type = $data['type'];
            $model->deleted = 0;
            $model->created_user_id = $this->userId;
            $model->created_time = date('Y-m-d H:i:s');
            $model->save();
        } else {
            $model = Role::get($data['id']);
            if (empty($model)) {
                return json(array(
                    'status' => 0,
                    "message" => "记录不存在"
                ));
            }
            $model->name = $data['name'];
            $model->type = $data['type'];
            $model->updated_time = date('Y-m-d H:i:s');
            $model->updated_user_id = $this->userId;
            $model->save();
        }
        Cache::clear('role');
        return json(array(
            'status' => 1,
            "message" => "保存成功"
        ));
    }

    public function _set_section()
    {
        $id = $this->request->param('id');
        $model = null;
        $section_roles = null;
        if (!empty($id)) {
            $model = Role::get($id);
            //$parent_sections = Section::all(['deleted' => 0, 'parent_id' => 0]);
            //$section_roles = SectionRoles::where(['role_id' => $id])->column('section_id');
            $map['section_roles.role_id'] = $id;
            $section_roles=db('section_roles')
                ->join('section', 'section_roles.section_id=section.id')
                ->where($map)->column('section_id');

            $section_roles = implode(',', $section_roles);
        }
        $this->assign('model', $model);
        $this->assign('section_roles', $section_roles);
        return view();
    }

    public function delete()
    {
        $id = $this->request->param('id');
        $model = Role::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                "message" => "记录不存在"
            ));
        }

        $count = db('user')->where(['deleted' => 0, 'role_id' => $id])->count();
        if ($count > 0) {
            return json(array(
                'status' => 0,
                "message" => "该角色下有关联的用户，不能删除"
            ));
        }

        $model->deleted = 1;
        $model->deleted_user_id = $this->userId;
        $model->deleted_time = date('Y-m-d H:i:s');
        $model->save();
        Cache::clear('role');
        return json(array(
            'status' => 1,
            "message" => "删除成功"
        ));
    }

    private function is_exist($name, $id = '')
    {
        $where['name'] = $name;
        $where['deleted'] = 0;
        if (!empty($id)) {
            $where['id'] = array('<>', $id);
        }
        $list = db('role')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }

    public function _item_permission()
    {
        $id = $this->request->param('id');
        $model = null;
        $system_permissions = null;
        if (!empty($id)) {
            $model = Role::get($id);
            $system_permissions = Permission::where(['role_id' => $id])->column('name');
            $system_permissions = implode(',', $system_permissions);
        }
        $this->assign('model', $model);
        $this->assign('system_permissions', $system_permissions);
        return view();
    }

    public function get_system_permissions()
    {
        $permission = Common::get_permissions();
        return json($permission);
    }


    public function get_all_sections($parent_id = 0)
    {
        $parent_sections = Section::all(['deleted' => 0, 'parent_id' => $parent_id]);

        $all_note = array();
        foreach ($parent_sections as $parent) {
            $parent_note = array(
                'id' => $parent['id'],
                'text' => $parent['name'],
                'children' => $this->get_section_children($parent['id'])
            );

            array_push($all_note, $parent_note);

        }

        return json($all_note);
    }

    public function get_section_children($pid)
    {
        $all_note = array();
        if (!empty($pid)) {
            $child_sections = Section::all(['deleted' => 0, 'parent_id' => $pid]);
            foreach ($child_sections as $section) {
                $child_note = array(
                    'id' => $section['id'],
                    'text' => $section['name'],
                );

                array_push($all_note, $child_note);
            }
        }

        return $all_note;
    }

    public function save_secton_roles()
    {
        $role_id = $this->request->param('role_id');

        $now_date = date('Y-m-d H:i:s');
        $sections = $this->request->param('sections');

        $section_role_list = [];

        if (!empty($sections)) {
            $sections = explode(",", $sections);
            foreach ($sections as $item) {
                $section_role["role_id"] = $role_id;
                $section_role["section_id"] = $item;
                $section_role["created_time"] = $now_date;
                $section_role_list[] = $section_role;
            }
        }

        Db::startTrans();
        try {
            //先全部删除
            SectionRoles::destroy(['role_id' => $role_id]);

            //重新保存
            if (!empty($section_role_list)) {
                $model = new SectionRoles();
                $model->saveAll($section_role_list);
            }

            Db::commit();
            return json(array(
                'status' => 1,
                "message" => "给角色设置栏目成功"
            ));
        } catch (\Exception $e) {
            Db::rollback();
            return api_exception($e);
        }
    }

    public function get_app_permissions()
    {
        $permission = Common::get_app_permissions();
        return json($permission);
    }

    public function save_permission()
    {
        $role_id = $this->request->param('role_id');

        Permission::destroy(['role_id' => $role_id]);

        $system_permission = $this->request->param('system_permission');
        $system_permission = explode(",", $system_permission);
        $now_date = date('Y-m-d H:i:s');
        $permission_list = [];
        foreach ($system_permission as $item) {
            if (!empty($item) && !in_array($item, Constant::PERMISSION_NOT_SAVE)) {
                $permission["role_id"] = $role_id;
                $permission["name"] = $item;
                $permission["created_time"] = $now_date;
                $permission_list[] = $permission;
            }
        }

        $model = new Permission();
        $model->saveAll($permission_list);

        return json(array(
            'status' => 1,
            "message" => "权限保存成功"
        ));
    }
}
