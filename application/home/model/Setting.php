<?php

namespace app\home\model;

use think\Model;

class Setting extends Model
{
    protected $pk = 'id';
    protected $table = 'setting';

    public static function get_list($type, $key)
    {
        if(empty($order)){
            $order = 'id asc';
        }
        $where['type'] = $type;
        $list = db('setting')->where($where)->order($order)->column('id,type,code,value',$key);
        return $list;
    }
}