<?php

namespace Core;

class Sign {

    public static function generate($params = []) {
        return md5(self::_buildBaseString($params) . config()['secret']);
    }

    public static function check($sign, $params = []) {
        return $sign == $params['accessToken'];
    }

    private static function _buildBaseString($params) {
        uksort($params, 'strcmp');
        $pairs = [];
        foreach ($params as $k => $v) {
            $pairs[] = $k . '=' . $v;
        }
        return implode('&', $pairs);
    }

}
