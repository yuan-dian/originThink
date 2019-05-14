/*
MySQL Backup
Source Server Version: 5.7.14
Source Database: originthink
Date: 2018/9/9 16:19:13
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
--  Table structure for `think_auth_group`
-- ----------------------------
DROP TABLE IF EXISTS `think_auth_group`;
CREATE TABLE `think_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` char(100) NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：为1正常，为0禁用',
  `rules` char(80) NOT NULL DEFAULT '' COMMENT '用户组拥有的规则id， 多个规则","隔开',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `think_auth_group_access`
-- ----------------------------
DROP TABLE IF EXISTS `think_auth_group_access`;
CREATE TABLE `think_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL COMMENT '用户id',
  `group_id` mediumint(8) unsigned NOT NULL COMMENT '用户组id',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `think_auth_rule`
-- ----------------------------
DROP TABLE IF EXISTS `think_auth_rule`;
CREATE TABLE `think_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '规则唯一标识',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '规则中文名称',
  `pid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：为1正常，为0禁用',
  `condition` char(100) NOT NULL DEFAULT '' COMMENT '规则表达式，为空表示存在就验证，不为空表示按照条件验证',
  `menu` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否是菜单；0:否，1:是',
  `icon` varchar(255) DEFAULT NULL COMMENT '菜单图标',
  `sort` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `name` (`name`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `think_config`
-- ----------------------------
DROP TABLE IF EXISTS `think_config`;
CREATE TABLE `think_config` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(255) NOT NULL COMMENT '配置字段名',
  `title` varchar(255) NOT NULL COMMENT '配置标题名称',
  `value` varchar(255) NOT NULL COMMENT '配置参数',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `think_login_log`
-- ----------------------------
DROP TABLE IF EXISTS `think_login_log`;
CREATE TABLE `think_login_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(11) unsigned NOT NULL COMMENT '用户id',
  `user` varchar(255) NOT NULL COMMENT '账号',
  `name` varchar(255) NOT NULL COMMENT '用户名',
  `last_login_ip` varchar(32) NOT NULL COMMENT '登录ip',
  `create_time` int(11) unsigned NOT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `think_user`
-- ----------------------------
DROP TABLE IF EXISTS `think_user`;
CREATE TABLE `think_user` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user` varchar(32) NOT NULL COMMENT '账号',
  `name` varchar(255) NOT NULL COMMENT '用户名',
  `head` varchar(255) DEFAULT NULL COMMENT '头像',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `login_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `last_login_ip` varchar(32) NOT NULL DEFAULT '0.0.0.0' COMMENT '最后登录ip地址',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否禁用；0: 禁用 1:正常',
  `updatapassword` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `user_unique` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records 
-- ----------------------------
INSERT INTO `think_auth_group` VALUES ('1','管理员组','1','1,2,3,4,5,6,8,9,10','0','1494407780'), ('2','普通用户组','1','1,2,3,4,10,13,14,18,19','0','1494308736');
INSERT INTO `think_auth_group_access` VALUES ('1','1'), ('2','2');
INSERT INTO `think_auth_rule` VALUES (1, '#', '首页', 0, 1, 1, '', 1, 'layui-icon layui-icon-home', 1),(2, '#', '用户管理', 0, 1, 1, '', 1, 'layui-icon layui-icon-user', 1),(3, 'admin/userList', '用户列表', 2, 1, 1, '', 1, NULL, 1),(4, 'admin/groupList', '用户组列表', 2, 1, 1, '', 1, NULL, 1),(5, 'admin/edit', '添加用户', 2, 1, 1, '', 0, '', 1),(6, '#', '系统管理', 0, 1, 1, '', 1, 'layui-icon layui-icon-set', 1),(7, 'admin/cleanCache', '清除缓存', 6, 1, 1, '', 1, '', 1),(8, 'admin/menu', '菜单管理', 6, 1, 1, '', 1, '', 1),(9, 'admin/home', '系统信息', 1, 1, 1, '', 1, '', 1),(10, 'admin/log', '日志管理', 6, 1, 1, '', 1, '', 1),(11, 'admin/editMenu', '编辑菜单', 6, 1, 1, '', 0, '', 1),(12, 'admin/deleteMenu', '删除菜单', 6, 1, 1, '', 0, '', 1),(13, 'admin/config', '系统配置', 6, 1, 1, '', 1, '', 1),(14, 'admin/siteConfig', '站点配置', 6, 1, 1, '', 1, '', 1),(15, 'admin/editGroup', '添加编辑用户组', 2, 1, 1, '', 0, '', 1),(16, 'admin/disableGroup', '禁用用户组', 2, 1, 1, '', 0, '', 1),(17, 'admin/ruleList', '规则列表', 2, 1, 1, '', 0, '', 1),(18, 'admin/editRule', '修改规则', 2, 1, 1, '', 0, '', 1);
INSERT INTO `think_config` VALUES ('1','system_config','系统配置','{\"debug\": \"0\", \"trace\": \"0\", \"trace_type\": \"0\"}','0','1523414007','1531729547'), ('2','site_config','站点配置','{\"name\": \"originThink\", \"title\": \"originThink\", \"copyright\": \"copyright @2018 originThink\",\"icp\": \"苏ICP备0000000号\"}','1','1523414007','1536478335');
INSERT INTO `think_user` VALUES ('1','admin','admin',NULL,'$2y$10$HLh4UHoluqLvwsNN6vQxz.tuKMA5xYp6rH2vOpA.74sxiQbjwm2My','87','127.0.0.1','1536480810','1','1','0','1536480810'), ('2','admin1','admin1',NULL,'$2y$10$HLh4UHoluqLvwsNN6vQxz.tuKMA5xYp6rH2vOpA.74sxiQbjwm2My','13','127.0.0.1','1535891091','1','1','0','1535891091');
