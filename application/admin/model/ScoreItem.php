<?php 

namespace app\admin\model; 

use think\Model; 

class ScoreItem extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'score_item'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}