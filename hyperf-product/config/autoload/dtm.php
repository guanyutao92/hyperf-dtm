<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-client/blob/master/LICENSE
 */
use DtmClient\Constants\DbType;
use DtmClient\Constants\Protocol;

return [
    // 客户端与 DTM Server 通讯的协议，支持 Protocol::HTTP 和 Protocol::GRPC 两种
    'protocol' => Protocol::HTTP,
    // DTM Server 的地址
    'server' => '192.168.0.106',
    // DTM Server 的端口
    'port' => [
        'http' => 36789,
        'grpc' => 36790,
    ],
    // 子事务屏障配置
    'barrier' => [
        // DB 模式下的子事务屏障配置
        'db' => [
            'type' => DbType::MySQL,
        ],
        // 非 Hyperf 框架下应用子事务屏障的类
        'apply' => [],
    ],
    // Configuration is required only if you use the XA transaction mode
    'database' => [
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'hyperf'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        'fetch_mode' => PDO::FETCH_ASSOC,
        'options' => [
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_AUTOCOMMIT => 0
        ],
    ],
    // HTTP 协议下 Guzzle 客户端的通用配置
    'guzzle' => [
        'options' => [],
    ],
];
