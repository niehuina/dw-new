<?php 

namespace app\admin\model; 

use think\Model; 

class TaskType extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'task_type'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}