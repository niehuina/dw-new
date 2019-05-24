
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for banner
-- ----------------------------
DROP TABLE IF EXISTS `banner`;
CREATE TABLE `banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `redirect_url` varchar(255) DEFAULT NULL COMMENT '图片跳转URL',
  `picture_url` varchar(255) DEFAULT NULL COMMENT '图片URL',
  `display_order` int(11) DEFAULT NULL COMMENT '排序',
  `deleted` tinyint(4) DEFAULT NULL COMMENT '是否删除',
  `created_time` datetime DEFAULT NULL COMMENT '创建时间',
  `created_user_id` int(11) DEFAULT NULL COMMENT '创建人',
  `updated_time` datetime DEFAULT NULL COMMENT '修改时间',
  `updated_user_id` int(11) DEFAULT NULL COMMENT '修改人',
  `deleted_time` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_user_id` int(11) DEFAULT NULL COMMENT '删除人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='轮播图';

-- ----------------------------
-- Table structure for case_info
-- ----------------------------
DROP TABLE IF EXISTS `case_info`;
CREATE TABLE `case_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(200) NOT NULL COMMENT '部门受案号',
  `warning` varchar(200) DEFAULT NULL COMMENT '超期预警',
  `name` varchar(500) DEFAULT NULL COMMENT '案件名称',
  `accept_time` date DEFAULT NULL COMMENT '受理时间',
  `current_stage` varchar(200) DEFAULT NULL COMMENT '当前阶段',
  `status` varchar(100) DEFAULT NULL COMMENT '案件状态',
  `due_time` date DEFAULT NULL COMMENT '到期日期',
  `over_time` date DEFAULT NULL COMMENT '办结日期',
  `complete_time` date DEFAULT NULL COMMENT '完成日期',
  `record_time` date DEFAULT NULL COMMENT '归档日期',
  `type_name` varchar(500) DEFAULT NULL COMMENT '案件类别名称',
  `web_user_id` int(11) DEFAULT NULL,
  `user_name` varchar(200) DEFAULT NULL COMMENT '承办检察官',
  `department` varchar(500) DEFAULT NULL COMMENT '办案部门',
  `cell` varchar(500) DEFAULT NULL COMMENT '办案单元',
  `type` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`,`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dict_holiday
-- ----------------------------
DROP TABLE IF EXISTS `dict_holiday`;
CREATE TABLE `dict_holiday` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `holiday` date NOT NULL,
  `type` int(11) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for link
-- ----------------------------
DROP TABLE IF EXISTS `link`;
CREATE TABLE `link` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) DEFAULT NULL COMMENT '网站名称',
  `redirect_url` varchar(255) DEFAULT NULL,
  `picture_url` varchar(255) DEFAULT NULL COMMENT '网站logo',
  `display_order` int(11) DEFAULT NULL COMMENT '排序',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `created_user_id` int(11) DEFAULT NULL COMMENT '创建人',
  `updated_time` datetime DEFAULT NULL COMMENT '修改时间',
  `updated_user_id` int(11) DEFAULT NULL COMMENT '修改人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='友情链接';

-- ----------------------------
-- Table structure for member_due_history
-- ----------------------------
DROP TABLE IF EXISTS `member_due_history`;
CREATE TABLE `member_due_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `web_user_id` int(11) NOT NULL COMMENT '党员姓名',
  `year` int(11) NOT NULL COMMENT '缴纳年',
  `month` int(11) NOT NULL COMMENT '缴纳月',
  `money` decimal(10,2) NOT NULL COMMENT '缴纳金额',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for member_should_pay
