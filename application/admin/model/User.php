<?php

namespace app\admin\model;

use app\admin\common\Constant;
use app\admin\common\Common;
use think\Model;

class User extends Model
{
    protected $pk = 'id';
    protected $table = 'user';

    protected function base($query)
    {
        $query->where('deleted', 0);
    }

    public function role()
    {
        return $this->belongsTo('Role', 'role_id');
    }

    public function has_permission($permission)
    {
        if ($this->role_id == Constant::ROLE_ADMIN) {
            return true;
        }
        foreach ($this->role->permissions as $item) {
            if (str_replace('_', '', $item['name']) == str_replace('_', '', $permission)) {
                return true;
            }
        }
        return false;
    }

    public function default_permission()
    {
        if ($this->role_id == Constant::ROLE_ADMIN) {
            return 'index';
        }
        foreach ($this->role->permissions as $item) {
            if (!in_array($item['name'], Constant::PERMISSION_NOT_SAVE)) {
                return str_replace('system.', '', $item['name']);
            }
        }
        return false;
    }
}