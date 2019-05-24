<?php


namespace app\admin\controller;


use app\admin\common\Constant;
use app\admin\common\Tool;

class CaseInfoSearchController extends BaseController
{
    public function index()
    {
        $this->assign('search_date_s', date('Y-m-d', strtotime('-1 month')));
        $this->assign('search_date_e', date('Y-m-d'));
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('wu.name');
        $search_date_s = $this->request->param('search_date_s');
        $search_date_e = $this->request->param('search_date_e');

        if (!empty($search_date_s) || !empty($search_date_e)) {
            if(empty($search_date_s)){
                $search_date_s='1969-01-01';
            }

            if(empty($search_date_e)){
                $search_date_e='9999-01-01';
            }

            $map['case_info.accept_time'] = array(array('EGT',$search_date_s),array('ELT',$search_date_e),'AND');
        }

        $order = 'case_info.accept_time desc';
        $recordCount = db('case_info')
            ->join('web_user wu', 'wu.id=case_info.web_user_id', 'LEFT')
            ->where($map)->count();
        $records = db('case_info')
            ->join('web_user wu', 'wu.id=case_info.web_user_id', 'LEFT')
            ->where($map)
            ->field('case_info.warning,case_info.name,case_info.number,case_info.accept_time,
                case_info.current_stage,case_info.status,case_info.due_time,case_info.over_time,case_info.complete_time,
                case_info.record_time,case_info.type_name,
                wu.name as user_name,
                case_info.department,case_info.cell')
            ->limit($start, $length)->order($order)->select();

        $i = $start + 1;
        foreach ($records as $key => $item) {
            $records[$key]['index'] = $i++;
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
        $map = $this->process_query('wu.name');
        $search_date_s = $this->request->param('search_date_s');
        $search_date_e = $this->request->param('search_date_e');
        if (!empty($search_date_s) || !empty($search_date_e)) {
            if(empty($search_date_s)){
                $search_date_s='1969-01-01';
            }

            if(empty($search_date_e)){
                $search_date_e='9999-01-01';
            }

            $map['case_info.accept_time'] = array(array('EGT',$search_date_s),array('ELT',$search_date_e),'AND');
        }

        $order = 'case_info.accept_time desc';
        $records = db('case_info')
            ->join('web_user wu', 'wu.id=case_info.web_user_id', 'LEFT')
            ->where($map)
            ->field('case_info.warning,case_info.name,case_info.number,case_info.accept_time,
                case_info.current_stage,case_info.status,case_info.due_time,case_info.over_time,case_info.complete_time,
                case_info.record_time,case_info.type_name,
                wu.name as user_name,
                case_info.department,case_info.cell')
            ->order($order)->select();

        $tit = array(
            "序号"=>"number",
            "受理时间"=>"string",
            "检察官"=>"string",
            "案件名称"=>"string",
            "案件类别"=>"string",
            "办案部门"=>"string",
            "案件状态"=>"string",
            "到期日期"=>"string",
            "办结日期"=>"string",
        );

        $key = array(
            "accept_time",
            "user_name",
            "name",
            "type_name",
            "department",
            "status",
            "due_time",
            "over_time"
        );

        Tool::export("检察官个人办案列表",$tit,$key,$records,true);
    }
}