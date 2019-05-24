<?php


namespace app\admin\controller;


use app\admin\common\Constant;
use app\admin\model\PublishType;
use app\admin\model\Section;
use app\admin\model\SectionInfo;

class PartyAffairsController extends BaseController
{
    public static $section_id = Constant::SETION_PARTY_AFFAIRS;

    public function index()
    {
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
        $map['parent_id'] = self::$section_id;
        $this->assign('section_list', Section::all($map));
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function save()
    {
        $data = input('post.');
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
        $model->deleted = 1;
        $model->deleted_user_id = $this->userId;
        $model->deleted_time = date('Y-m-d H:i:s');
        $model->save();
        return json(array(
            'status' => 1,
            'message' => '删除成功'
        ));
    }
}