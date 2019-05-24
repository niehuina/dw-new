<?php 

namespace app\admin\model; 

use think\Model; 

class PartyMember extends Model 
{ 
    protected $pk = 'id'; 
    protected $table = 'party_member'; 

    protected function base($query)
    {
        $query->where('deleted', 0); 
    }
}