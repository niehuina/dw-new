<?php

namespace app\admin\common;


class Common
{
    public static function get_permissions()
    {
        $system = array('id' => 'system', 'text' => '系统');

        $permission = array('id' => 'system.block1', 'text' => '系统设置');
        $permission['children'][] = array('id' => 'system.user', 'text' => '后台用户');
        $permission['children'][] = array('id' => 'system.role', 'text' => '角色管理');
//        $permission['children'][] = array('id' => 'system.setting', 'text' => '配置管理');
        $permission['children'][] = array('id' => 'system.banner', 'text' => '轮播图管理');
        $permission['children'][] = array('id' => 'system.website', 'text' => '网站信息');
        $permission['children'][] = array('id' => 'system.link', 'text' => '友情链接');
        $permission['children'][] = array('id' => 'system.cache', 'text' => '缓存管理');
        $system['children'][] = $permission;
        $permission = array('id' => 'system.block2', 'text' => '基础设置');
        $permission['children'][] = array('id' => 'system.department', 'text' => '部门组织机构');
        $permission['children'][] = array('id' => 'system.position', 'text' => '职务管理');
        $permission['children'][] = array('id' => 'system.web_user', 'text' => '人员管理');
        $permission['children'][] = array('id' => 'system.organization', 'text' => '党组织机构');
        $permission['children'][] = array('id' => 'system.party_member', 'text' => '党员管理');
        $system['children'][] = $permission;

        $permission = array('id' => 'system.block5', 'text' => '党务检务公开');
        $permission['children'][] = array('id' => 'system.score_item', 'text' => '党员积分项目');
        $permission['children'][] = array('id' => 'system.score_history_review', 'text' => '党员积分审核');
        $permission['children'][] = array('id' => 'system.should_pay_list', 'text' => '党费缴纳统计');
        $system['children'][] = $permission;

        $permission = array('id' => 'system.block6', 'text' => '休假管理');
        $permission['children'][] = array('id' => 'system.vacation_record', 'text' => '人员休假');
        $permission['children'][] = array('id' => 'system.vacation_statistical', 'text' => '休假统计');
        $system['children'][] = $permission;

        $permission = array('id' => 'system.block3', 'text' => '任务分配监督');
        $permission['children'][] = array('id' => 'system.task_type', 'text' => '任务分类');
        $permission['children'][] = array('id' => 'system.task', 'text' => '任务发布');
        $permission['children'][] = array('id' => 'system.task_item_review', 'text' => '项目完成情况审核');
        $system['children'][] = $permission;
        $permission = array('id' => 'system.block4', 'text' => '检查管办案信息');
        $permission['children'][] = array('id' => 'system.case_info', 'text' => '案件信息管理');
        $permission['children'][] = array('id' => 'system.case_info_search', 'text' => '个人办案明细');
        $permission['children'][] = array('id' => 'system.case_info_report', 'text' => '年度办案统计');
        $system['children'][] = $permission;

        $permission = array('id' => 'system.block7', 'text' => '信息发布');
        $permission['children'][] = array('id' => 'system.section', 'text' => '栏目设置');
        $permission['children'][] = array('id' => 'system.section_info', 'text' => '信息发布');
        $permission['children'][] = array('id' => 'system.news', 'text' => '新闻资讯');
        $permission['children'][] = array('id' => 'system.publicity_court', 'text' => '出庭公示');
        $permission['children'][] = array('id' => 'system.politic_life', 'text' => '党内政治生活');
        //$permission['children'][] = array('id' => 'system.party_affairs', 'text' => '党务检务公开');
        $system['children'][] = $permission;

        $permission = array('id' => 'system.block8', 'text' => '信息调研宣传');
        $permission['children'][] = array('id' => 'system.publish_type', 'text' => '分类管理');
        $permission['children'][] = array('id' => 'system.publish_info', 'text' => '公示发布');
        $permission['children'][] = array('id' => 'system.publish_info_review', 'text' => '公示审核');
        $system['children'][] = $permission;

        return $system;
    }

