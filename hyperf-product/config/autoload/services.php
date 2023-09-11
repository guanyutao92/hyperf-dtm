<?php
return [
    //    'cunsumers'=>[
//        [
//            'name'=>'CalculatorService',
//            'registry'=>[
//                'protocol'=>'nacos',
//                'address'=>'http://127.0.0.1:8848'
//            ]
//        ]
//    ],
//   注册服务
    'drivers'=>[
        'nacos'=>[
            'host'=>'192.168.1.107',
            'port'=>8848,
            'username'=>'nacos',
            'password'=>'nacos',
            'guzzle'=>[
                'config'=>null
            ],
            'group_name'=>'SEATA_GROUP',
            'namespace_id'=>'',
            'heartbeat'=>5,
            // 是否为临时实例
            //'ephemeral' => false,
        ]
    ]
];