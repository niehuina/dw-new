<?php 

namespace app\home\model;

use think\Model; 

class Link extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'link';

    public static function get_list($where=[], $field='*', $start=0, $length=0,$order='')
    {
        if(empty($order)){
            $order = 'display_order asc';
        }
        if($length==0){
            $list = db('link')->where($where)
                ->field($field)->order($order)->select();
        }else{
            $list = db('link')->where($where)
                ->field($field)->order($order)->limit($start,$length)->select();
        }
        return $list;
    }
}