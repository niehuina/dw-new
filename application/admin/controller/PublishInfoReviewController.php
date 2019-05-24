<?php


namespace app\admin\controller;


use app\admin\common\Constant;
use app\admin\model\SectionInfo;
use think\Db;

class PublishInfoReviewController extends BaseController
{
    public static $section_id = 4;
    public function index()
    {
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('si.title');
        $map['si.deleted'] = '0';
        $map['si.section_id'] = self::$section_id;
        $map['si.web_user_id'] = ['exp', 'is not null'];

        $order = 'si.review_status asc,si.review_time desc,si.publish_time desc';
        $recordCount = db('section_info si')
            ->join('section sec','sec.id=si.section_id', 'LEFT')
            ->join('user', 'user.id=si.review_user_id', 'LEFT')
            ->join('web_user','web_user.id=si.web_user_id', 'LEFT')
            ->where($map)->count();
        $records = db('section_info si')
            ->join('section sec','sec.id=si.section_id', 'LEFT')
            ->join('user', 'user.id=si.review_user_id', 'LEFT')
            ->join('web_user','web_user.id=si.web_user_id', 'LEFT')
            ->where($map)
            ->field('si.id,sec.name as sec_name,si.publish_time,
                web_user.name as user_name, user.name as reviewer, si.review_time,si.review_status,
                si.title,si.summary,si.content')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $records[$key]['review_status_name'] = get_value($item['review_status'], Constant::REVIEW_ACTION);
        }

        return json(array(
            'draw' => $this->request->param('draw'),
            'recordsTotal' => $recordCount,
            'recordsFiltered' => $recordCount,
            'data' => $records
        ));
    }

    public function _item_review()
    {
        $id = $this->request->param('id');
        $model = null;
        $edit_state = false;
        if (!empty($id)) {
            $model = SectionInfo::get($id);
            $edit_state = true;
        }

        if($model['review_status'] == Constant::REVIEW_STATUS_WATING){
            $model['review_status'] = Constant::REVIEW_STATUS_SUCCESS;
        }
        $review_status_list = Constant::REVIEW_ACTION;
        $this->assign('review_status_list', $review_status_list);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function save()
    {
        $data = input('post.');

        $model = SectionInfo::get($data['id']);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }
        $data['review_time'] = date('Y-m-d H:i:s');
        $data['review_user_id'] = $this->userId;

        $model->data($data)->save();

        return json(array(
            'status' => 1,
            "message" => "保存成功"
        ));
    }
}