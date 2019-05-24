<?php 

namespace app\home\model;

use think\Db;
use think\Model;

class Task extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'task'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }

    public static function get_list($where=[], $field='*', $start=0, $length=0,$order='')
    {
        if(empty($order)){
            $order = 'publish_time desc';
        }
        $where['deleted'] = 0;
        if($length==0){
            $list = db('task')->where($where)
                ->field($field)->order($order)->select();
        }else{
            $list = db('task')->where($where)
                ->field($field)->order($order)->limit($start,$length)->select();
        }
        return $list;
    }

    public static function get_page_list($where=[], $field, $page=1, $length=0,$order='')
    {
        if(empty($order)){
            $order = 'publish_time desc';
        }
        $where['deleted'] = 0;
        $count = db('task')->where($where)
            ->field($field)->order($order)->count();
        $start = 0;
        if($page>1){
            $start = (intval($page) - 1)*$length;
        }

        if($where['type_id']){
            $result = Db::query("select getTaskTypeChildrens({$where['type_id']})");
            if($result){
                $type_ids = current(array_values($result[0]));
                $where['type_id'] = ['in', $type_ids];
            }
        }

        if($length==0){
            $list = db('task')->where($where)
                ->field($field)->order($order)->select();
        }else{
            $list = db('task')->where($where)
                ->field($field)->order($order)->limit($start,$length)->select();
        }
        $data['total_count'] = $count;
        $data['data'] = $list;
        return $data;
    }
}