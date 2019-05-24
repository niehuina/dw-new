<?php


namespace app\admin\controller;


use app\admin\common\Constant;
use app\admin\model\Section;
use app\admin\model\SectionInfo;

class PublicityCourtController extends BaseController
{
    public static $section_id = Constant::SETION_PUBLICITY_COURT;

    function _initialize(){
        parent::_initialize();
        $this->can_edit = parent::tf_has_section_permissions(self::$section_id);
    }

    public function index()
    {
        $this->assign('can_edit', $this->can_edit);
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('section_info.title');
        $map['section_info.deleted'] = '0';
        $map['sec.id|sec.parent_id'] = self::$section_id;

        $order = 'section_info.publish_time desc';
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
            $edit_state = true;
        }else {
            $model['section_id'] = self::$section_id;
        }

        $map['deleted'] = 0;
        $map['id|parent_id'] = self::$section_id;
        $this->assign('section_list', Section::all($map));
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        $this->assign('can_edit', $this->can_edit);
        return view();
    }

    public function save()
    {
        $data = input('post.');

        $has_per = parent::tf_has_section_permissions($data['section_id']);
        if($has_per==false){
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