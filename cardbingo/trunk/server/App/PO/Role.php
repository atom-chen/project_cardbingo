<?php

namespace App\PO;

/**
CREATE TABLE `role` (
  `role_id` bigint(22) unsigned NOT NULL COMMENT '角色ID',
  `nickname` char(30) NOT NULL COMMENT '角色昵称',
  `coin` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '角色金币',
  `diamond` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '角色钻石',
  `power` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '角色体力',
  `login_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '登陆时间戳',
  `archive_version` bigint(22) unsigned NOT NULL DEFAULT '0' COMMENT '存档版本号',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
 */
class Role extends \Core\PO {

    protected $_name = 'role';
    protected $_priKey = 'role_id';
    protected $_fields = ['role_id','nickname', 'coin', 'diamond', 'power','login_time', 'register_time','archive_version','game_version','channel_id'];

}
