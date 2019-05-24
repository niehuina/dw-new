<?php 

namespace app\admin\model; 

use think\Model; 

class Section extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'section'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}