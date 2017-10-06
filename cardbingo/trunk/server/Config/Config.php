<?php

return [
    //是否开启调试模式
    'debug'         => true,
    //时区
    'timezone'      => 'PRC',
    //屏蔽日志， 针对文件名
    'forbid_log'    => [
        "testlogfilename" => true,
    ],
    //命名空间相对路径配置
    'namespaces'    => [
        'Core' => DIR_CORE,
        'App' => DIR_APP,
        'Tplt' => DIR_TPLT,
        //'Zend' => DIR_VENDOR . 'Zend/',
        //'predis' => DIR_VENDOR . 'PRedis/',
    ],
    //密码salt
    'password_salt' => '$@Ds34',
    'secret' => 'cardbingo$ifolin@126.com',
];