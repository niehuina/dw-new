<?php

namespace app\admin\controller;

use app\admin\common\Cache;
use app\admin\common\Tool;
use app\admin\model\Position;
use app\admin\model\VacationRecord;
use app\admin\common\Constant;
use think\Model;

class VacationStatisticalController extends BaseController
{
    public function index()
    {
        return view();
    }

    public function get_list()
    {
        $current_year = date('Y');
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('wu.name');
        $map['wu.deleted'] = '0';

        $order = 'CONVERT(wu.name USING gbk) asc';
        $recordCount = db('web_user wu')
            ->where($map)->count();

        $records = db('web_user wu')
            ->join('vacation_record vr', 'wu.id=vr.web_user_id and vr.deleted=0 and vr.type=' . Constant::VACATION_TYPES_YEAR . ' and year(vr.start_time)=' . $current_year . ' and year(vr.end_time)=' . $current_year, 'left')
            ->join('organization o', 'o.id=wu.organ_id', 'left')
            ->where($map)
            ->field('ifnull(sum(vr.days),0) as vacation_days,vr.web_user_id,
                            wu.name as user_name,wu.position_ids,max(wu.entry_time) as entry_time,o.name as orgin_name')
            ->group('wu.name,wu.position_ids,o.name,vr.web_user_id')
            ->limit($start, $length)->order($order)->select();

        //$web_user_list = Cache::key_value('web_user');
        foreach ($records as $key => $item) {
            $list = db('position')->where(['deleted' => 0, 'id' => ['in', $item['position_ids']]])->field('name')->select();
            //$list = Position::all();
            $records[$key]['position_ids'] = implode(' | ', array_column($list, 'name'));

            $v_map = null;
            $v_map['vacation_record.deleted'] = '0';
            $v_map['vacation_record.type'] = Constant::VACATION_TYPES_SICK;
            $v_map['vacation_record.web_user_id'] = $item['web_user_id'];
            $sum_sick_leave_days = db('vacation_record')->where($v_map)
                ->field('vacation_record.days')->sum('vacation_record.days');

            $total_vacation = Tool::get_year_vacation($item['entry_time'], $sum_sick_leave_days);
            $overplus_vacation = $total_vacation - $item['vacation_days'];
            $records[$key]['overplus_vacation'] = $overplus_vacation < 0 ? 0 : $overplus_vacation;

            $records[$key]['total_sick_vacation_days'] = $sum_sick_leave_days;
        }

        return json(array(
            'draw' => $this->request->param('draw'),
            'recordsTotal' => $recordCount,
            'recordsFiltered' => $recordCount,
            'data' => $records
        ));
    }

    public function _show_detail()
    {
        $web_user_id = $this->request->param('web_user_id');
        $this->assign('web_user_id', $web_user_id);
        return view();
    }

    public function get_detail_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $web_user_id = $this->request->param('web_user_id');
        $map = $this->process_query('id');
        $map['vacation_record.deleted'] = '0';
        $map['vacation_record.web_user_id'] = $web_user_id;
        $map['year(vacation_record.start_time)'] = date('Y');
        $map['year(vacation_record.end_time)'] = date('Y');

        $order = 'id desc';
        $recordCount = db('vacation_record')->where($map)->count();
        $records = db('vacation_record')->where($map)
            ->field('vacation_record.id,vacation_record.web_user_id,DATE_FORMAT(vacation_record.start_time,\'%Y-%m-%d\') as start_time,DATE_FORMAT(vacation_record.end_time,\'%Y-%m-%d\') as end_time,vacation_record.days,vacation_record.type,vacation_record.created_time,vacation_record.updated_time')
            ->limit($start, $length)->order($order)->select();

        $web_user_list = Cache::key_value('web_user');
        foreach ($records as $key => $item) {
            $records[$key]['web_user_name'] = $web_user_list[$item['web_user_id']];
            $records[$key]['type'] = get_value($item['type'], Constant::VACATION_TYPES);
        }

        return json(array(
            'draw' => $this->request->param('draw'),
            'recordsTotal' => $recordCount,
            'recordsFiltered' => $recordCount,
            'data' => $records
        ));
    }

    public function export_data()
    {
        $current_year = date('Y');

        $map = $this->process_query('wu.name');
        $map['wu.deleted'] = '0';


        $order = 'CONVERT(wu.name USING gbk) asc';

        $records = db('web_user wu')
            ->join('vacation_record vr', 'wu.id=vr.web_user_id and vr.deleted=0 and vr.type=' . Constant::VACATION_TYPES_YEAR . ' and year(vr.start_time)=' . $current_year . ' and year(vr.end_time)=' . $current_year, 'left')
            ->join('organization o', 'o.id=wu.organ_id', 'left')
            ->where($map)
            ->field('ifnull(sum(vr.days),0) as vacation_days,vr.web_user_id,
                            wu.name as user_name,wu.position_ids,max(wu.entry_time) as entry_time,o.name as orgin_name')
            ->group('wu.name,wu.position_ids,o.name,vr.web_user_id')
            ->order($order)->select();

        foreach ($records as $key => $item) {
            $list = db('position')->where(['deleted' => 0, 'id' => ['in', $item['position_ids']]])->field('name')->select();
            //$list = Position::all();
            $records[$key]['position_ids'] = implode(' | ', array_column($list, 'name'));

            $v_map = null;
            $v_map['vacation_record.deleted'] = '0';
            $v_map['vacation_record.type'] = Constant::VACATION_TYPES_SICK;
            $v_map['vacation_record.web_user_id'] = $item['web_user_id'];
            $sum_sick_leave_days = db('vacation_record')->where($v_map)
                ->field('vacation_record.days')->sum('vacation_record.days');

            $total_vacation = Tool::get_year_vacation($item['entry_time'], $sum_sick_leave_days);
            $overplus_vacation = $total_vacation - $item['vacation_days'];
            $records[$key]['overplus_vacation'] = $overplus_vacation < 0 ? 0 : $overplus_vacation;

            $records[$key]['total_sick_vacation_days'] = $sum_sick_leave_days;
        }

        $tit = array(
            "人员姓名" => "string",
            "部门" => "string",
            "职务" => "string",
            "已休年假天数" => "string",
            "剩余年假天数" => "string",
            "已休病假天数" => "string",
        );
        $key = array(
            "user_name",
            'orgin_name',
            'position_ids',
            'vacation_days',
            'overplus_vacation',
            'total_sick_vacation_days'
        );

        Tool::export("休假统计", $tit, $key, $records);
    }
}