-- ----------------------------
DROP TABLE IF EXISTS `member_should_pay`;
CREATE TABLE `member_should_pay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `web_user_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `money` decimal(10,2) DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT '0',
  `created_time` datetime DEFAULT NULL COMMENT '创建时间',
  `created_user_id` int(11) DEFAULT NULL COMMENT '创建人',
  `updated_time` datetime DEFAULT NULL COMMENT '修改时间',
  `updated_user_id` int(11) DEFAULT NULL COMMENT '修改人',
  `deleted_time` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_user_id` int(11) DEFAULT NULL COMMENT '删除人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for notification
-- ----------------------------
DROP TABLE IF EXISTS `notification`;
CREATE TABLE `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noti_type` varchar(50) DEFAULT NULL COMMENT '消息类型',
  `web_user_id` int(11) DEFAULT NULL COMMENT '用户',
  `subject` varchar(500) DEFAULT NULL COMMENT '主题',
  `content` varchar(2000) DEFAULT NULL COMMENT '内容',
  `task_id` int(11) DEFAULT NULL COMMENT '任务',
  `is_read` tinyint(1) DEFAULT '0' COMMENT '是否阅读',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for organization
-- ----------------------------
DROP TABLE IF EXISTS `organization`;
CREATE TABLE `organization` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL COMMENT '上级组织机构',
  `number` varchar(200) NOT NULL COMMENT '机构编号',
  `name` varchar(200) NOT NULL COMMENT '机构名称',
  `remark` varchar(2000) NOT NULL COMMENT '备注',
  `type` tinyint(1) NOT NULL COMMENT '机构类型',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组织机构';

-- ----------------------------
-- Table structure for party_member
-- ----------------------------
DROP TABLE IF EXISTS `party_member`;
CREATE TABLE `party_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `web_user_id` int(11) NOT NULL COMMENT '用户名',
  `organ_id` int(11) NOT NULL COMMENT '党组织机构',
  `join_time` date DEFAULT NULL COMMENT '入党时间',
  `score` int(11) NOT NULL DEFAULT '0' COMMENT '当前积分',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='人员信息';

-- ----------------------------
-- Table structure for permission
-- ----------------------------
DROP TABLE IF EXISTS `permission`;
CREATE TABLE `permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `role_id` int(11) NOT NULL COMMENT '角色ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '权限编码',
  `created_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for position
-- ----------------------------
DROP TABLE IF EXISTS `position`;
CREATE TABLE `position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(100) NOT NULL COMMENT '职务名称',
  `name` varchar(200) NOT NULL COMMENT '职务名称',
  `remark` varchar(2000) DEFAULT NULL COMMENT '备注',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='职务';

-- ----------------------------
-- Table structure for publish_type
-- ----------------------------
DROP TABLE IF EXISTS `publish_type`;
CREATE TABLE `publish_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '分类名称',
  `display` int(11) DEFAULT NULL COMMENT '显示顺序',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for role
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) DEFAULT NULL COMMENT '数据级别',
  `name` varchar(255) NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) NOT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES ('1', '1', '管理员', '0', '2017-10-13 16:25:22', '1', null, null, null, null);

-- ----------------------------
-- Table structure for score_history
-- ----------------------------
DROP TABLE IF EXISTS `score_history`;
CREATE TABLE `score_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `web_user_id` int(11) NOT NULL COMMENT '党员',
  `score_item_id` int(11) NOT NULL COMMENT '积分项目',
  `review_user_id` int(11) DEFAULT NULL COMMENT '审核人员',
  `review_time` datetime DEFAULT NULL COMMENT '审核时间',
  `review_status` int(11) DEFAULT NULL COMMENT '审核状态',
  `review_comment` varchar(2000) DEFAULT NULL COMMENT '审核意见',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for score_item
-- ----------------------------
DROP TABLE IF EXISTS `score_item`;
CREATE TABLE `score_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '项目编号',
  `name` varchar(200) NOT NULL COMMENT '积分项目名称',
  `score` int(11) NOT NULL COMMENT '对应分值',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='党员积分项目';

-- ----------------------------
-- Table structure for section
-- ----------------------------
DROP TABLE IF EXISTS `section`;
CREATE TABLE `section` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '栏目编号',
  `parent_id` int(11) NOT NULL COMMENT '父栏目',
  `name` varchar(200) NOT NULL COMMENT '栏目名称',
  `tf_show_index` tinyint(1) NOT NULL COMMENT '是否首页显示',
  `sort` decimal(10,0) DEFAULT NULL COMMENT '排序',
  `url` varchar(255) DEFAULT NULL COMMENT '栏目跳转网址',
  `role_ids` varchar(2000) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='信息栏目';

