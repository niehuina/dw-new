<?php 

namespace app\home\model;

use think\Model; 

class Notification extends Model
{
    protected $pk = 'id';
    protected $table = 'notification';

    protected function base($query)
    {
        $query->where('deleted', 0);
    }

    public static function get_count($where=[])
    {
        $where['is_read'] = 0;
        $where['deleted'] = 0;
        $count = db('notification')->where($where)->count();
        return $count;
    }

    public static function get_page_list($where=[], $field='*', $page=1, $length=0,$order='')
    {
        if(empty($order)){
            $order = 'created_time desc, is_read asc';
        }
        $where['deleted'] = 0;
        $count = db('notification')->where($where)
            ->field($field)->order($order)->count();
        $start = 0;
        if($page>1){
            $start = (intval($page) - 1)*$length;
        }
        if($length==0){
            $list = db('notification')->where($where)
                ->field($field)->order($order)->select();
        }else{
            $list = db('notification')->where($where)
                ->field($field)->order($order)->limit($start,$length)->select();
        }
        $data['total_count'] = $count;
        $data['data'] = $list;
        return $data;
    }
}