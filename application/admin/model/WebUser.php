<?php 

namespace app\admin\model; 

use think\Model; 

class WebUser extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'web_user'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}