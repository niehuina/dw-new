<?php

namespace app\admin\controller;

use app\admin\model\Section;
use app\admin\model\SectionInfo;
use app\admin\common\Constant;
use think\Session;

class SectionInfoController extends BaseController
{
    public function index()
    {
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('section_info.title');
        $map['sec.parent_id'] = ['>', 7];
        $map['section_info.deleted'] = '0';

        $order = 'section_info.publish_time asc';
        $recordCount = db('section_info')
            ->join('section sec', 'sec.id=section_info.section_id', 'LEFT')
            ->join('user', 'user.id=section_info.user_id', 'LEFT')
            ->join('web_user', 'web_user.id=section_info.web_user_id', 'LEFT')
            ->where($map)->count();
        $records = db('section_info')
            ->join('section sec', 'sec.id=section_info.section_id', 'LEFT')
            ->join('user', 'user.id=section_info.user_id', 'LEFT')
            ->join('web_user', 'web_user.id=section_info.web_user_id', 'LEFT')
            ->where($map)
            ->field('section_info.id,sec.name as sec_name,section_info.publish_time,
                case when user.id is not null then user.name
                    when web_user.id is not null then web_user.name end as user_name,
                section_info.title,section_info.summary,section_info.content')
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
            $model = SectionInfo::get($id);
            $secton_model = Section::get($model['section_id']);
            $edit_state = true;
        }

        $map['s.deleted'] = 0;
        $map['s.id'] = ['>', 7];
        $map['s.parent_id'] = 0;
        $map['s2.id'] = array('exp','is not null');
        if (!$this->isAdmin) {
            //$map['section_roles.role_id'] = ['in', Session::get(Constant::SESSION_USER_ROLES)];
        }

        $parent_section_list = $records = db('section s')
            ->join('section_roles', "section_roles.section_id=s.id", "LEFT")
            ->join('section s2', "s2.parent_id=s.id", "LEFT")
            ->where($map)
            ->field('distinct s.id,s.name')
            ->select();

        $section_list=array();
        if ($edit_state && !empty($secton_model)) {
            $where['section.deleted'] = 0;
            $where['section.parent_id'] = $secton_model['parent_id'];
            $model['parent_section_id'] = $secton_model['parent_id'];

            if (!$this->isAdmin) {
                //$where['section_roles.role_id'] = ['in', Session::get(Constant::SESSION_USER_ROLES)];
            }

            $section_list = $records = db('section')
                ->join('section_roles', "section_roles.section_id=section.id", "LEFT")
                ->where($where)
                ->field('distinct section.id,section.name')
                ->select();
        }
        $this->assign('section_list', $section_list);
        $this->assign('parent_section_list', $parent_section_list);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function getChildSection($parent_id)
    {
        $child = array();
        if (!empty($parent_id)) {
            $where['section.deleted'] = 0;
            $where['section.parent_id'] = $parent_id;
            if (!$this->isAdmin) {
                //$where['section_roles.role_id'] = ['in', Session::get(Constant::SESSION_USER_ROLES)];
            }
            $section_list = $records = db('section')
                ->join('section_roles', "section_roles.section_id=section.id", "LEFT")
                ->where($where)
                ->field('distinct section.id,section.name')
                ->select();

            $child = $section_list;
        }

        return json(array(
            'status' => 1,
            'result' => $child
        ));
    }

    public function save()
    {
        $data = input('post.');
        $has_per = parent::tf_has_section_permissions($data['section_id']);
        if ($has_per == false) {
            return json(array(
                'status' => 0,
                'message' => '您无权发布此信息栏目'
            ));
        }
        if ($this->is_exist($data['title'], $data['id'],$data['section_id'])) {
            return json(array(
                'status' => 0,
                'message' => '信息标题已存在'
            ));
        }
        if (empty($data['id'])) {
            $model = new SectionInfo ();
            $data['deleted'] = 0;
            $data['user_id'] = $this->userId;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = SectionInfo::get($data['id']);
            if (empty($model)) {
                return json(array(
                    'status' => 0,
                    'message' => '记录不存在'
                ));
            }
            $data['updated_time'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->userId;
        }
        $file = $this->request->file('file');
        if (!empty($file)) {
            $upload_dir = '\public\upload\section_info';
            $info = $file->move(ROOT_PATH . $upload_dir);
            if ($info) {
                $file_name = $upload_dir . '\\' . $info->getSaveName();
                $data['picture_url'] = $file_name;
            } else {
                return api_error('上传失败');
            }
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
        $model = SectionInfo::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }
        $has_per = parent::tf_has_section_permissions($model['section_id']);
        if ($has_per == false) {
            return json(array(
                'status' => 0,
                'message' => '您无权操作此信息栏目'
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

    private function is_exist($key, $id = '',$section_id)
    {
        $where['title'] = $key;
        $where['deleted'] = 0;
        $where['section_id'] = $section_id;
        if (!empty($id)) {
            $where['id'] = array('<>', $id);
        }
        $list = db('section_info')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }
}
