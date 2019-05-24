<?php

namespace app\home\controller;

use app\admin\model\TaskItemUser;
use app\admin\model\TaskUser;
use app\home\model\Banner;
use app\home\common\Constant;
use app\home\model\CaseInfo;
use app\home\model\PublishType;
use app\home\model\Section;
use app\home\model\SectionInfo;
use app\home\model\Setting;
use app\home\model\Task;
use app\home\model\TaskItem;
use app\home\model\TaskType;
use app\home\model\WebUser;
use think\Session;

class IndexController extends BaseController
{
    public function unauthorized()
    {
        return view();
    }

    public function index()
    {
        $list_count = 8;

        $case_list = CaseInfo::get_list('id,number,name', 0, 10);
        $this->assign('case_list', $case_list);

        $publicity_court_list = SectionInfo::get_list(Constant::SETION_PUBLICITY_COURT, [], 'si.*', 0, $list_count);
        $this->assign('publicity_court_list', $publicity_court_list);

        $news_list = SectionInfo::get_list(Constant::SETION_NEWS, [], 'si.*', 0, $list_count);
        $this->assign('news_list', $news_list);

        $politic_life_list = SectionInfo::get_list(Constant::SETION_POLITIC_LIFE, [], 'si.*', 0, $list_count);
        $this->assign('politic_life_list', $politic_life_list);

        $party_affairs_list = SectionInfo::get_list(Constant::SETION_PARTY_AFFAIRS, [], 'si.*', 0, $list_count);
        $this->assign('party_affairs_list', $party_affairs_list);

        $publish_info_list = SectionInfo::get_list(Constant::SETION_PUBLISH_INFO, ['review_status' => Constant::REVIEW_STATUS_SUCCESS], 'si.*', 0, $list_count);
        $this->assign('publish_info_list', $publish_info_list);

        $task_list = Task::get_list([], '*', 0, $list_count);
        $this->assign('task_list', $task_list);

        $web_user_list = WebUser::get_list([], '*', 0, $list_count);
        $this->assign('web_user_list', $web_user_list);

        $this->assign('banner_list', Banner::all());

        $section_list = db('section')->where(['deleted' => 0])->column('url', 'id');
        $this->assign('sec_list', $section_list);

        return view();
    }

    public function get_phone_list($length, $search = '')
    {
        if (empty($search)) {
            $where = [];
        } else {
            $where['name|phone'] = array('like', "%{$search}%", 'or');
        }

        $phone_list = WebUser::get_list($where, 'name,phone', 0, $length);
        return json_encode(array(
            'status' => 1,
            "result" => $phone_list
        ));
    }

    public function article_list()
    {
        $section_id = $this->request->param('section_id');
        $is_home = $this->request->param('is_home');
        $page = $this->request->param('page');
        $page = isset($page) ? $page : 1;
        $this->assign('page', $page);
        if (isset($is_home)) {
            $section = Section::get($section_id);
            $this->assign('section', $section);

            $section_item = Section::get(['parent_id' => $section_id]);
            $this->assign('section_item', $section_item);

            $section_children_list = db('section')->where(['deleted' => 0, 'parent_id' => $section_id])
                ->column('id,name,parent_id,url', 'id');
            $this->assign('section_children_list', $section_children_list);

            $section_info_list = SectionInfo::get_page_list($section_item['id'], [], 'si.*', $page, $this->page_length);
        } else {
            $section_item = Section::get($section_id);
            $this->assign('section_item', $section_item);

            if ($section_item['parent_id']) {
                $section = Section::get($section_item['parent_id']);
                $this->assign('section', $section);
            } else {
                $this->assign('section', $section_item);
            }

            //$this->assign('section', $section_item);
            $section_children_list = db('section')->where(['deleted' => 0, 'parent_id' => $section_item['parent_id']])
                ->column('id,name,parent_id,url', 'id');
            $this->assign('section_children_list', $section_children_list);

            $section_info_list = SectionInfo::get_page_list($section_id, [], 'si.*', $page, $this->page_length);
        }
        $total_count = $section_info_list['total_count'];
        $total_page = $total_count % $this->page_length ? intval($total_count / $this->page_length) + 1 : $total_count / $this->page_length;
        $this->assign('total_page', $total_page);
        $this->assign('section_info_list', $section_info_list['data']);

        return view();
    }

