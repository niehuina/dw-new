<?php

namespace app\admin\controller;


use app\admin\common\Cache;
use app\admin\common\Constant;
use app\admin\common\Common;
use app\admin\model\User;

class BaseController extends \think\Controller
{

    var $userId, $user, $dataPermission, $selectPermission, $isAdmin, $isGlobal;
    var $can_edit = false;

    protected function _initialize()
    {
        $controller_name = strtolower($this->request->controller());
        if($controller_name=="scorehistory"){
            $controller_name="partymember";
        }
        $action_name = strtolower($this->request->action());
        if (!in_array($action_name, Constant::ACTION_NO_AUTHORIZATION_REQUIRED)) {
            if (session(Constant::SESSION_USER_ID)) {
                $user_id = session(Constant::SESSION_USER_ID);
                $user = User::get(['id' => $user_id, 'active' => 1, 'deleted' => 0]);
                if (!empty($user)) {
                    $permission = session(Constant::SESSION_USER_PERMISSION);
                    $is_admin = $user['role_id'] == Constant::ROLE_ADMIN;

                    if ($is_admin || $controller_name == 'common' || $user->has_permission('system.' . $controller_name)) {
                        $this->user = $user;
                        $this->userId = $user_id;
                        $this->isAdmin = $is_admin;

                        $menus = Common::get_menu_permissions($is_admin, $permission['menu_permission']);

                        $this->assign('menu', $menus);
                        $this->assign('user', $user);
                    } else {
                        $this->redirect(url('index/unauthorized'));
                    }
                } else {
                    $this->redirect(url('index/login'));
                }
            } else {
                $this->redirect(url('index/login'));
            }
        }
    }

    protected function process_query_hasdate($key_field, $date_field = '')
    {
        $query = $this->request->param('query');
        $map = array();
        if (!empty($query)) {
            $result['date'] = array();
            $result['key'] = array();
            $list = explode(' ', $query);
            foreach ($list as $item) {
                if (!empty($item)) {
                    $length = strlen($item);
                    //数字日期判断20170905 170806
                    if (is_numeric($item) && ($length == 6 || $length == 8) && $this->check_date($item)) {
                        $result['date'] = ['between', [$this->check_date($item), $this->check_date($item) . ' 23:59:59']];
                    } elseif ((strpos($item, '-') == 0 || strpos($item, '-') == $length - 1) && is_numeric(str_replace('-', '', $item)) && ($length == 7 || $length == 9) && $this->check_date(str_replace('-', '', $item))) {//日期单独范围判断 -20170909 20170808-
                        if (strpos($item, '-') == 0) {
                            $result['date'] = ['lt', $this->check_date(str_replace('-', '', $item)) . ' 23:59:59'];
                        } else {
                            $result['date'] = ['gt', $this->check_date(str_replace('-', '', $item))];
                        }
                    } elseif ($length == 13 && strpos($item, '-') == 6 && is_numeric(str_replace('-', '', $item)) && $this->check_date(substr($item, 0, 6)) && $this->check_date(substr($item, 7, 6))) {
                        $start = $this->check_date(substr($item, 0, 6));
                        $end = $this->check_date(substr($item, 7, 6)) . ' 23:59:59';
                        $result['date'] = ['between', [$start, $end]];
                    } elseif ($length == 17 && strpos($item, '-') == 8 && is_numeric(str_replace('-', '', $item)) && $this->check_date(substr($item, 0, 8)) && $this->check_date(substr($item, 9, 9))) {
                        $start = $this->check_date(substr($item, 0, 8));
                        $end = $this->check_date(substr($item, 9, 8)) . ' 23:59:59';
                        $result['date'] = ['between', [$start, $end]];
                    } else {
                        array_push($result['key'], '%' . $item . '%');
                    }
                }
            }


            if (!empty($result['key'])) {
                $map[$key_field] = ['like', $result['key'], 'OR'];
            }
            if (!empty($date_field) && !empty($result['date'])) {
                $map[$date_field] = $result['date'];
            }
        }

        return $map;
    }

    protected function process_query($key_field)
    {
        $query = $this->request->param('query');
        $map = array();
        if (!empty($query)) {
            $result['key'] = array();
            $list = explode(' ', $query);
            foreach ($list as $item) {
                if (!empty($item)) {
                    array_push($result['key'], '%' . $item . '%');
                }
            }

            if (!empty($result['key'])) {
                $map[$key_field] = ['like', $result['key'], 'OR'];
            }
        }

        return $map;
    }

    private function check_date($number)
    {
        $length = strlen($number);
        $day = substr($number, $length - 2, 2);
        $month = substr($number, $length - 4, 2);
        if ($length == 6) {
            $year = '20' . substr($number, 0, 2);
        } else {
            $year = substr($number, 0, 4);
        }

        $date = $year . '-' . $month . '-' . $day;
        $unixTime = strtotime($date);
        if (!$unixTime) {
            return false;
        }
        $fomart = "Y-m-d";
        if (date($fomart, $unixTime) == $date) {
            return $date;
        }

        return false;
    }

    protected function tf_has_section_permissions($section_id)
    {
        if($this->isAdmin){
            return true;
        }

        $user_roles=session(Constant::SESSION_USER_ROLES);
        $user_roles=explode(",",$user_roles);
        $map['section_roles.role_id'] = array('in',$user_roles);
        $map['section_roles.section_id'] = $section_id;
        $recordCount = db('section_roles')
            ->where($map)->count();

        if($recordCount){
            return true;
        }

        return false;
    }

}