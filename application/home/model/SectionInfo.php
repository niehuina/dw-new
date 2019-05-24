<?php 

namespace app\home\model;

use think\Model; 

class SectionInfo extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'section_info'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }

    public static function get_list($section_id, $map=[], $field='*', $start=0, $length=0,$order='')
    {
        if(empty($order)){
            $order = 'si.publish_time desc';
        }
        $map['si.deleted'] = 0;
        $map['sec.id|sec.parent_id'] = $section_id;
        if($length==0){
            $list = db('section_info si')
                ->join('section sec', 'sec.id=si.section_id', 'LEFT')
                ->where($map)
                ->field($field)->order($order)->select();
        }else{
            $list = db('section_info si')
                ->join('section sec', 'sec.id=si.section_id', 'LEFT')
                ->where($map)
                ->field($field)->order($order)->limit($start,$length)->select();
        }
        return $list;
    }

    public static function get_page_list($section_id, $map=[], $field, $page=1, $length=0,$order='')
    {
        if(empty($order)){
            $order = 'si.publish_time desc';
        }
        $map['si.deleted'] = 0;
        $map['sec.id|sec.parent_id'] = $section_id;

        $start = 0;
        if($page>1){
            $start = (intval($page) - 1)*$length;
        }
        if($length==0){
            $list = db('section_info si')
                ->join('section sec', 'sec.id=si.section_id', 'LEFT')
                ->join('setting','setting.code=si.level and setting.type="publicity_level"', 'LEFT')
                ->where($map)
                ->field($field)->order($order)->select();
        }else{
            $list = db('section_info si')
                ->join('section sec', 'sec.id=si.section_id', 'LEFT')
                ->join('setting','setting.code=si.level and setting.type="publicity_level"', 'LEFT')
                ->where($map)
                ->field($field)->order($order)->limit($start,$length)->select();
        }
        $count = db('section_info si')
            ->join('section sec', 'sec.id=si.section_id', 'LEFT')
            ->where($map)->count();

        $data['total_count'] = $count;
        $data['data'] = $list;
        return $data;
    }
}