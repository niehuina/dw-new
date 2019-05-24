<?php 

namespace app\home\model;

use think\Model; 

class TaskItem extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'task_item'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }

    public function Attachments()
    {
        $this->hasMany('task_item_attachment','task_item_id', 'id');
    }

    public static function get_list($where=[], $field='*', $start=0, $length=0,$order='')
    {
        if(empty($order)){
            $order = 'task.publish_time desc,ti.sort asc';
        }
        $where['ti.deleted'] = 0;
        if($length==0){
            $list = db('task_item ti')
                ->join('task','task.id=ti.task_id and task.deleted=0')
                ->join('task_user tu','tu.task_id=ti.task_id')
                ->where($where)
                ->field($field)->order($order)->select();
        }else{
            $list = db('task_item ti')
                ->join('task','task.id = ti.task_id and task.deleted=0')
                ->where($where)
                ->field($field)->order($order)->limit($start,$length)->select();
        }
        return $list;
    }

    public static function get_page_list1($where=[], $field='*', $page=1, $length=0,$order='')
    {
        if(empty($order)){
            $order = 'task.publish_time desc,ti.sort asc';
        }
        $where['task.deleted'] = 0;
        $count = db('task')
            ->join('task_item ti','ti.deleted=0 and task.id=ti.task_id')
            ->join('task_user tu','tu.task_id=task.id')
            ->where($where)->count();

        $start = 0;
        if($page>1){
            $start = (intval($page) - 1)*$length;
        }
        if($length==0){
            $list = db('task')
                ->join('task_item ti','ti.deleted=0 and task.id=ti.task_id')
                ->join('task_user tu','tu.task_id=task.id')
                ->where($where)
                ->field($field)->order($order)->select();
        }else{
            $list = db('task')
                ->join('task_item ti','ti.deleted=0 and task.id=ti.task_id')
                ->join('task_user tu','tu.task_id=task.id')
                ->where($where)
                ->field($field)->order($order)->limit($start,$length)->select();
        }
        $data['total_count'] = $count;
        $data['data'] = $list;
        return $data;
    }

    public static function get_page_list($where=[], $field='*', $page=1, $length=0,$order='')
    {
        if(empty($order)){
            $order = 'task.publish_time desc';
        }
        $where['ti.deleted'] = 0;
        $count = db('task_item ti')
            ->join('task','task.deleted=0 and task.id=ti.task_id')
            ->join('task_user tu','tu.task_id=ti.task_id')
            ->where($where)->count();
        $start = 0;
        if($page>1){
            $start = (intval($page) - 1)*$length;
        }
        if($length==0){
            $list = db('task_item ti')
                ->join('task','task.deleted=0 and task.id=ti.task_id')
                ->join('task_user tu','tu.task_id=ti.task_id')
                ->where($where)
                ->field($field)->order($order)->select();
        }else{
            $list = db('task_item ti')
                ->join('task','task.deleted=0 and task.id=ti.task_id')
                ->join('task_user tu','tu.task_id=ti.task_id')
                ->where($where)
                ->field($field)->order($order)->limit($start,$length)->select();
        }
        $data['total_count'] = $count;
        $data['data'] = $list;
        return $data;
    }
}