    public function phone_list()
    {
        $phone_list = $this->get_phone_list(20, '');
        $phone_list = json_decode($phone_list, true);
        $this->assign('phone_list', $phone_list['result']);
        return view();
    }

    public function article_detail()
    {
        $section_info_id = $this->request->param('id');
        $section_info = SectionInfo::get($section_info_id);
        $this->assign('section_info', $section_info);

        $section_id = $section_info['section_id'];
        $section = Section::get($section_id);
        $this->assign('section', $section);

        $section_children_list = db('section')->where(['deleted' => 0, 'parent_id' => $section['parent_id']])
            ->column('id,name,parent_id,url', 'id');
        $this->assign('section_children_list', $section_children_list);

        $parent_section = Section::get($section['parent_id']);
        $this->assign('parent_section', $parent_section);

        return view();
    }

    public function publish_list()
    {
        $section_id = Constant::SETION_PUBLISH_INFO;

        $section = Section::get($section_id);
        $this->assign('section', $section);

        $is_home = $this->request->param('is_home');
        $page = $this->request->param('page');
        $page = isset($page) ? $page : 1;
        $this->assign('page', $page);
        if (isset($is_home)) {
            $publish_type_list = db("publish_type")->where(['deleted' => 0])
                ->field('id,name')->order('display asc')->select();
            $this->assign('publish_type_list', $publish_type_list);

            $publish_type = current($publish_type_list);
            $this->assign('publish_type', $publish_type);

            $section_info_list = SectionInfo::get_page_list($section_id,
                ['publish_type_id' => $publish_type['id'], 'review_status' => Constant::REVIEW_STATUS_SUCCESS],
                'si.*, setting.value as level_name', $page, $this->page_length);
        } else {
            $type_id = $this->request->param('type_id');

            $publish_type_list = db("publish_type")->where(['deleted' => 0])
                ->field('id,name')->order('display asc')->select();
            $this->assign('publish_type_list', $publish_type_list);

            $task_type = PublishType::get($type_id);
            $this->assign('publish_type', $task_type);

            $section_info_list = SectionInfo::get_page_list($section_id,
                ['publish_type_id' => $type_id, 'review_status' => Constant::REVIEW_STATUS_SUCCESS],
                'si.*, setting.value as level_name', $page, $this->page_length);
        }
        $total_count = $section_info_list['total_count'];
        $total_page = $total_count % $this->page_length ? intval($total_count / $this->page_length) + 1 : $total_count / $this->page_length;
        $this->assign('total_page', $total_page);
        $this->assign('section_info_list', $section_info_list['data']);

        return view();
    }

    public function publish_detail()
    {
        $section_info_id = $this->request->param('id');
        $section_info = SectionInfo::get($section_info_id);
        $publish_level = Setting::get(['type' => 'publicity_level', 'code' => $section_info['level']]);
        $section_info['level_name'] = $publish_level['value'];
        $this->assign('section_info', $section_info);

        $section_id = Constant::SETION_PUBLISH_INFO;
        $section = Section::get($section_id);
        $this->assign('section', $section);

        $publish_type_list = db("publish_type")->where(['deleted' => 0])
            ->field('id,name')->order('display asc')->select();
        $this->assign('publish_type_list', $publish_type_list);

        $publish_type = db("publish_type")->where(['deleted' => 0, 'id' => $section_info['publish_type_id']])
            ->field('id,name')->find();
        $this->assign('publish_type', $publish_type);

        return view();
    }

