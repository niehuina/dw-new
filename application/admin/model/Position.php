<?php 

namespace app\admin\model; 

use think\Model; 

class Position extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'position'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}