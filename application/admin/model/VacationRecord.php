<?php 

namespace app\admin\model; 

use think\Model; 

class VacationRecord extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'vacation_record'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}