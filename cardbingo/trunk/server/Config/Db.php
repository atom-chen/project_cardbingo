<?php

return [
    /**
     * 数据库拆分配置
     * shard_{id}  id不可以为0
     * 新用户注册进来的权重比例，关闭则设置成0
     */
    'sharding_config' => [
        1 => 1, ////shard_1 
        2 => 0, //shard_2
        3 => 0, //shard_3
    ],
    'default' => [ //全局数据库，
    //
    //
    //用于维护全局数据（账号表， 配置表，相关统计数据表等） 
        'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=cardbingo;charset=utf8',
        'user' => 'root',
        'password' => 'onekes',
    ],
    'shard_1' => [ //拆分数据库，用于用户实际游戏数据存储， 根据账户中sid决定用户属于哪个分区数据库
        'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=teddy1;charset=utf8',
        'user' => 'root',
        'password' => 'onekes',
    ],
    'shard_2' => [
        'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=teddy2;charset=utf8',
        'user' => 'root',
        'password' => 'onekes',
    ],
];
