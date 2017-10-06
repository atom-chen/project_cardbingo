<?php

namespace Core;

class HttpCacheControl {

    /**
     * 设置etag Last-Modified Cache-Control
     * @param String $etag
     * @param timestamp  $modifiedTime
     * @param int $maxAge
     */
    function cache($etag, $modifiedTime = false, $maxAge = false, $time = false) {
        if (isset($maxAge) && $maxAge !== false && $maxAge !== null) {
            self::setStatus('expires', array('time' => $time, 'maxAge' => $maxAge));
        }
        if ($modifiedTime !== false || $modifiedTime !== null) {
            $mtime = gmdate("D, d M Y H:i:s", $modifiedTime) . " GMT";
            self::setStatus('mod', array('modifiedTime' => $mtime));
            if (isset($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {
                $ifModifiedSince = $_SERVER["HTTP_IF_MODIFIED_SINCE"];
                $ifModifiedSinceArr = explode(';', $ifModifiedSince);
                if ($ifModifiedSinceArr[0] == $mtime) {
                    self::setStatus(304);
                    exit;
                }
            }
        }
        if ($etag) {
            self::setStatus('etag', array('etag' => $etag));
            if (isset($_SERVER["HTTP_IF_NONE_MATCH"]) && $_SERVER["HTTP_IF_NONE_MATCH"] == $etag) {
                self::setStatus(304);
                exit;
            }
        }
    }

    private function setStatus($type = 304, $params = array()) {
        switch ($type) {
            case 304:
                header('HTTP/1.1 304 Not Modified');
                ;
                break;
            case 'Expires': case 'expires':
                $time = $params['time'] !== false ? $params['time'] : (defined('NOW') ? NOW : time());
                header("Expires: " . gmdate("D, d M Y H:i:s", $time + $params['maxAge']) . " GMT");
                header("Cache-Control: max-age={$params['maxAge']}");
                break;
            case 'Last-Modified': case 'mod':
                header("Last-Modified: " . $params['modifiedTime']);
                break;
            case 'Etag': case 'etag':
                header("Etag: " . $params['etag']);
                break;
            default:
                break;
        }
    }

    /**
     * 设置etag
     * @param string $etag
     */
    function etag($etag) {
        //如果仅仅设置etag，需要通过服务端判断，必须加上过期
        return self::cache($etag, false, 0, 0);
        ;
    }

    /**
     * 设置Last-Modified
     * @param string $modifiedTime
     */
    function lastModified($modifiedTime) {
        //如果仅仅设置lastModified，需要通过服务端判断，必须加上过期
        return self::cache(false, $modifiedTime, 0, 0);
    }

    /**
     * 设置Last-Modified
     * @param string $modifiedTime
     */
    function mod($modifiedTime) {
        return self::cache(false, $modifiedTime, 0, 0);
    }

    /**
     * 设置Cache-Control： max-age
     * @param <type> $maxAge
     * @return <type>
     */
    function maxAge($maxAge, $time = false) {
        return self::cache(false, false, $maxAge, $time);
    }

    /**
     * 设置Cache-Control： max-age
     * @param <type> $maxAge
     * @return <type>
     */
    function expries($maxAge) {
        return self::cache(false, false, $maxAge, $time);
    }

}