-- ----------------------------
-- Records of section
-- ----------------------------
INSERT INTO `section` VALUES ('1', '0', '党内政治生活', '1', '1', null, null, '0', '2019-04-14 20:36:43', null, null, null, null, null);
INSERT INTO `section` VALUES ('2', '0', '党务检务公开', '1', '2', null, null, '0', '2019-04-14 20:36:43', null, null, null, null, null);
INSERT INTO `section` VALUES ('3', '0', '办案公示', '1', '3', null, null, '0', '2019-04-14 20:36:43', null, null, null, null, null);
INSERT INTO `section` VALUES ('4', '0', '信息调研宣传公示', '1', '4', null, null, '0', '2019-04-14 20:36:43', null, null, null, null, null);
INSERT INTO `section` VALUES ('5', '0', '任务分配监督', '1', '5', null, null, '0', '2019-04-14 20:36:43', null, null, null, null, null);
INSERT INTO `section` VALUES ('6', '0', '新闻资讯', '0', '6', null, null, '0', '2019-04-14 21:01:04', null, null, null, null, null);
INSERT INTO `section` VALUES ('7', '0', '出庭公告', '0', '7', null, null, '0', '2019-04-18 23:01:35', null, null, null, null, null);

-- ----------------------------
-- Table structure for section_info
-- ----------------------------
DROP TABLE IF EXISTS `section_info`;
CREATE TABLE `section_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL COMMENT '信息栏目',
  `publish_type_id` int(11) DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `publish_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发布时间',
  `user_id` int(11) DEFAULT NULL COMMENT '发布人',
  `web_user_id` int(11) DEFAULT NULL,
  `picture_url` varchar(2000) DEFAULT NULL COMMENT '信息主图',
  `title` varchar(2000) DEFAULT NULL COMMENT '信息标题',
  `summary` varchar(2000) DEFAULT NULL COMMENT '信息概要说明',
  `content` longtext COMMENT '信息内容',
  `status` tinyint(1) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  `review_user_id` int(11) DEFAULT NULL COMMENT '审核人员',
  `review_time` datetime DEFAULT NULL COMMENT '审核时间',
  `review_status` int(11) DEFAULT NULL COMMENT '审核状态',
  `review_comment` varchar(2000) DEFAULT NULL COMMENT '审核意见',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='信息发布';

