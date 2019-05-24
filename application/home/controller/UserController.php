<?php

namespace app\home\controller;

use app\admin\model\TaskItemUser;
use app\home\model\Notification;
use app\home\model\Section;
use app\home\model\Setting;
use app\home\model\Task;
use app\home\model\TaskItem;
use app\home\common\Constant;
use app\home\common\Tool;
use app\home\model\Organization;
use app\home\model\PartyMember;
use app\home\model\SectionInfo;
use app\home\model\TaskItemAttachment;
use app\home\model\WebUser;
use think\Db;
use think\Model;
use think\Session;

class UserController extends BaseController
{
    public function information()
    {
        if (session(Constant::SESSION_USER_ID)) {
            $user_id = session(Constant::SESSION_USER_ID);
            $user = $this->user;
            if (!empty($user)) {
                $party_member = PartyMember::get(['web_user_id' => $user_id]);
                $organ = Organization::get($party_member['organ_id']);
                if ($organ) {
                    $organ['join_time'] = $party_member['join_time'];
                    $this->pm_organ = $organ;
                    $this->isPM = true;
                    $this->assign("pm_organ", $this->pm_organ);
                } else {
                    $this->isPM = false;
                }

                $this->assign("isPM", $this->isPM);

                $this->dep_name = "";
                $dep = Organization::get($user->organ_id);
                $this->dep_name = $dep->name;
                $this->assign("dep_name", $this->dep_name);
            }
            $list = db('position')->where(['deleted' => 0, 'id' => ['in', $user->position_ids]])->column('name', 'id');
            $position_names = implode(' | ', $list);
            $this->user->position_names = $position_names;
            return view();
        } else {
            $this->redirect(url('index/login'));
        }
    }

    public function reset_password()
    {
        if ($_POST) {
            return $this->save_reset_password();
        }

        return view();
    }

