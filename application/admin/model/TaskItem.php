<?php 

namespace app\admin\model; 

use think\Model; 

class TaskItem extends Model
{ 
    protected $pk = 'id'; 
    protected $table = 'task_item';

    protected function base($query)
    {
        $query->where('deleted', 0);
    }
}