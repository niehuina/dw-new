<?php


namespace app\admin\controller;


use app\admin\common\Tool;
use think\Db;

class CaseInfoReportController extends BaseController
{
    public function index()
    {
        $this->assign('search_year', date('Y'));
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = [];
        $search_year = $this->request->param('search_year');

        $order = 'CONVERT(web_user_name USING gbk) asc';
        $recordCount = db('web_user wu')
            ->join('setting', 'setting.id = wu.user_type and setting.type="user_type" and setting.code="prosecutor"')
            ->join('case_info ci', 'wu.id=ci.web_user_id and year(ci.accept_time)='.$search_year, 'LEFT')
            ->join('organization org', 'org.id=wu.organ_id', 'LEFT')
            ->where($map)
            ->field('wu.name as web_user_name,ci.web_user_id, wu.position_ids, 
                org.name as depart_name, count(ci.web_user_id) as case_count')
            ->group('ci.web_user_id, wu.name, wu.position_ids, org.name')->count();
        $records = db('web_user wu')
            ->join('setting', 'setting.id = wu.user_type and setting.type="user_type" and setting.code="prosecutor"')
            ->join('case_info ci', 'wu.id=ci.web_user_id and year(ci.accept_time)='.$search_year, 'LEFT')
            ->join('organization org', 'org.id=wu.organ_id', 'LEFT')
            ->where($map)
            ->field('wu.name as web_user_name,ci.web_user_id, wu.position_ids, 
                org.name as depart_name, count(ci.web_user_id) as case_count')
            ->group('ci.web_user_id, wu.name, wu.position_ids, org.name')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $list = db('position')->where(['deleted' => 0, 'id' => ['in', $item['position_ids']]])->column('name','id');
            $records[$key]['position_ids'] = implode(' | ', $list);

            $case_list = db('case_info')
                ->where(['web_user_id'=>$item['web_user_id']])
                ->field('web_user_id, user_name, type_name, count(web_user_id) as case_count')
                ->group('web_user_id, user_name, type_name')->select();

            if(count($case_list) > 0){
                $type_name = '';
                foreach($case_list as $k=>$case){
                    if($type_name) $type_name .= " | ";
                    $type_name .= $case['type_name'] .' '. $case['case_count'] . ' 件';
                }
                $records[$key]['type_name'] = $type_name;
            }else{
                $records[$key]['type_name'] = '';
            }
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
        $map = [];
        $search_year = $this->request->param('search_year');

        $order = 'CONVERT(web_user_name USING gbk) asc';
        $records = db('web_user wu')
            ->join('setting', 'setting.id = wu.user_type and setting.type="user_type" and setting.code="prosecutor"')
            ->join('case_info ci', 'wu.id=ci.web_user_id and year(ci.accept_time)='.$search_year, 'LEFT')
            ->join('organization org', 'org.id=wu.organ_id', 'LEFT')
            ->where($map)
            ->field('wu.name as web_user_name,ci.web_user_id, wu.position_ids, 
                org.name as depart_name, count(ci.web_user_id) as case_count')
            ->group('ci.web_user_id, wu.name, wu.position_ids, org.name')
            ->order($order)->select();

        foreach ($records as $key => $item) {
            $list = db('position')->where(['deleted' => 0, 'id' => ['in', $item['position_ids']]])->column('name','id');
            $records[$key]['position_ids'] = implode(' | ', $list);

            $case_list = db('case_info')
                ->where(['web_user_id'=>$item['web_user_id']])
                ->field('web_user_id, user_name, type_name, count(web_user_id) as case_count')
                ->group('web_user_id, user_name, type_name')->select();

            if(count($case_list) > 0){
                $type_name = '';
                foreach($case_list as $k=>$case){
                    if($type_name) $type_name .= " | ";
                    $type_name .= $case['type_name'] .' '. $case['case_count'] . ' 件';
                }
                $records[$key]['type_name'] = $type_name;
            }else{
                $records[$key]['type_name'] = '';
            }
        }

        $tit = array(
            "检察官姓名"=>"string",
            "职务"=>"string",
            "办案数量"=>"number",
            "分管/协管/所在业务部门"=>"string",
            "办案分类"=>"string",
        );

        $key = array(
            "web_user_name",
            "position_ids",
            "case_count",
            "depart_name",
            "type_name"
        );

        Tool::export("年办案情况统计",$tit,$key,$records);
    }
}