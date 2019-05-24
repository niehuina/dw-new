<?php 

namespace app\home\model;

use think\Model;

class Banner extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'banner';

    protected function base($query)
    {
        $query->where('deleted', 0);
    }
}