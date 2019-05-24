<?php 

namespace app\home\model;

use think\Model; 

class WebUser extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'web_user'; 

    protected function base($query)
    {
        $query->where('deleted', 0);
    }

    public static function get_list($where=[], $field='*', $start=0, $length=0,$order='')
    {
        if(empty($order)){
            $order = 'CONVERT(name USING gbk) asc';
        }
        $where['deleted'] = 0;
        if($length==0){
            $list = db('web_user')->where($where)
                ->field($field)->order($order)->select();
        }else{
            $list = db('web_user')->where($where)
                ->field($field)->order($order)->limit($start,$length)->select();
        }
        return $list;
    }
}