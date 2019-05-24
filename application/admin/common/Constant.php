<?php

namespace app\admin\common;

class Constant
{
    const SESSION_USER_ID = 'user_id', SESSION_USER_PERMISSION = 'user_permission',SESSION_USER_ROLES='user_roles';

    const ROLE_ADMIN = 1;
    const ROLE_TYPE_LIST = [2 => '学院', 1 => '学校'];
    const ROLE_TYPE_SCHOOL = 1, ROLE_TYPE_COLLEGE = 2;

    const MENU_TYPE_LIST = [0 => '跳转链接', 1 => '详情', 2 => '图文列表', 3 => '文章列表', 4 => '文本列表'];

    const PERMISSION_NOT_SAVE = ['system', 'system.block1', 'system.block2', 'system.block3', 'system.block4', 'system.block5', 'system.block6', 'system.block7', 'system.block8'];

    const ACTION_NO_AUTHORIZATION_REQUIRED = ['login', 'logout', 'unauthorized'];

    const ACTIVE_LIST = [1 => '启用', 0 => '禁用'];

    const GENDER_LIST = [1 => '男', 2 => '女'];

    const YES_NO = [1 => '是', 0 => '否'];

    const BANNER_TYPE_LIST = [1 => '首页', 2 => '新闻', 3 => '圈子'];

    const MENU_LEVEL = [1=>'主菜单',2=>'二级菜单',3=>'三级菜单'];

    const SETTING_TYPE = ['publicity_level' => '信息调研宣传公示级别', 'prosecutor'=>'检察官',
                        'support'=>'检查辅助人员', 'judicial'=>'司法行政人员','other'=>'其他'];
    const USER_TYPE_PROSECUTOR = 'prosecutor';

    const SETTING_CODE = ['website.url' => '网站链接'];

    const REVIEW_STATUS = [1 => '待审核', 2 => '审核不通过', 3 => '审核通过'];
    const REVIEW_STATUS_WATING=1;
    const REVIEW_STATUS_FAILD=2;
    const REVIEW_STATUS_SUCCESS=3;

    const REVIEW_ACTION = [3 => '通过', 2 => '不通过'];

    const TASK_STATUS = [0=>'未开始', 1=>'进行中', 2=>'完成'];
    const TASK_STATUS_OPEN=0;
    const TASK_STATUS_IN_PROGRESS=1;
    const TASK_STATUS_COMPLETED=2;

    const TO_USER_TYPE = ['all'=>'所有用户', 'dep'=>'部门机构','party'=>'党组织机构','user'=>'特定人员'];

    const VACATION_TYPES = [0=>'病假', 1=>'年休假'];
    const VACATION_TYPES_SICK=0;
    const VACATION_TYPES_YEAR=1;

    const SETION_POLITIC_LIFE = 1;
    const SETION_PARTY_AFFAIRS = 2;
    const SETION_CASE_INFO = 3;
    const SETION_PUBLISH_INFO = 4;
    const SETION_TASK_INFO = 5;
    const SETION_NEWS = 6;
    const SETION_PUBLICITY_COURT = 7;
}
