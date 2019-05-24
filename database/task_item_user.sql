/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50714
Source Host           : localhost:3306
Source Database       : dwms

Target Server Type    : MYSQL
Target Server Version : 50714
File Encoding         : 65001

Date: 2019-05-05 23:27:58
*/

SET FOREIGN_KEY_CHECKS=0;

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
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