    public function save_reset_password()
    {
        $data = input('post.');

        $model = WebUser::get($this->userId);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                "message" => "用户不存在"
            ));
        }
        if (md5($data['oldpassword']) != $model['password']) {
            return json(array(
                'status' => 0,
                "message" => "旧密码不正确"
            ));
        }
        $model->password = md5($data['password']);
        $model->save();

        return json(array(
            'status' => 1,
            "message" => "修改密码成功",
        ));
    }

    public function message()
    {
        $page = $this->request->param('page');
        $page = isset($page) ? $page : 1;
        $this->assign('page', $page);

        $records = Notification::get_page_list(['web_user_id' => $this->userId], '*', $page, $this->page_length);
        $total_count = $records['total_count'];
        $total_page = $total_count % $this->page_length ? intval($total_count / $this->page_length) + 1 : $total_count / $this->page_length;
        $this->assign('total_page', $total_page);
        $this->assign('records', $records['data']);
        return view();
    }

    public function message_detail()
    {
        $id = $this->request->param('id');
        $message_info = Notification::get($id);
        $message_info->is_read = 1;
        $message_info->save();
        $this->assign('message_info', $message_info);
        return view();
    }

    public function research()
    {
        $page = $this->request->param('page');
        $page = isset($page) ? $page : 1;
        $this->assign('page', $page);

        $records = SectionInfo::get_page_list(Constant::SETION_PUBLISH_INFO, ['web_user_id' => $this->userId],
            'si.*, setting.value as level_name', $page, $this->page_length);
        foreach ($records['data'] as $key => $item) {
            $records['data'][$key]['review_status_name'] = get_value($item['review_status'], Constant::REVIEW_STATUS);
        }
        $total_count = $records['total_count'];
        $total_page = $total_count % $this->page_length ? intval($total_count / $this->page_length) + 1 : $total_count / $this->page_length;
        $this->assign('total_page', $total_page);
        $this->assign('records', $records['data']);

        return view();
    }

    public function research_detail()
    {
        if ($_POST) {
            $data = input('post.');

            if (empty($data['id'])) {
                $model = new SectionInfo ();
                $data['section_id'] = Constant::SETION_PUBLISH_INFO;
                $data['deleted'] = 0;
                $data['web_user_id'] = $this->userId;
                $data['created_user_id'] = $this->userId;
                $data['created_time'] = date('Y-m-d H:i:s');
                $data['review_status'] = 1;
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
            $model->data($data);
            $model->save();
            return json(array(
                'status' => 1,
                'message' => '保存成功'
            ));
        } else {
            $id = $this->request->param('id');
            $model = SectionInfo::get($id);
            if (!empty($model)) {
                $model['review_status_name'] = get_value($model['review_status'], Constant::REVIEW_STATUS);
            }
            $publish_level = db('setting')->where(['type' => 'publicity_level'])->field('type,code,value')->select();
            $this->assign('publish_level', $publish_level);
            $publish_type_list = db('publish_type')->where(['deleted' => 0])->column('id,name', 'id');
            $this->assign('publish_type_list', $publish_type_list);
            $this->assign('model', $model);
        }
        return view();
    }

    public function research_info()
    {
        $section_info_id = $this->request->param('id');
        $section_info = SectionInfo::get($section_info_id);
        $publish_level = Setting::get(['type' => 'publicity_level', 'code' => $section_info['level']]);
        $section_info['level_name'] = $publish_level['value'];
        $this->assign('section_info', $section_info);

        $publish_type = db("publish_type")->where(['deleted' => 0, 'id' => $section_info['publish_type_id']])
            ->field('id,name')->find();
        $this->assign('publish_type', $publish_type);

        return view();
    }

    public function task()
    {
        $page = $this->request->param('page');
        $page = isset($page) ? $page : 1;
        $this->assign('page', $page);

        $map = ['task_item_user.web_user_id' => $this->userId, 'task.deleted' => 0, 'task_item.deleted' => 0];
        $count = db('task_item_user')
            ->join('task_item', 'task_item_user.task_item_id = task_item.id')
            ->join('task', 'task_item_user.task_id = task.id')
            ->where($map)
            ->count();
        $start = (intval($page) - 1) * $this->page_length;
        $records = db('task_item_user')
            ->join('task_item', 'task_item_user.task_item_id = task_item.id')
            ->join('task', 'task_item_user.task_id = task.id')
            ->where($map)
            ->field('task_item.id,task.id as task_id,task.title as task_title, task_item.name,task.end_time as task_end_time, task.status as task_status,task_item_user.status as task_item_status,task_item_user.review_status')
            ->order('task_item_user.review_status asc, task_item_user.created_time asc')
            ->limit($start, $this->page_length)
            ->select();

        foreach ($records as $key => $item) {
            if ($item['task_item_status'] == Constant::TASK_STATUS_COMPLETED) {
                $records[$key]['item_status'] = get_value($item['review_status'], Constant::REVIEW_STATUS);
            } else {
                $records[$key]['item_status'] = get_value($item['task_item_status'], Constant::TASK_STATUS);
            }
            $records[$key]['task_status'] = get_value($item['task_status'], Constant::TASK_STATUS);

            switch ($item['task_status']) {
                case '0':
                case '1':
                    $records[$key]['task_status_color'] = "red";
                    break;
                case '2':
                    $records[$key]['task_status_color'] = "green";
                    break;
            }
            //[0=>'待完成', 1=>'待完成', 2=>'完成'];

            if ($records[$key]['item_status'] == "审核通过") {
                $records[$key]['item_status_color'] = "green";
            } elseif ($records[$key]['item_status'] == "待审核") {
                $records[$key]['item_status_color'] = "darkorange";
            } elseif ($records[$key]['item_status'] == "审核不通过") {
                $records[$key]['item_status_color'] = "red";
            } elseif ($records[$key]['item_status'] == "待完成") {
                $records[$key]['item_status_color'] = "red";
            }
        }
        $total_count = $count;
        $total_page = $total_count % $this->page_length ? intval($total_count / $this->page_length) + 1 : $total_count / $this->page_length;
        $this->assign('total_page', $total_page);
        $this->assign('records', $records);

        return view();
    }

    public function task_info()
    {
        $id = $this->request->param('id');
        $task = Task::get($id);

        $task_item = TaskItem::get_list(['ti.deleted' => 0, 'ti.task_id' => $id, 'tu.web_user_id' => $this->userId], 'ti.*');
        foreach ($task_item as $key => $item) {
            if ($item['status'] == Constant::TASK_STATUS_COMPLETED) {
                $task_item[$key]['status_name'] = get_value($item['review_status'], Constant::REVIEW_STATUS);
            } else {
                $task_item[$key]['status_name'] = get_value($item['status'], Constant::TASK_STATUS);
            }

            if ($task_item[$key]['status_name'] == "审核通过") {
                $task_item[$key]['item_status_color'] = "green";
            } elseif ($task_item[$key]['status_name'] == "待审核") {
                $task_item[$key]['item_status_color'] = "darkorange";
            } elseif ($task_item[$key]['status_name'] == "审核不通过") {
                $task_item[$key]['item_status_color'] = "red";
            } elseif ($task_item[$key]['status_name'] == "待完成") {
                $task_item[$key]['item_status_color'] = "red";
            }
        }
        $this->assign('task', $task);
        $this->assign('task_item', $task_item);

        return view();
    }

    public function task_detail()
    {
        if ($_POST) {
            $data = input('post.');

            $model = TaskItem::get($data['id']);
            if (empty($model)) {
                return json(array(
                    'status' => 0,
                    'message' => '记录不存在'
                ));
            }

            Db::startTrans();

            try {
                $task_item_user = TaskItemUser::get(['task_id' => $model['task_id'], 'task_item_id' => $model['id'], 'web_user_id' => $this->userId]);


                $task_item_user['complete_description'] = $data['description'];
                $task_item_user['complete_time'] = date('Y-m-d H:i:s');
                $task_item_user['complete_web_user_id'] = $this->userId;
                $task_item_user['status'] = Constant::TASK_STATUS_COMPLETED;
                $task_item_user['review_status'] = Constant::REVIEW_STATUS_WATING;
                $task_item_user['review_comment'] = null;
                $task_item_user['review_time'] = null;
                $task_item_user['review_user_id'] = null;
                $task_item_user->save();

                $task = Task::get($model['task_id']);
                $task['status'] = Constant::TASK_STATUS_IN_PROGRESS;
                $task->save();

                db('task_item_attachment')->where(['task_item_id' => $data['id'], 'web_user_id' => $this->userId])->delete();
                $attach['task_item_id'] = $data['id'];
                $attach['web_user_id'] = $this->userId;
                $files = $data['file_list'];
                if (!empty($files)) {
                    foreach ($files as $file) {
                        $attach['attachment_url'] = $file['file'];
                        $attach['attachment_name'] = $file['name'];
                        db('task_item_attachment')->insert($attach);
                    }
                }

                Db::commit();
                return json(array(
                    'status' => 1,
                    'message' => '提交成功'
                ));
            } catch (\Exception $e) {
                Db::rollback();
                return api_exception($e);
            }
        } else {
            $id = $this->request->param('id');
            $task_item = TaskItem::get($id);
            $this->assign('task_item', $task_item);

            $task_item_user = TaskItemUser::get(['task_id' => $task_item['task_id'], 'task_item_id' => $id, 'web_user_id' => $this->userId]);
            if (!empty($task_item_user)) {
                $task_item_user['review_status_name'] = get_value($task_item_user['review_status'], Constant::REVIEW_STATUS);
            }
            $this->assign('task_item_user', $task_item_user);

            $task_id = $task_item['task_id'];
            $task = Task::get($task_id);
            $this->assign('task', $task);

            $attachment_list = TaskItemAttachment::all(['task_item_id' => $id, 'web_user_id' => $this->userId]);
            $this->assign('attachment_list', $attachment_list);
        }

        return view();
    }

    public function task_item_info()
    {
        $id = $this->request->param('id');
        $task_item = TaskItem::get($id);
        $this->assign('task_item', $task_item);

        $task_id = $task_item['task_id'];
        $task = Task::get($task_id);
        $this->assign('task', $task);

        $task_item_user = TaskItemUser::get(['task_id' => $task_item['task_id'], 'task_item_id' => $id, 'web_user_id' => $this->userId]);
        $this->assign('task_item_user', $task_item_user);

        $attachment_list = TaskItemAttachment::all(['task_item_id' => $id]);
        $this->assign('attachment_list', $attachment_list);
        return view();
    }

    //党员积分
    public function point()
    {
        $page = $this->request->param('page');
        $page = isset($page) ? $page : 1;
        $this->assign('page', $page);
        $start = 0;
        if ($page > 1) {
            $start = (intval($page) - 1) * $this->page_length;
        }

        $map['score_history.deleted'] = '0';
        $map['web_user_id'] = Session::get(Constant::SESSION_USER_ID);
        $map['year(score_history.created_time)'] = date('Y');

        $order = 'id desc';
        $count = db('score_history')->where($map)
            ->field('score_history.id,score_history.web_user_id,score_history.score_item_id,
                        score_history.review_user_id,score_history.review_time,score_history.review_status,
                        score_history.created_time as get_time,score_history.updated_time,
                        score_item.name as score_item_name,score_item.score as score_item_score,score_item.created_time as score_item_created_time')
            ->join('score_item', 'score_item.id = score_history.score_item_id', 'LEFT')->count();
        $records = db('score_history')->where($map)
            ->field('score_history.id,score_history.web_user_id,score_history.score_item_id,
                        score_history.review_user_id,score_history.review_time,score_history.review_status,
                        score_history.created_time as get_time,score_history.updated_time,
                        score_item.name as score_item_name,score_item.score as score_item_score,score_item.created_time as score_item_created_time')
            ->join('score_item', 'score_item.id = score_history.score_item_id', 'LEFT')
            ->order($order)
            ->limit($start, $this->page_length)->select();

        $sum_score = db('score_history')->join('score_item', 'score_item.id = score_history.score_item_id')
            ->where($map)->sum('score_item.score');
        $this->assign('sum_score', $sum_score);

        $total_count = $count;
        $total_page = $total_count % $this->page_length ? intval($total_count / $this->page_length) + 1 : $total_count / $this->page_length;
        $this->assign('total_page', $total_page);
        $this->assign('records', $records);
        return view();
    }

    public function leave()
    {
        $vacation_days = 0;
        $map_year['vr.deleted'] = '0';
        $map_year['year(vr.start_time)'] = date('Y');
        $map_year['year(vr.end_time)'] = date('Y');
        $map_year['vr.type'] = Constant::VACATION_TYPES_YEAR;
        $map_year['vr.web_user_id'] = Session::get(Constant::SESSION_USER_ID);

        $sum_records = db('vacation_record vr')
            ->join('web_user wu', 'wu.id=vr.web_user_id and wu.deleted=0')
            ->where($map_year)
            ->field('ifnull(sum(vr.days),0) as days')
            ->group('vr.web_user_id')
            ->find();

        $vacation_days = $sum_records['days'];
		
		if(empty($vacation_days)){
			$vacation_days=0;
		}

        $order = 'vr.start_time desc';
        $map['vr.deleted'] = '0';
        $map['year(vr.start_time)'] = date('Y');
        $map['year(vr.end_time)'] = date('Y');
        $map['vr.web_user_id'] = Session::get(Constant::SESSION_USER_ID);
        $records = db('vacation_record vr')
            ->join('web_user wu', 'wu.id=vr.web_user_id and wu.deleted=0')
            ->where($map)
            ->field('ifnull(vr.days,0) as days,vr.start_time,vr.end_time,vr.type')
            ->order($order)->select();

        $web_user_model = WebUser::get(Session::get(Constant::SESSION_USER_ID));

        $entry_time = $web_user_model['entry_time'];
        foreach ($records as $key => $item) {
            $records[$key]['type_name'] = get_value($item['type'], Constant::VACATION_TYPES);
        }

        $v_map = null;
        $v_map['vacation_record.deleted'] = '0';
        $v_map['vacation_record.type'] = '0';
        $v_map['vacation_record.web_user_id'] = Session::get(Constant::SESSION_USER_ID);
        $sum_sick_leave_days = db('vacation_record')->where($v_map)
            ->field('vacation_record.days')->sum('vacation_record.days');

        $total_vacation = Tool::get_year_vacation($entry_time, $sum_sick_leave_days);
        $overplus_vacation = $total_vacation - $vacation_days;
        $overplus_vacation = $overplus_vacation < 0 ? 0 : $overplus_vacation;

        $year_vacation['vacation_days'] = $vacation_days;
        $year_vacation['total_vacation'] = $total_vacation;
        $year_vacation['overplus_vacation'] = $overplus_vacation;

        $this->assign('records', $records);
        $this->assign('year_vacation', $year_vacation);
        return view();
    }

    public function case_info()
    {
        $page = $this->request->param('page');
        $page = isset($page) ? $page : 1;
        $this->assign('page', $page);
        $start = 0;
        if ($page > 1) {
            $start = (intval($page) - 1) * $this->page_length;
        }

        $search_year = $this->request->param('search_year');
        if ($search_year) {
            $map['year(ci.accept_time)'] = $search_year;
        }
        $map['ci.web_user_id'] = $this->userId;
        $order = 'CONVERT(web_user_name USING gbk)';
        $count = db('web_user wu')
            ->join('case_info ci', 'wu.id=ci.web_user_id', 'LEFT')
            ->join('organization org', 'org.id=wu.organ_id', 'LEFT')
            ->where($map)
            ->field('ci.web_user_id,wu.name as web_user_name,ci.web_user_id, wu.position_ids, 
                org.name as depart_name, count(ci.web_user_id) as case_count')
            ->group('ci.web_user_id, wu.name, wu.position_ids, org.name')->count();
        $records = db('web_user wu')
            ->join('setting', 'setting.id = wu.user_type and setting.type="user_type" and setting.code="prosecutor"')
            ->join('case_info ci', 'wu.id=ci.web_user_id', 'LEFT')
            ->join('organization org', 'org.id=wu.organ_id', 'LEFT')
            ->where($map)
            ->field('ci.web_user_id,wu.name as web_user_name,ci.web_user_id, wu.position_ids, 
                org.name as depart_name, count(ci.web_user_id) as case_count')
            ->group('ci.web_user_id, wu.name, wu.position_ids, org.name')
            ->order($order)
            ->limit($start, $this->page_length)->select();

        foreach ($records as $key => $item) {
            $case_list = db('case_info')
                ->where(['web_user_id' => $item['web_user_id']])
                ->field('web_user_id, user_name, type_name, count(web_user_id) as case_count')
                ->group('web_user_id, user_name, type_name')->select();

            if (count($case_list) > 0) {
                $type_name = '';
                foreach ($case_list as $k => $case) {
                    if ($type_name) $type_name .= " | ";
                    $type_name .= $case['type_name'] . ' ' . $case['case_count'] . ' 件';
                }
                $records[$key]['type_name'] = $type_name;
            } else {
                $records[$key]['type_name'] = '';
            }
        }

        $total_count = $count;
        $total_page = $total_count % $this->page_length ? intval($total_count / $this->page_length) + 1 : $total_count / $this->page_length;
        $this->assign('total_page', $total_page);
        $this->assign('records', $records);

        return view();
    }

    public function case_detail()
    {

        $page = $this->request->param('page');
        $page = isset($page) ? $page : 1;
        $this->assign('page', $page);

        $start = 0;
        $length = $this->page_length;
        if ($page > 1) {
            $start = (intval($page) - 1) * $length;
        }

        $map['case_info.web_user_id'] = $this->userId;
        $order = 'case_info.accept_time desc';

        $total_count = db('case_info')
            ->join('web_user wu', 'wu.id=case_info.web_user_id', 'LEFT')
            ->where($map)->count();

        $records = db('case_info')
            ->join('web_user wu', 'wu.id=case_info.web_user_id', 'LEFT')
            ->where($map)
            ->field('case_info.warning,case_info.name,case_info.number,case_info.accept_time,
                case_info.current_stage,case_info.status,case_info.due_time,case_info.over_time,case_info.complete_time,
                case_info.record_time,case_info.type_name,
                case when wu.name is null then case_info.user_name else wu.name end as user_name,
                case_info.department,case_info.cell')
            ->limit($start, $length)->order($order)->select();

        $i = $start + 1;
        foreach ($records as $key => $item) {
            $records[$key]['index'] = $i++;
        }

        $total_page = $total_count % $length ? intval($total_count / $length) + 1 : $total_count / $length;
        $this->assign('total_page', $total_page);
        $this->assign('records', $records);

        return view();
    }

    public function upload_files()
    {
        $success = 0;
        $files = array();
        $post_files = $this->request->file('file');
        $error_message = '';

        foreach ($post_files as $info) {
            $index = count($files);
            $item = $info->getInfo();

            $files[$index]['name'] = $item['name'];    //上传图片的原名字
            $files[$index]['error'] = $item['error'];    //和该文件上传相关的错误代码
            $files[$index]['size'] = $item['size'];        //已上传文件的大小，单位为字节
            $files[$index]['type'] = $item['type'];        //文件的 MIME 类型，需要浏览器提供该信息的支持，例如"image/gif"
            $files[$index]['success'] = false;            //这个用于标志该图片是否上传成功
            $files[$index]['path'] = '';                //存图片路径
            $files[$index]['file'] = '';                //存图片路径

            // 接收过程有没有错误
            if ($item['error'] != 0) continue;
            //判断图片能不能上传
            /*if (!is_uploaded_file($item['tmp_name'])) {
                $files[$index]['error'] = $files[$index]['name'] . ' 文件已上传';
                $error_message .= $files[$index]['error'] . 'br';
                continue;
            }*/
            //扩展名
            $extension = strrchr($item['name'], '.');
            if (FALSE == $extension) {
                $files[$index]['error'] = $files[$index]['name'] . ' 文件格式不正确';
                $error_message .= $files[$index]['error'] . 'br';
                continue;
            }
            $files[$index]['type'] = $extension;

            $save_path = '\public\upload\task';
            $file_name = md5(time() . mt_rand(1, 1000000)) . "$extension";
            $path = ROOT_PATH . $save_path;
            if (!file_exists($path)) {
                mkdir($path, 0700);
            }
            $ret = move_uploaded_file($item['tmp_name'], $path . '\\' . $file_name);
            if ($ret === false) {
                $files[$index]['error'] = $files[$index]['name'] . ' 文件保存失败';
                $error_message .= $files[$index]['error'] . 'br';
                continue;
            } else {
                $files[$index]['file'] = $file_name;
                $files[$index]['path'] = $save_path . '\\' . $file_name;        //存图片路径
                $files[$index]['success'] = true;            //图片上传成功标志
                $success++;    //成功+1
            }
        }
        if (empty($error_message)) {
            echo json_encode(array(
                'status' => 1,
                "message" => "成功上传 $success 个文件",
                'data' => $files
            ));
        } else {
            echo json_encode(array(
                'status' => 0,
                "message" => "成功上传 $success 个文件，以下文件上传失败：<br>" . $error_message,
                'data' => $files
            ));
        }
    }

    public function delete_file()
    {
        $file_path = $_POST['file_path'];
        unlink(ROOT_PATH . $file_path);
    }

    function download_file($file_path, $file_name)
    {
        $file = ROOT_PATH . $file_path;
        if (is_file($file)) {
            $length = filesize($file); //文件大小
            $type = mime_content_type($file); //文件类型
            $showname = $file_name;//ltrim(strrchr($file,'/'),'/'); //文件名
            header("Content-Description: File Transfer");
            header('Content-type: ' . $type);
            header('Content-Length:' . $length);
            $userBrowser = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/MSIE/i', $userBrowser)) {
                $showname = urlencode($showname);
            }
            $showname = iconv('UTF-8', 'GBK//IGNORE', $showname);

            header('Content-Disposition: attachment; filename="' . $showname . '"');
            readfile($file);
            exit;
        } else {
            $this->assign('result', '文件已被删除！');
            return view();
            //exit();
        }
    }
}
