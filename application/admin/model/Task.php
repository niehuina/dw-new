<?php 

namespace app\admin\model; 

use think\Model; 

class Task extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'task'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}