<?php 

namespace app\home\model;

use think\Db;
use think\Model;

class PublicityCourt extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'publicity_court';

    public static function get_list($where=[], $field='*', $start=0, $length=0,$order='')
    {
        if(empty($order)){
            $order = 'court_time desc';
        }
        if($length==0){
            $list = db('publicity_court')->where($where)
                ->field($field)->order($order)->select();
        }else{
            $list = db('publicity_court')->where($where)
                ->field($field)->order($order)->limit($start,$length)->select();
        }
        return $list;
    }


    public static function get_page_list($where=[], $field, $page=1, $length=0,$order='')
    {
        if(empty($order)){
            $order = 'court_time desc';
        }
        $count = db('publicity_court')->where($where)
            ->field($field)->order($order)->count();
        $start = 0;
        if($page>1){
            $start = (intval($page) - 1)*$length;
        }

        if($length==0){
            $list = db('publicity_court')->where($where)
                ->field($field)->order($order)->select();
        }else{
            $list = db('publicity_court')->where($where)
                ->field($field)->order($order)->limit($start,$length)->select();
        }
        $data['total_count'] = $count;
        $data['data'] = $list;
        return $data;
    }
}