-- ----------------------------
-- Table structure for section_roles
-- ----------------------------
DROP TABLE IF EXISTS `section_roles`;
CREATE TABLE `section_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL COMMENT '栏目',
  `role_id` int(11) NOT NULL COMMENT '角色',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for setting
-- ----------------------------
DROP TABLE IF EXISTS `setting`;
CREATE TABLE `setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` varchar(255) DEFAULT NULL COMMENT '类型',
  `code` varchar(255) DEFAULT NULL COMMENT '编码',
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT '值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of setting
-- ----------------------------
INSERT INTO `setting` VALUES ('1', 'publicity_level', 'national', '国家级');
INSERT INTO `setting` VALUES ('2', 'publicity_level', 'provincial', '省级');
INSERT INTO `setting` VALUES ('3', 'publicity_level', 'municipal', '市级');
INSERT INTO `setting` VALUES ('4', 'user_type', 'prosecutor', '检察长');
INSERT INTO `setting` VALUES ('5', 'user_type', 'prosecutor', '副检察长');
INSERT INTO `setting` VALUES ('6', 'user_type', 'prosecutor', '检察委员会专职委员');
INSERT INTO `setting` VALUES ('7', 'user_type', 'prosecutor', '检察官');
INSERT INTO `setting` VALUES ('8', 'user_type', 'judicial', '司法行政人员');
INSERT INTO `setting` VALUES ('9', 'user_type', 'judicial', '行政管理');
INSERT INTO `setting` VALUES ('10', 'user_type', 'judicial', '后勤服务');
INSERT INTO `setting` VALUES ('11', 'user_type', 'judicial', '党务工作');
INSERT INTO `setting` VALUES ('12', 'user_type', 'judicial', '纪检监督');
INSERT INTO `setting` VALUES ('13', 'user_type', 'judicial', '其他司法行政人员');
INSERT INTO `setting` VALUES ('14', 'user_type', 'support', '检查辅助人员');
INSERT INTO `setting` VALUES ('15', 'user_type', 'support', '检察官助理');
INSERT INTO `setting` VALUES ('16', 'user_type', 'support', '书记员');
INSERT INTO `setting` VALUES ('17', 'user_type', 'support', '司法警察');
INSERT INTO `setting` VALUES ('18', 'user_type', 'support', '检查技术人员');
INSERT INTO `setting` VALUES ('19', 'user_type', 'other', '其他工作人员');
INSERT INTO `setting` VALUES ('20', 'website', 'website_logo', '\\public\\upload\\banner\\20190427\\ccfd6cb5624d395a331fbecdea8f3f94.png');
INSERT INTO `setting` VALUES ('21', 'website', 'website_name', '利津县人民检察院');
INSERT INTO `setting` VALUES ('22', 'website', 'regist_info', '鲁ICP备XXXXXX号-3 地址 : 利津县XXXXXX 技术服务热线 : XXX-XXX-XXXX');
INSERT INTO `setting` VALUES ('23', 'website', 'copy_right', 'Copyright 2018-2019 利津县人民检察院 All rights reserved');
INSERT INTO `setting` VALUES ('24', 'website', 'website_policy', '');

-- ----------------------------
-- Table structure for task
-- ----------------------------
DROP TABLE IF EXISTS `task`;
CREATE TABLE `task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL COMMENT '任务分类',
  `publish_user_id` int(11) NOT NULL COMMENT '发布人',
  `publish_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '发布时间',
  `title` varchar(2000) NOT NULL COMMENT '任务标题',
  `summary` varchar(2000) DEFAULT NULL COMMENT '任务概要说明',
  `end_time` date DEFAULT NULL COMMENT '任务结束时间',
  `content` longtext COMMENT '任务内容',
  `status` tinyint(4) DEFAULT '0' COMMENT '任务状态',
  `to_user_type` varchar(20) DEFAULT NULL COMMENT '分配类型',
  `to_type_id` varchar(2000) DEFAULT NULL COMMENT '分配类型id',
  `to_user_id` varchar(2000) DEFAULT NULL COMMENT '分配特定人',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='任务发布';

-- ----------------------------
-- Table structure for task_item
-- ----------------------------
DROP TABLE IF EXISTS `task_item`;
CREATE TABLE `task_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL COMMENT '任务名称',
  `name` varchar(200) NOT NULL COMMENT '项目名称',
  `sort` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `complete_description` varchar(2000) DEFAULT NULL,
  `complete_web_user_id` int(11) DEFAULT NULL,
  `complete_time` datetime DEFAULT NULL,
  `review_user_id` int(11) DEFAULT NULL COMMENT '审核人员',
  `review_time` datetime DEFAULT NULL COMMENT '审核时间',
  `review_status` int(11) DEFAULT NULL COMMENT '审核状态',
  `review_comment` varchar(2000) DEFAULT NULL COMMENT '审核意见',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='任务项目';

-- ----------------------------
-- Table structure for task_item_attachment
-- ----------------------------
DROP TABLE IF EXISTS `task_item_attachment`;
CREATE TABLE `task_item_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_item_id` int(11) NOT NULL COMMENT '任务项目名称',
  `web_user_id` int(11) DEFAULT NULL,
  `attachment_url` varchar(1000) NOT NULL COMMENT '附件',
  `attachment_name` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for task_item_user
