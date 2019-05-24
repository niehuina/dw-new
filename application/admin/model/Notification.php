<?php 

namespace app\admin\model; 

use think\Model; 

class Notification extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'notification'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}