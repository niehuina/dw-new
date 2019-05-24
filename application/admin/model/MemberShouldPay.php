<?php 

namespace app\admin\model; 

use think\Model; 

class MemberShouldPay extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'member_should_pay'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}