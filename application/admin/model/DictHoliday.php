<?php 

namespace app\admin\model; 

use think\Model; 

class DictHoliday extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'dict_holiday'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}