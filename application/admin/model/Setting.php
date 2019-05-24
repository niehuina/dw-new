<?php

namespace app\admin\model;

use think\Model;

class Setting extends Model
{
    protected $pk = 'id';
    protected $table = 'setting';
    public static function get_list($where=[], $start=0, $length=0)
    {
        if($length==0){
            $list = db('setting')->where($where)
                ->field('type,code,value')->select();
        }else{
            $list = db('setting')->where($where)
                ->field('type,code,value')->limit($start,$length)->select();
        }

        $setting_list=array();
        foreach ($list as $item)
        {
            $setting_list[$item['code']]=$item['value'];
        }
        return $setting_list;
    }

}