/*
 Navicat Premium Data Transfer

 Source Server         : dockr_mysql5.7
 Source Server Type    : MySQL
 Source Server Version : 50736 (5.7.36)
 Source Host           : 192.168.0.106:3310
 Source Schema         : dtm

 Target Server Type    : MySQL
 Target Server Version : 50736 (5.7.36)
 File Encoding         : 65001

 Date: 11/09/2023 14:52:00
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for kv
-- ----------------------------
DROP TABLE IF EXISTS `kv`;
CREATE TABLE `kv`  (
  `id` bigint(22) NOT NULL AUTO_INCREMENT,
  `cat` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'the category of this data',
  `k` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `v` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `version` bigint(22) NULL DEFAULT 1 COMMENT 'version of the value',
  `create_time` datetime NULL DEFAULT NULL,
  `update_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uniq_k`(`cat`, `k`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of kv
-- ----------------------------

-- ----------------------------
-- Table structure for trans_branch_op
-- ----------------------------
DROP TABLE IF EXISTS `trans_branch_op`;
CREATE TABLE `trans_branch_op`  (
  `id` bigint(22) NOT NULL AUTO_INCREMENT,
  `gid` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'global transaction id',
  `url` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'the url of this op',
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'request body, depreceated',
  `bin_data` blob NULL COMMENT 'request body',
  `branch_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'transaction branch ID',
  `op` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'transaction operation type like: action | compensate | try | confirm | cancel',
  `status` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'transaction op status: prepared | succeed | failed',
  `finish_time` datetime NULL DEFAULT NULL,
  `rollback_time` datetime NULL DEFAULT NULL,
  `create_time` datetime NULL DEFAULT NULL,
  `update_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `gid_uniq`(`gid`, `branch_id`, `op`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for trans_global
-- ----------------------------
DROP TABLE IF EXISTS `trans_global`;
CREATE TABLE `trans_global`  (
  `id` bigint(22) NOT NULL AUTO_INCREMENT,
  `gid` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'global transaction id',
  `trans_type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'transaction type: saga | xa | tcc | msg',
  `status` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'transaction status: prepared | submitted | aborting | succeed | failed',
  `query_prepared` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'url to check for msg|workflow',
  `protocol` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'protocol: http | grpc | json-rpc',
  `create_time` datetime NULL DEFAULT NULL,
  `update_time` datetime NULL DEFAULT NULL,
  `finish_time` datetime NULL DEFAULT NULL,
  `rollback_time` datetime NULL DEFAULT NULL,
  `options` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'options for transaction like: TimeoutToFail, RequestTimeout',
  `custom_data` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'custom data for transaction',
  `next_cron_interval` int(11) NULL DEFAULT NULL COMMENT 'next cron interval. for use of cron job',
  `next_cron_time` datetime NULL DEFAULT NULL COMMENT 'next time to process this trans. for use of cron job',
  `owner` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'who is locking this trans',
  `ext_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'extra data for this trans. currently used in workflow pattern',
  `result` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'result for transaction',
  `rollback_reason` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'rollback reason for transaction',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `gid`(`gid`) USING BTREE,
  INDEX `owner`(`owner`) USING BTREE,
  INDEX `status_next_cron_time`(`status`, `next_cron_time`) USING BTREE COMMENT 'cron job will use this index to query trans'
) ENGINE = InnoDB AUTO_INCREMENT = 69 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;


