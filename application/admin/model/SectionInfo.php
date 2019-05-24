<?php 

namespace app\admin\model; 

use think\Model; 

class SectionInfo extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'section_info'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}