<?php

namespace app\home\common;

class Constant
{
    const SESSION_USER_ID = 'web_user_id', SESSION_USER_PERMISSION = 'user_permission';

    const ROLE_ADMIN = 1;

    const CACHE_SMS_PREFIX = 'sms_code_';

    const PERMISSION_NOT_SAVE = ['system', 'system.block1', 'system.block2', 'system.block3', 'system.block4', 'system.block5'];

    const ACTION_NO_AUTHORIZATION_REQUIRED = ['login', 'logout', 'unauthorized'];

    const ACCOUNT_USER_TYPE_LIST = [1 => '老师', 2 => '学生'];

    const ACTIVE_LIST = [1 => '启用', 0 => '禁用'];

    const USER_ACTIVE = [1 => '启用', 2 => '禁用'];

    const BANNER_TYPE_LIST = [1 => '平台'];
//
//    const SETTING_TYPE = ['contact' => '联系我们'];
//    const SETTING_CODE = ['contact.email' => '邮箱', 'contact.description' => '描述'];

    const SETTING_TYPE = ['publicity_level' => '信息调研宣传公示级别', 'prosecutor'=>'检察官',
        'support'=>'检查辅助人员', 'judicial'=>'司法行政人员','other'=>'其他'];
    const SETTING_CODE = ['website.url' => '网站链接'];

    // 审核状态
    const REVIEW_STATUS = [1 => '待审核', 2 => '审核不通过', 3 => '审核通过'];
    const REVIEW_STATUS_WATING=1;
    const REVIEW_STATUS_FAILD=2;
    const REVIEW_STATUS_SUCCESS=3;

    //审核事件
    const REVIEW_ACTION = [3 => '通过', 2 => '不通过'];

    //任务，任务项目状态
    const TASK_STATUS = [0=>'待完成', 1=>'待完成', 2=>'完成'];
    const TASK_STATUS_OPEN=0;
    const TASK_STATUS_IN_PROGRESS=1;
    const TASK_STATUS_COMPLETED=2;

    //分配类型
    const TO_USER_TYPE = ['all'=>'所有用户', 'dep'=>'部门机构','party'=>'党组织机构','user'=>'特定人员'];

    //请假类型
    const VACATION_TYPES = [0=>'病假', 1=>'年休假'];
    const VACATION_TYPES_SICK = 0;
    const VACATION_TYPES_YEAR = 1;

    const SETION_POLITIC_LIFE = 1;
    const SETION_PARTY_AFFAIRS = 2;
    const SETION_CASE_INFO = 3;
    const SETION_PUBLISH_INFO = 4;
    const SETION_TASK_INFO = 5;
    const SETION_NEWS = 6;
    const SETION_PUBLICITY_COURT = 7;
}
