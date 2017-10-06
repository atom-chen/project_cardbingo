<?php

namespace App\PO;

/**
 CREATE TABLE `account` (
  `user_id` bigint(22) unsigned NOT NULL COMMENT '用户ID',
  `user_name` varchar(22) NOT NULL COMMENT '用户账号名',
  `password` varchar(32) NOT NULL COMMENT '密码',
  `isbind` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '账号当前的绑定状态 0未绑定 1绑定',
  `server_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '用户所属的服务器组ID',
  `register_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `UNIQUE_user_name` (`user_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

 */
class Account extends \Core\PO {

    protected $_name = 'account';
    protected $_priKey = 'user_id';
    protected $_fields = ['user_id','user_name','password','isbind','server_id','register_time','game_version','channel_id'];

}