    public function task_list()
    {
        $section_id = Constant::SETION_TASK_INFO;

        $section = Section::get($section_id);
        $this->assign('section', $section);

        $is_home = $this->request->param('is_home');
        $page = $this->request->param('page');
        $page = isset($page) ? $page : 1;
        $this->assign('page', $page);
        if (isset($is_home)) {
            $task_type_list = db("task_type")->where(['deleted' => 0, 'parent_id' => 0])->field('id,code,name')->select();
            $this->assign('task_type_list', $task_type_list);

            $task_type = current($task_type_list);
            $this->assign('task_type', $task_type);

            $task_list = Task::get_page_list(['type_id' => $task_type['id']], '*', $page, $this->page_length);
        } else {
            $type_id = $this->request->param('type_id');

            $task_type_list = db("task_type")->where(['deleted' => 0, 'parent_id' => 0])->field('id,code,name')->select();
            $this->assign('task_type_list', $task_type_list);

            $task_type = TaskType::get($type_id);
            $this->assign('task_type', $task_type);

            $task_list = Task::get_page_list(['type_id' => $type_id], '*', $page, $this->page_length);
        }

        foreach ($task_list['data'] as $key => $item) {
            $task_list['data'][$key]['task_status'] = get_value($item['status'], Constant::TASK_STATUS);

            //[0=>'待完成', 1=>'待完成', 2=>'完成'];
            switch ($item['status']) {
                case '0':
                case '1':
                    $task_list['data'][$key]['task_status_color'] = "red";
                    break;
                case '2':
                    $task_list['data'][$key]['task_status_color'] = "green";
                    break;
            }
        }

        $total_count = $task_list['total_count'];
        $total_page = $total_count % $this->page_length ? intval($total_count / $this->page_length) + 1 : $total_count / $this->page_length;
        $this->assign('total_page', $total_page);
        $this->assign('task_list', $task_list['data']);
        return view();
    }

    public function task_detail()
    {
        $section_id = Constant::SETION_TASK_INFO;
        $section = Section::get($section_id);
        $this->assign('section', $section);

        $task_id = $this->request->param('id');

        $task = Task::get($task_id);
        $this->assign('task', $task);

        $last_parent_task_type_id = $this->getTaskTypeParentId($task['type_id']);
        $task_type = TaskType::get($last_parent_task_type_id);
        $this->assign('task_type', $task_type);

        $task_type_list = db("task_type")->where(['deleted' => 0, 'parent_id' => 0])->field('id,code,name')->select();
        $this->assign('task_type_list', $task_type_list);

        /*$page = $this->request->param('page');
        $page = isset($page) ? $page : 1;
        $this->assign('page', $page);

        $start = 0;
        $length = $this->page_length;
        if ($page > 1) {
            $start = (intval($page) - 1) * $length;
        }*/

        $order = 'CONVERT(wu.name USING gbk) asc';
        /*$total_count = db('task_user tu')
            ->distinct(true)
            ->join('web_user wu', 'wu.id=tu.web_user_id and wu.deleted=0')
            ->join('task_item ti', 'ti.task_id = tu.task_id')
            ->count();*/

        $records = db('task_user tu')
            ->distinct(true)
            ->join('web_user wu', 'wu.id=tu.web_user_id and wu.deleted=0')
            ->field('tu.web_user_id,wu.name as web_user_name')
            ->where(['tu.task_id' => $task_id])
            ->order($order)->select();

        $task_items = TaskItem::all(['task_id' => $task_id]);
        $item_count = 0;
        foreach ($records as $key => $user) {
            $task_user_items = [];
            foreach ($task_items as $task_item) {
                $task_user_item['item_name'] = $task_item['name'];
                $task_item_user = TaskItemUser::where(['task_id' => $task_id, 'task_item_id' => $task_item['id'], 'web_user_id' => $user['web_user_id']])->find();
                if (empty($task_item_user)) {

                    $task_user_item['item_status_name'] = '待完成';
                    $task_user_item['item_status_color'] = 'red';
                } else {
                    if ($task_item_user['review_status'] == Constant::REVIEW_STATUS_SUCCESS) {
                        $task_user_item['item_status_name'] = '已完成';
                        $task_user_item['item_status_color'] = 'green';
                    } else if ($task_item_user['review_status'] == Constant::REVIEW_STATUS_FAILD) {
                        $task_user_item['item_status_name'] = '审核不通过';
                        $task_user_item['item_status_color'] = 'red';
                    } else if ($task_item_user['review_status'] == Constant::REVIEW_STATUS_WATING && $task_item_user['status'] == Constant::TASK_STATUS_COMPLETED) {
                        $task_user_item['item_status_name'] = '待审核';
                        $task_user_item['item_status_color'] = 'darkorange';
                    } else {
                        $task_user_item['item_status_name'] = '待完成';
                        $task_user_item['item_status_color'] = 'red';
                    }
                }
                $task_user_items[] = $task_user_item;
            }

            $records[$key]['items'] = $task_user_items;
            $item_count = count($task_user_items);
        }

        //$total_page = $total_count % $length ? intval($total_count / $length) + 1 : $total_count / $length;
        //$this->assign('total_page', $total_page);
        $this->assign('records', $records);
        $this->assign('item_count', $item_count);

        return view();
    }

