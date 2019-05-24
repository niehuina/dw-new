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
}