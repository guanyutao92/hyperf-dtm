/*
 Navicat Premium Data Transfer

 Source Server         : dockr_mysql5.7
 Source Server Type    : MySQL
 Source Server Version : 50736 (5.7.36)
 Source Host           : 192.168.0.106:3310
 Source Schema         : dtm_product

 Target Server Type    : MySQL
 Target Server Version : 50736 (5.7.36)
 File Encoding         : 65001

 Date: 11/09/2023 14:52:16
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for barrier
-- ----------------------------
DROP TABLE IF EXISTS `barrier`;
CREATE TABLE `barrier`  (
  `id` bigint(22) NOT NULL AUTO_INCREMENT,
  `trans_type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `gid` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `branch_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `op` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `barrier_id` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `reason` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'the branch type who insert this record',
  `create_time` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `gid`(`gid`, `branch_id`, `op`, `barrier_id`) USING BTREE,
  INDEX `create_time`(`create_time`) USING BTREE,
  INDEX `update_time`(`update_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;



-- ----------------------------
-- Table structure for product
-- ----------------------------
DROP TABLE IF EXISTS `product`;
CREATE TABLE `product`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `total` int(10) UNSIGNED NULL DEFAULT NULL,
  `sprice` decimal(10, 2) UNSIGNED NOT NULL,
  `free_total` int(11) UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of product
-- ----------------------------
INSERT INTO `product` VALUES (1, '红酒', 178, 10.00, 0);

