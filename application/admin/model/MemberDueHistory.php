<?php 

namespace app\admin\model; 

use think\Model; 

class MemberDueHistory extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'member_due_history'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}