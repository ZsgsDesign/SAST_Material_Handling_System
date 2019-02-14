DROP Database IF EXISTS `sastmhs`;
CREATE Database sastmhs;
USE sastmhs;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `uid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `real_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `OPENID` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `SID` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `rtime` datetime NULL DEFAULT NULL,
  `ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `credit` int(11) NULL DEFAULT NULL COMMENT '信用分',
  `is_admin` tinyint(4) UNSIGNED NULL DEFAULT 0 COMMENT '是否为管理员',
  PRIMARY KEY (`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cart
-- ----------------------------
DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart`  (
  `cid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id',
  `user` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户名',
  `item_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '物品id',
  `count` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '数量',
  PRIMARY KEY (`cid`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for item
-- ----------------------------
DROP TABLE IF EXISTS `item`;
CREATE TABLE `item`  (
  `iid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '物品id',
  `scode` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态码',
  `name` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '物品名称',
  `count` int(11) UNSIGNED NULL DEFAULT 1 COMMENT '物品数量',
  `owner` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '发布（出借）人',
  `dec` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '物品描述',
  `create_time` datetime NULL DEFAULT NULL COMMENT '发布时间',
  `limit_time` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '借用限时',
  `pic` blob NULL COMMENT '物品图片',
  `location` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '物品位置',
  `gcount` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '好评数量',
  `mcount` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '中评数量',
  `bcount` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '差评数量',
  `order_count` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '借用总数',
  `credit_limit` int(11) NULL DEFAULT NULL COMMENT '信用限制',
  PRIMARY KEY (`iid`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for order
-- ----------------------------
DROP TABLE IF EXISTS `order`;
CREATE TABLE `order`  (
  `oid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '订单号',
  `scode` tinyint(4) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态码',
  `item_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '物品id',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `renter_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '借用人id',
  `owner_review` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '出借人评价',
  `renter_review` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '借用人评价',
  `count` bigint(20) NULL COMMENT '借用的数量',
  `rent_time` datetime NULL COMMENT '借用人取用的时间',
  PRIMARY KEY (`oid`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;