    public static function get_menu_permissions($is_admin, $permissions)
    {
        $menus = array();
        /*if ($is_admin) {
            array_push($menus, array('code', '代码管理', 'home'));
        }*/
        $menu = array('', '系统设置', 'gears');
        if ($is_admin || in_array('system.user', $permissions)) {
            $menu['children'][] = array('user', '后台用户');
        }
        if ($is_admin || in_array('system.role', $permissions)) {
            $menu['children'][] = array('role', '角色管理');
        }
//        if ($is_admin || in_array('system.setting', $permissions)) {
//            $menu['children'][] = array('setting', '配置管理');
//        }
        if ($is_admin || in_array('system.banner', $permissions)) {
            $menu['children'][] = array('banner', '轮播图管理');
        }
        if ($is_admin || in_array('system.website', $permissions)) {
            $menu['children'][] = array('website', '网站信息');
        }
        if ($is_admin || in_array('system.link', $permissions)) {
            $menu['children'][] = array('link', '友情链接');
        }
        if ($is_admin || in_array('system.cache', $permissions)) {
            $menu['children'][] = array('cache', '缓存管理');
        }
        if (isset($menu['children']))
            array_push($menus, $menu);

        $menu = array('', '基础设置', 'gear');
        if ($is_admin || in_array('system.department', $permissions)) {
            $menu['children'][] = array('department', '部门组织机构');
        }
        if ($is_admin || in_array('system.position', $permissions)) {
            $menu['children'][] = array('position', '职务管理');
        }
        if ($is_admin || in_array('system.web_user', $permissions)) {
            $menu['children'][] = array('web_user', '人员管理');
        }
        if ($is_admin || in_array('system.organization', $permissions)) {
            $menu['children'][] = array('organization', '党组织机构');
        }
        if ($is_admin || in_array('system.party_member', $permissions)) {
            $menu['children'][] = array('party_member', '党员管理');
        }

        if (isset($menu['children']))
            array_push($menus, $menu);

        $menu = array('', '党务检务公开', 'briefcase');
        if ($is_admin || in_array('system.score_item', $permissions)) {
            $menu['children'][] = array('score_item', '党员积分项目');
        }
        if ($is_admin || in_array('system.score_history_review', $permissions)) {
            $menu['children'][] = array('score_history_review', '党员积分审核');
        }

        if ($is_admin || in_array('system.should_pay_list', $permissions)) {
            $menu['children'][] = array('should_pay_list', '党费缴纳统计');
        }

        if (isset($menu['children']))
            array_push($menus, $menu);

        $menu = array('', '休假管理', 'photo');
        if ($is_admin || in_array('system.vacation_record', $permissions)) {
            $menu['children'][] = array('vacation_record', '人员休假');
        }
        if ($is_admin || in_array('system.vacation_statistical', $permissions)) {
            $menu['children'][] = array('vacation_statistical', '休假统计');
        }

        if (isset($menu['children']))
            array_push($menus, $menu);

        $menu = array('', '任务分配监督', 'newspaper-o');
        if ($is_admin || in_array('system.task_type', $permissions)) {
            $menu['children'][] = array('task_type', '任务分类');
        }
        if ($is_admin || in_array('system.task', $permissions)) {
            $menu['children'][] = array('task', '任务发布');
        }
        if ($is_admin || in_array('system.task_item_review', $permissions)) {
            $menu['children'][] = array('task_item_review', '项目完成情况审核');
        }
        if (isset($menu['children']))
            array_push($menus, $menu);


        $menu = array('', '检察官办案信息', 'server');
        if ($is_admin || in_array('system.case_info', $permissions)) {
            $menu['children'][] = array('case_info', '案件信息管理');
        }
        if ($is_admin || in_array('system.case_info_search', $permissions)) {
            $menu['children'][] = array('case_info_search', '个人办案明细');
        }
        if ($is_admin || in_array('system.case_info_report', $permissions)) {
            $menu['children'][] = array('case_info_report', '年度办案统计');
        }

        if (isset($menu['children']))
            array_push($menus, $menu);

        $menu = array('', '信息发布', 'id-card');
        if ($is_admin || in_array('system.section', $permissions)) {
            $menu['children'][] = array('section', '栏目设置');
        }
        if ($is_admin || in_array('system.section_info', $permissions)) {
            $menu['children'][] = array('section_info', '信息发布');
        }
        if ($is_admin || in_array('system.publicity_court', $permissions)) {
            $menu['children'][] = array('publicity_court', '出庭公示');
        }
        if ($is_admin || in_array('system.news', $permissions)) {
            $menu['children'][] = array('news', '新闻资讯');
        }
        if ($is_admin || in_array('system.politic_life', $permissions)) {
            $menu['children'][] = array('politic_life', '党内政治生活');
        }
        /*if ($is_admin || in_array('system.party_affairs', $permissions)) {
            $menu['children'][] = array('party_affairs', '党务检务公开');
        }*/

        if (isset($menu['children']))
            array_push($menus, $menu);
        $menu = array('', '信息调研宣传', 'list-ul');
        if ($is_admin || in_array('system.publish_type', $permissions)) {
            $menu['children'][] = array('publish_type', '分类管理');
        }
        if ($is_admin || in_array('system.publish_info', $permissions)) {
            $menu['children'][] = array('publish_info', '公示发布');
        }
        if ($is_admin || in_array('system.publish_info', $permissions)) {
            $menu['children'][] = array('publish_info_review', '公示审核');
        }

        if (isset($menu['children']))
            array_push($menus, $menu);

        return $menus;
    }
}
