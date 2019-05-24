<?php 

namespace app\home\model;

use think\Model; 

class Section extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'section'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }

    public static function get_list($where=[], $field='*', $order='', $start=0, $length=0)
    {
        if(empty($order)){
            $order = 'sort asc';
        }
        $where['deleted'] = 0;

        if($length==0){
            $list = db('section')->where($where)->field($field)->order($order)->select();
        }else{
            $list = db('section')->where($where)->field($field)->order($order)->limit($start, $length)->select();
        }

        return $list;
    }
}