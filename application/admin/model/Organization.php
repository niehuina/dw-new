<?php 

namespace app\admin\model; 

use think\Model; 

class Organization extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'organization'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}