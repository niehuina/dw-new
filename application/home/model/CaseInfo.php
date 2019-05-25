<?php 

namespace app\home\model;

use think\Model; 

class CaseInfo extends Model 
{ 
    protected $pk = 'id';
    protected $table = 'case_info';

    public static function get_list($field, $start=0, $length=0, $order='')
    {
        if(empty($order)){
            $order = 'accept_time desc';
        }
        if($length==0){
            $list = db('case_info')->field($field)->order($order)->select();
        }else{
            $list = db('case_info')->field($field)->order($order)->limit($start,$length)->select();
        }
        return $list;
    }

    public static function get_page_list($field, $page=1, $length=0, $order='')
    {
        if(empty($order)){
            $order = 'accept_time desc';
        }
        $count = db('case_info')
            ->field($field)->order($order)->count();
        $start = 0;
        if($page>1){
            $start = (intval($page) - 1)*$length;
        }
        if($length==0){
            $list = db('case_info')->field($field)->order($order)->select();
        }else{
            $list = db('case_info')->field($field)->order($order)->limit($start,$length)->select();
        }
        $data['total_count'] = $count;
        $data['data'] = $list;
        return $data;
    }

    public static function get_current_year_report($page=1, $length=0, $order='')
    {
        $search_year = date('Y');
        if(empty($order)){
            $order = 'CONVERT(web_user_name USING gbk) asc';
        }
        $count = db('web_user wu')
            ->join('setting', 'setting.id = wu.user_type and setting.type="user_type" and setting.code="prosecutor"')
            ->join('case_info ci', 'wu.id=ci.web_user_id and year(ci.accept_time)='.$search_year, 'INNER')
            ->join('organization org', 'org.id=wu.organ_id', 'LEFT')
            ->field('wu.name as web_user_name,ci.web_user_id, wu.position_ids, 
                org.name as depart_name, count(ci.web_user_id) as case_count')
            ->group('ci.web_user_id, wu.name, wu.position_ids, org.name')->count();
        $start = 0;
        if($page>1){
            $start = (intval($page) - 1)*$length;
        }
        if($length==0){
            $records = db('web_user wu')
                ->join('setting', 'setting.id = wu.user_type and setting.type="user_type" and setting.code="prosecutor"')
                ->join('case_info ci', 'wu.id=ci.web_user_id and year(ci.accept_time)='.$search_year, 'INNER')
                ->join('organization org', 'org.id=wu.organ_id', 'LEFT')
                ->field('wu.name as web_user_name,ci.web_user_id, wu.position_ids, 
                org.name as depart_name, count(ci.web_user_id) as case_count')
                ->group('ci.web_user_id, wu.name, wu.position_ids, org.name')
                ->order($order)->select();;
        }else{
            $records = db('web_user wu')
                ->join('setting', 'setting.id = wu.user_type and setting.type="user_type" and setting.code="prosecutor"')
                ->join('case_info ci', 'wu.id=ci.web_user_id and year(ci.accept_time)='.$search_year, 'INNER')
                ->join('organization org', 'org.id=wu.organ_id', 'LEFT')
                ->field('wu.name as web_user_name,ci.web_user_id, wu.position_ids, 
                org.name as depart_name, count(ci.web_user_id) as case_count')
                ->group('ci.web_user_id, wu.name, wu.position_ids, org.name')
                ->limit($start, $length)->order($order)->select();
        }
        foreach ($records as $key => $item) {
            $list = db('position')->where(['deleted' => 0, 'id' => ['in', $item['position_ids']]])->column('name','id');
            $records[$key]['position_ids'] = implode(' | ', $list);

            $case_list = db('case_info')
                ->where(['web_user_id'=>$item['web_user_id'], 'year(case_info.accept_time)'=>$search_year])
                ->field('web_user_id, user_name, type_name, count(web_user_id) as case_count')
                ->group('web_user_id, user_name, type_name')->select();

            if(count($case_list) > 0){
                $type_name = '';
                foreach($case_list as $k=>$case){
                    if($type_name) $type_name .= "<br/>";
                    $type_name .= $case['type_name'] .' '. $case['case_count'] . ' 件';
                }
                $records[$key]['type_name'] = $type_name;
            }else{
                $records[$key]['type_name'] = '';
            }
        }
        $data['total_count'] = $count;
        $data['data'] = $records;
        return $data;
    }
}