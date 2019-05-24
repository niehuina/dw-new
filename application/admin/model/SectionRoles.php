<?php 

namespace app\admin\model; 

use think\Model; 

class SectionRoles extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'section_roles'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}