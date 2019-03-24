/*
 Navicat Premium Data Transfer

 Source Server         : 本地-root@localhost
 Source Server Type    : MySQL
 Source Server Version : 50723
 Source Host           : localhost:3306
 Source Schema         : o2o

 Target Server Type    : MySQL
 Target Server Version : 50723
 File Encoding         : 65001

 Date: 24/03/2019 10:29:20
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for o2o_admin
-- ----------------------------
DROP TABLE IF EXISTS `o2o_admin`;
CREATE TABLE `o2o_admin`  (
  `admin_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自然ID',
  `admin_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名',
  `admin_password` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
  `admin_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '邮箱',
  `admin_telephone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '电话',
  `admin_description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '管理员描述',
  `admin_role_id` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '角色',
  `admin_status` tinyint(2) UNSIGNED NULL DEFAULT 0 COMMENT '状态',
  `admin_super` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '超级管理员',
  `admin_login_ip` bigint(20) NULL DEFAULT NULL COMMENT '登录IP',
  `admin_login_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '登录时间',
  `admin_login_count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '登录次数',
  `create_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`admin_id`) USING BTREE,
  UNIQUE INDEX `admin_name`(`admin_name`) USING BTREE,
  INDEX `admin_role_id`(`admin_role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of o2o_admin
-- ----------------------------
INSERT INTO `o2o_admin` VALUES (1, 'admin1', '$2y$10$7JLuwGWDuVGywMerCgDFdu/JU3dtwNvDKu3PbzUiAJkXJ9BBBhkeu', '', '1772829283', '', 1, 1, 0, 0, 1553393149, 1, 1553158320, 1553165044, NULL);
INSERT INTO `o2o_admin` VALUES (2, 'admin', '$2y$10$VXxnfhHDOaf4VSF6ALZGq.puzcE2BTapyBFULRmv.wfFdHT244Xlm', '1@2.3', '123456748', '超级管理员', NULL, 1, 0, 0, 1553394080, 11, 1553164090, 1553165915, NULL);
INSERT INTO `o2o_admin` VALUES (3, 'admin2', '$2y$10$ak0cyhlAg/6BHWODcx8D7OAPPUgNyu9c5qlZEXInHwKynI0RU38C.', 'admin2@1234.com', '', '', 2, 1, 0, 0, 1553393313, 1, 1553393295, 1553393295, NULL);

-- ----------------------------
-- Table structure for o2o_admin_log
-- ----------------------------
DROP TABLE IF EXISTS `o2o_admin_log`;
CREATE TABLE `o2o_admin_log`  (
  `admin_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `log_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `log_ip` int(11) NULL DEFAULT NULL,
  `log_input` varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `log_permissible` tinyint(4) NULL DEFAULT NULL,
  `create_time` int(11) NULL DEFAULT NULL,
  `update_time` int(11) NULL DEFAULT NULL
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for o2o_config
-- ----------------------------
DROP TABLE IF EXISTS `o2o_config`;
CREATE TABLE `o2o_config`  (
  `config_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '配置项中文名',
  `config_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '配置项键名',
  `config_value` varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '配置项值',
  `config_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '配置类型：0是文本，1是图片，2是文件',
  `config_deletable` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否可删除，1是可被删除，0是不可删除',
  `create_time` int(10) UNSIGNED NULL DEFAULT NULL,
  `update_time` int(10) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`config_name`, `config_key`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of o2o_config
-- ----------------------------
INSERT INTO `o2o_config` VALUES ('后台关键词', 'admin_keywords', '通用管理后台', 0, 0, NULL, NULL);
INSERT INTO `o2o_config` VALUES ('后台描述', 'admin_description', '一款可以通用的后台管理系统', 0, 0, NULL, NULL);
INSERT INTO `o2o_config` VALUES ('后台标题', 'admin_title', '通用o2o系统管理后台', 0, 0, NULL, NULL);
INSERT INTO `o2o_config` VALUES ('后台页脚信息', 'admin_footer', 'Copyright &amp;copy;2019 ChenQiang All Rights Reserved.&lt;br/&gt;		本系统前端部分由		&lt;a href=&quot;http://www.h-ui.net/&quot; target=&quot;_blank&quot; title=&quot;H-ui前端框架&quot;&gt;H-ui前端框架&lt;/a&gt;		支持&lt;br/&gt;		本系统后端部分由		&lt;a href=&quot;http://www.thinkphp.cn&quot; target=&quot;_blank&quot; title=&quot;ThinkPHP&quot;&gt;ThinkPHP&lt;/a&gt;		支持', 0, 0, NULL, NULL);
INSERT INTO `o2o_config` VALUES ('备案编号', 'beian', '辽ICP备18013736号', 0, 0, NULL, NULL);
INSERT INTO `o2o_config` VALUES ('统计代码', 'tongji', '&lt;script&gt; var _hmt = _hmt || []; (function() {   var hm = document.createElement(&quot;script&quot;);   hm.src = &quot;https://hm.baidu.com/hm.js?944d4adcd460e3f09700bc2a4dc7385b&quot;;   var s = document.getElementsByTagName(&quot;script&quot;)[0];    s.parentNode.insertBefore(hm, s); })(); &lt;/script&gt;', 0, 0, NULL, NULL);
INSERT INTO `o2o_config` VALUES ('网站关键词', 'description', '在线预约线下服务的系统', 0, 0, NULL, NULL);
INSERT INTO `o2o_config` VALUES ('网站关键词', 'keywords', 'o2o,预约', 0, 0, NULL, NULL);
INSERT INTO `o2o_config` VALUES ('网站标题', 'title', '在线预约系统', 0, 0, NULL, NULL);

-- ----------------------------
-- Table structure for o2o_menu
-- ----------------------------
DROP TABLE IF EXISTS `o2o_menu`;
CREATE TABLE `o2o_menu`  (
  `menu_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '菜单ID',
  `menu_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL unique DEFAULT '' COMMENT '菜单名称',
  `menu_route` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL unique DEFAULT '' COMMENT '菜单路由',
  `menu_visible` tinyint(4) NOT NULL DEFAULT 0 COMMENT '菜单可见',
  `menu_father_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '菜单上级ID',
  `menu_sn` int(10) NOT NULL DEFAULT 0 COMMENT '菜单排序号',
  `menu_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '菜单描述',
  `create_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`menu_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 30 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of o2o_menu
-- ----------------------------
INSERT INTO `o2o_menu` VALUES (1, '系统管理', '', 1, 0, 1, '对后台系统进行管理，一般仅对超级管理员开放', 1553151822, 1553154506, NULL);
INSERT INTO `o2o_menu` VALUES (2, '后台菜单', 'admin/menu/menu_list', 1, 1, 1, '管理后台功能菜单', 1553152128, 1553154944, NULL);
INSERT INTO `o2o_menu` VALUES (3, '后台菜单-添加', 'admin/menu/menu_add', 0, 1, 0, '添加后台菜单的页面', 1553152286, 1553154944, NULL);
INSERT INTO `o2o_menu` VALUES (4, '后台菜单-添加-执行', 'admin/menu/do_menu_add', 0, 1, 0, '执行后台菜单的添加操作', 1553152323, 1553154944, NULL);
INSERT INTO `o2o_menu` VALUES (5, '后台菜单-编辑', 'admin/menu/menu_edit', 0, 1, 0, '后台菜单的编辑页面', 1553152456, 1553154944, NULL);
INSERT INTO `o2o_menu` VALUES (6, '后台菜单-编辑-执行', 'admin/menu/do_menu_edit', 0, 1, 0, '执行后台菜单的编辑操作', 1553152493, 1553154944, NULL);
INSERT INTO `o2o_menu` VALUES (7, '角色管理', 'admin/role/role_list', 1, 1, 2, '管理系统中的角色', 1553155653, 1553155653, NULL);
INSERT INTO `o2o_menu` VALUES (8, '菜单显示-切换', 'admin/menu/do_menu_visible', 0, 1, 0, '切换菜单的显示状态', 1553155736, 1553155736, NULL);
INSERT INTO `o2o_menu` VALUES (9, '后台菜单-删除-执行', 'admin/menu/do_menu_delete', 0, 1, 0, '执行后台菜单的删除操作', 1553155779, 1553325536, NULL);
INSERT INTO `o2o_menu` VALUES (10, '后台菜单-批量删除-执行', 'admin/menu/do_menu_multidelete', 0, 1, 0, '执行后台菜单的批量删除操作', 1553155817, 1553325549, NULL);
INSERT INTO `o2o_menu` VALUES (11, '后台账号', 'admin/admin/admin_list', 1, 1, 3, '管理后台账号', 1553157726, 1553157808, NULL);
INSERT INTO `o2o_menu` VALUES (12, '角色管理-添加', 'admin/role/role_add', 0, 1, 0, '添加角色界面', 1553325159, 1553325159, NULL);
INSERT INTO `o2o_menu` VALUES (13, '角色管理-添加-执行', 'admin/role/do_role_add', 0, 1, 0, '', 1553325597, 1553325597, NULL);
INSERT INTO `o2o_menu` VALUES (14, '角色管理-编辑', 'admin/role/role_edit', 0, 1, 0, '', 1553325685, 1553325692, NULL);
INSERT INTO `o2o_menu` VALUES (15, '角色管理-编辑-执行', 'admin/role/do_role_edit', 0, 1, 0, '', 1553325716, 1553325716, NULL);
INSERT INTO `o2o_menu` VALUES (16, '角色管理-删除-执行', 'admin/role/do_role_delete', 0, 1, 0, '', 1553325741, 1553325741, NULL);
INSERT INTO `o2o_menu` VALUES (17, '角色管理-批量删除-执行', 'admin/role/do_role_multidelete', 0, 1, 0, '', 1553325774, 1553325774, NULL);
INSERT INTO `o2o_menu` VALUES (18, '后台账号-添加', 'admin/admin/admin_add', 0, 1, 0, '', 1553325933, 1553325933, NULL);
INSERT INTO `o2o_menu` VALUES (19, '后台账号-编辑', 'admin/admin/admin_edit', 0, 1, 0, '', 1553325955, 1553326276, NULL);
INSERT INTO `o2o_menu` VALUES (20, '后台账号-修改密码', 'admin/admin/admin_password', 0, 1, 0, '', 1553326200, 1553326200, NULL);
INSERT INTO `o2o_menu` VALUES (21, '后台账号-添加-执行', 'admin/admin/do_admin_add', 0, 1, 0, '', 1553326232, 1553326232, NULL);
INSERT INTO `o2o_menu` VALUES (22, '后台账号-编辑-执行', 'admin/admin/do_admin_edit', 0, 1, 0, '', 1553326258, 1553326265, NULL);
INSERT INTO `o2o_menu` VALUES (23, '后台账号-修改密码-执行', 'admin/admin/do_admin_password', 0, 1, 0, '', 1553326302, 1553326302, NULL);
INSERT INTO `o2o_menu` VALUES (24, '后台账号-切换状态-执行', 'admin/admin/do_admin_status', 0, 1, 0, '', 1553326330, 1553326330, NULL);
INSERT INTO `o2o_menu` VALUES (25, '后台账号-删除-执行', 'admin/admin/do_admin_delete', 0, 1, 0, '', 1553326362, 1553326362, NULL);
INSERT INTO `o2o_menu` VALUES (26, '后台账号-批量删除-执行', 'admin/admin/do_admin_multidelete', 0, 1, 0, '', 1553326387, 1553326387, NULL);
INSERT INTO `o2o_menu` VALUES (27, '系统配置', 'admin/config/config', 1, 1, 4, '一些系统参数的配置', 1553327323, 1553327323, NULL);
INSERT INTO `o2o_menu` VALUES (28, '系统配置-编辑-执行', 'admin/config/do_config_edit', 0, 1, 0, '对系统配置项进行编辑操作', 1553328202, 1553328202, NULL);
INSERT INTO `o2o_menu` VALUES (29, '系统配置-添加-执行', 'admin/config/do_config_add', 0, 1, 0, '执行添加系统配置项的操作', 1553328281, 1553328281, NULL);

-- ----------------------------
-- Table structure for o2o_role
-- ----------------------------
DROP TABLE IF EXISTS `o2o_role`;
CREATE TABLE `o2o_role`  (
  `role_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自然ID',
  `role_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '角色名称',
  `role_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '角色说明',
  `create_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`role_id`) USING BTREE,
  UNIQUE INDEX `role_name`(`role_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of o2o_role
-- ----------------------------
INSERT INTO `o2o_role` VALUES (1, '系统管理员', '管理后台系统', 1553156223, 1553156688);
INSERT INTO `o2o_role` VALUES (2, '菜单管理员', '', 1553393236, 1553393236);

-- ----------------------------
-- Table structure for o2o_role_menu
-- ----------------------------
DROP TABLE IF EXISTS `o2o_role_menu`;
CREATE TABLE `o2o_role_menu`  (
  `role_id` int(10) UNSIGNED NOT NULL COMMENT '角色ID',
  `menu_id` int(10) UNSIGNED NOT NULL COMMENT '菜单ID',
  INDEX `role_id`(`role_id`) USING BTREE,
  INDEX `menu_id`(`menu_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of o2o_role_menu
-- ----------------------------
INSERT INTO `o2o_role_menu` VALUES (1, 1);
INSERT INTO `o2o_role_menu` VALUES (1, 2);
INSERT INTO `o2o_role_menu` VALUES (1, 7);
INSERT INTO `o2o_role_menu` VALUES (1, 3);
INSERT INTO `o2o_role_menu` VALUES (1, 4);
INSERT INTO `o2o_role_menu` VALUES (1, 5);
INSERT INTO `o2o_role_menu` VALUES (1, 6);
INSERT INTO `o2o_role_menu` VALUES (1, 8);
INSERT INTO `o2o_role_menu` VALUES (1, 11);
INSERT INTO `o2o_role_menu` VALUES (1, 12);
INSERT INTO `o2o_role_menu` VALUES (1, 9);
INSERT INTO `o2o_role_menu` VALUES (1, 10);
INSERT INTO `o2o_role_menu` VALUES (1, 13);
INSERT INTO `o2o_role_menu` VALUES (1, 14);
INSERT INTO `o2o_role_menu` VALUES (1, 15);
INSERT INTO `o2o_role_menu` VALUES (1, 16);
INSERT INTO `o2o_role_menu` VALUES (1, 17);
INSERT INTO `o2o_role_menu` VALUES (1, 18);
INSERT INTO `o2o_role_menu` VALUES (1, 20);
INSERT INTO `o2o_role_menu` VALUES (1, 21);
INSERT INTO `o2o_role_menu` VALUES (1, 22);
INSERT INTO `o2o_role_menu` VALUES (1, 19);
INSERT INTO `o2o_role_menu` VALUES (1, 23);
INSERT INTO `o2o_role_menu` VALUES (1, 24);
INSERT INTO `o2o_role_menu` VALUES (1, 25);
INSERT INTO `o2o_role_menu` VALUES (1, 26);
INSERT INTO `o2o_role_menu` VALUES (1, 27);
INSERT INTO `o2o_role_menu` VALUES (1, 28);
INSERT INTO `o2o_role_menu` VALUES (1, 29);
INSERT INTO `o2o_role_menu` VALUES (2, 1);
INSERT INTO `o2o_role_menu` VALUES (2, 2);
INSERT INTO `o2o_role_menu` VALUES (2, 4);
INSERT INTO `o2o_role_menu` VALUES (2, 9);
INSERT INTO `o2o_role_menu` VALUES (2, 6);
INSERT INTO `o2o_role_menu` VALUES (2, 10);
INSERT INTO `o2o_role_menu` VALUES (2, 8);
INSERT INTO `o2o_role_menu` VALUES (2, 5);

SET FOREIGN_KEY_CHECKS = 1;