    public function case_list()
    {
        $section_id = $this->request->param('section_id');
        $is_home = $this->request->param('is_home');
        $page = $this->request->param('page');
        $page = isset($page) ? $page : 1;
        $this->assign('page', $page);

        if (isset($is_home)) {
            $section = Section::get($section_id);
            $this->assign('section', $section);

            $section_item = Section::get(['parent_id' => $section_id]);
            $this->assign('section_item', $section_item);

            $section_children_list = db('section')->where(['deleted' => 0, 'parent_id' => $section_id])
                ->column('id,name,parent_id,url', 'id');
            $this->assign('section_children_list', $section_children_list);
        } else {
            $section_item = Section::get($section_id);
            $this->assign('section_item', $section_item);

            $section = Section::get($section_item['parent_id']);
            $this->assign('section', $section);

            $section_children_list = db('section')->where(['deleted' => 0, 'parent_id' => $section_item['parent_id']])
                ->column('id,name,parent_id,url', 'id');
            $this->assign('section_children_list', $section_children_list);
        }

        $case_list = CaseInfo::get_current_year_report($page, $this->page_length);
        $total_count = $case_list['total_count'];
        $total_page = $total_count % $this->page_length ? intval($total_count / $this->page_length) + 1 : $total_count / $this->page_length;
        $this->assign('total_page', $total_page);
        $this->assign('case_list', $case_list['data']);
        $this->assign('section_id', $section_id);

        return view();
    }