-- ----------------------------
DROP TABLE IF EXISTS `task_item_user`;
CREATE TABLE `task_item_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) DEFAULT NULL,
  `task_item_id` int(11) DEFAULT NULL,
  `web_user_id` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `complete_description` varchar(2000) DEFAULT NULL,
  `complete_web_user_id` int(11) DEFAULT NULL,
  `complete_time` datetime DEFAULT NULL,
  `review_user_id` int(11) DEFAULT NULL COMMENT '审核人员',
  `review_time` datetime DEFAULT NULL COMMENT '审核时间',
  `review_status` int(11) DEFAULT NULL COMMENT '审核状态',
  `review_comment` varchar(2000) DEFAULT NULL COMMENT '审核意见',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for task_type
-- ----------------------------
DROP TABLE IF EXISTS `task_type`;
CREATE TABLE `task_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '分类编号',
  `code` varchar(50) DEFAULT NULL COMMENT '分类编号',
  `name` varchar(200) NOT NULL COMMENT '分类名称',
  `parent_id` int(11) NOT NULL COMMENT '上级分类',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='任务分类';

-- ----------------------------
-- Table structure for task_user
-- ----------------------------
DROP TABLE IF EXISTS `task_user`;
CREATE TABLE `task_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL COMMENT '任务名称',
  `web_user_id` int(11) NOT NULL COMMENT '任务人员',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL COMMENT '职务',
  `email` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('1', 'admin', 'c4ca4238a0b923820dcc509a6f75849b', '管理员', null, null, null, '1', null, '1', '0', null, null, null, null, null, null);

-- ----------------------------
-- Table structure for vacation_record
-- ----------------------------
DROP TABLE IF EXISTS `vacation_record`;
CREATE TABLE `vacation_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `web_user_id` int(11) NOT NULL COMMENT '人员姓名',
  `start_time` date DEFAULT NULL COMMENT '休假开始日期',
  `end_time` date DEFAULT NULL COMMENT '休假结束日期',
  `days` double DEFAULT NULL COMMENT '休假天数',
  `year` int(11) DEFAULT NULL,
  `type` int(11) NOT NULL COMMENT '休假类型',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='人员休假';

-- ----------------------------
-- Table structure for web_user
-- ----------------------------
DROP TABLE IF EXISTS `web_user`;
CREATE TABLE `web_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL COMMENT '真实姓名',
  `id_number` varchar(20) NOT NULL COMMENT '身份证号',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `sex` varchar(10) DEFAULT NULL COMMENT '性别',
  `birthday` date DEFAULT NULL COMMENT '出生年月',
  `entry_time` date DEFAULT NULL COMMENT '入职时间',
  `user_type` int(11) DEFAULT NULL COMMENT '人员身份类型',
  `organ_id` int(11) DEFAULT NULL COMMENT '所属部门',
  `position_ids` varchar(2000) DEFAULT NULL COMMENT '职务',
  `active` tinyint(1) DEFAULT '1',
  `deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `deleted_time` datetime DEFAULT NULL,
  `deleted_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='人员信息';

-- ----------------------------
-- Function structure for getTaskTypeChildrens
-- ----------------------------
DROP FUNCTION IF EXISTS `getTaskTypeChildrens`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` FUNCTION `getTaskTypeChildrens`(orgid INT) RETURNS varchar(4000) CHARSET utf8
BEGIN
	DECLARE oTemp VARCHAR(4000);
	DECLARE oTempChild VARCHAR(4000);
	 
	SET oTemp = '';
	SET oTempChild = CAST(orgid AS CHAR);

	WHILE oTempChild IS NOT NULL
		DO
		IF oTemp = '' THEN
			SET oTemp = CONCAT(oTemp,oTempChild);
		ELSE
			SET oTemp = CONCAT(oTemp,',',oTempChild);
		END IF;
		SELECT GROUP_CONCAT(id) INTO oTempChild FROM task_type WHERE FIND_IN_SET(parent_id,oTempChild) > 0;
	END WHILE;
	RETURN oTemp;
END
;;
DELIMITER ;
