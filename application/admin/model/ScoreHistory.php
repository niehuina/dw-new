<?php 

namespace app\admin\model; 

use think\Model; 

class ScoreHistory extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'score_history'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}