    public function case_detail()
    {
        $section_id = Constant::SETION_CASE_INFO;
        $web_user_id = $this->request->param('web_user_id');

        $web_user = WebUser::get($web_user_id);
        $this->assign('web_user', $web_user);

        $page = $this->request->param('page');
        $page = isset($page) ? $page : 1;
        $this->assign('page', $page);

        $start = 0;
        $length = $this->page_length;
        if ($page > 1) {
            $start = (intval($page) - 1) * $length;
        }

        $map['case_info.web_user_id'] = $web_user_id;
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
        $this->assign('case_list', $records);

        $section = Section::get($section_id);
        $this->assign('section', $section);

        $section_children_list = db('section')->where(['deleted' => 0, 'parent_id' => $section['parent_id']])
            ->column('id,name,parent_id,url', 'id');
        $this->assign('section_children_list', $section_children_list);

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
            $model = WebUser::get(['user_name' => $data['user_name']]);
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

            if ($model['active'] != 1) {
                return json(array(
                    'status' => 0,
                    "message" => '用户未启用'
                ));
            }

            session(Constant::SESSION_USER_ID, $model['id']);

            return json(array(
                'status' => 1,
                "message" => "登录成功"
            ));
        } else {
            return view();
        }
    }

    public function log_out()
    {
        Session::delete(Constant::SESSION_USER_ID);
        $this->redirect(url('/home/index'));
    }

    public function forgot_password()
    {
        return view();
    }

    public function user_info()
    {
        return view();
    }

    public function party_check_list()
    {
        $page = $this->request->param('page');
        $page = isset($page) ? $page : 1;
        $this->assign('page', $page);

        $start = 0;
        $length = $this->page_length;
        if ($page > 1) {
            $start = (intval($page) - 1) * $length;
        }

        $action = $this->request->param('action');

        $current_year = date('Y');

        $map['pm.deleted'] = '0';

        $order = 'CONVERT(org.name USING gbk) asc, CONVERT(wu.name USING gbk) asc';
        $total_count = db('party_member pm')
            ->join('web_user wu', 'wu.id=pm.web_user_id and wu.deleted=0')
            ->where($map)->count();

        $join_sql = $this->getPartyMemberScore();
        $records = db('party_member pm')
            ->join('web_user wu', 'wu.id=pm.web_user_id and wu.deleted=0')
            ->join('organization org', 'org.id=pm.organ_id', 'left')
            ->join('(' . $join_sql . ') T ', 'T.web_user_id=pm.web_user_id', 'left')
            ->join('member_should_pay s ', 's.web_user_id=pm.web_user_id and s.year=' . $current_year . ' and s.deleted=0', 'left')
            ->where($map)
            ->field('pm.id,pm.web_user_id,pm.organ_id,pm.join_time,pm.score,pm.created_time,
                            pm.updated_time,CONCAT(year(from_days(datediff(now(), join_time))),\' 年\') as age,
                            org.name as organ_name,ifnull(T.total_score,0) as total_score,
                            (ifnull(s.money,0) * 12) as year_should_pay,
                            wu.name as web_user_name')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $money_list = db('member_due_history')
                ->where(['deleted' => 0, 'web_user_id' => $item['web_user_id'], 'year' => $current_year])
                ->column('money');
            $year_paid_month = count($money_list);
            $year_paid_money = array_sum($money_list);

//            $year_paid_money = 0;
//            for ($i = 1; $i < 13; $i++) {
//                $money = 0;
//                $money_list = db('member_due_history')->where(['deleted' => 0, 'web_user_id' => $item['web_user_id'], 'year' => $current_year, 'month' => $i])->field('money')->find();
//                if (!empty($money_list['money'])) {
//                    $money = $money_list['money'];
//                }
//                $records[$key]['money' . $i] = $money;
//                $year_paid_money += $money;
//            }

            $records[$key]['year_paid_month'] = $year_paid_month;
            $records[$key]['year_paid_money'] = number_format($year_paid_money, 2);
            $records[$key]['year_remaining_pay'] = number_format($item['year_should_pay'] - $year_paid_money, 2);
        }

        $total_page = $total_count % $length ? intval($total_count / $length) + 1 : $total_count / $length;
        $this->assign('total_page', $total_page);
        $this->assign('records', $records);
        $this->assign('action', $action);
        return view();
    }

    public function getPartyMemberScore()
    {
        $review_success = Constant::REVIEW_STATUS_SUCCESS;
        $sql = "SELECT web_user_id,SUM(si.score) AS total_score  
                FROM score_history s
                INNER JOIN score_item si ON s.score_item_id = si.id and si.deleted=0
                WHERE s.deleted=0 and s.review_status = {$review_success} AND year(s.created_time)=year(CURRENT_DATE)
                GROUP BY web_user_id";
        return $sql;
    }

    private function getTaskTypeParentId($task_type_id)
    {
        $sql = "SELECT id
                    FROM ( 
                        SELECT 
                                @r AS _id, 
                                (SELECT @r := parent_id FROM task_type WHERE id = _id) AS last_parent_id, 
                                 @l := @l + 1 AS lvl 
                        FROM 
                                (SELECT @r := {$task_type_id}, @l := 0) vars, 
                                task_type h 
                        WHERE @r <> 0) T1 
                    JOIN task_type T2 
                    ON T1._id = T2.id
                where last_parent_id=0 limit 1";

        $record = db()->query($sql);

        if (count($record)) {
            return $record[0]['id'];
        }

        return 0;
    }

    public function publicity_court_index()
    {
        $list_count = 10;
        $publicity_court_list = SectionInfo::get_list(Constant::SETION_PUBLICITY_COURT, [], 'si.*', 0, $list_count);
        $this->assign('publicity_court_list', $publicity_court_list);

        return view();
    }

    public function publicity_court_detail()
    {
        $section_info_id = $this->request->param('id');
        $section_info = SectionInfo::get($section_info_id);
        $this->assign('section_info', $section_info);

        return view();
    }
}
