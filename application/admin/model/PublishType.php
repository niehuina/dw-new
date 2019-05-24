<?php 

namespace app\admin\model; 

use think\Model; 

class PublishType extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'publish_type'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}