<?php

/**
 * Description of Runtime
 *
 * @author Joy
 */
class Runtime {

    static function doQuery() {
        global $_QueryAction, $_QueryMethod;
        $pathInfo = $_SERVER["PATH_INFO"];
        $arrInfo = explode("/", $pathInfo);
        $act = isset($arrInfo[1]) ? $arrInfo[1] : "Index";
        $_QueryAction = $act;
        $actClass = ucfirst($act . "Action");
        $method = isset($arrInfo[2]) ? $arrInfo[2] : "index";
        $_QueryMethod = $method;
        
        if (class_exists($actClass)) {
            $o = new $actClass();
            if (($o instanceof Action) && method_exists($o, $method)) {
                $o->__before();
                $o->$method();
                $o->__after();
                return;
            }
        }

        die("404 ({$act}/{$method} 不存在)");
    }

    static function load($vPath, $vName, &$_viewData = null) {
        include getC("TPL_PATH") . $vPath . "/" . $vName . ".html.php";
    }

    static function url($url_path) {
        return "http://".$_SERVER["SERVER_NAME"].getC("APP_URL") . $url_path;
    }

    static function url_action($url_path) {
        return "http://".$_SERVER["SERVER_NAME"].getC("APP_URL") . getC("APP_URL_ACTIONTAG") . $url_path;
    }

    /**
     * 解析用DES加密的字符串， 返回 key => value 数组。
     * @param type $desparam
     * @param type $key
     * @return type
     */
    static function parse_des_urlparam($desparam, $os, $key) {
        $des = DES::share($os);
        $params = $des->decode($desparam, $key);
        $arrParam = array();
        $arrTmp = explode("&", $params);
        foreach ($arrTmp as $p) {
            $item = explode("=", $p);
            if (count($item) == 2) {
                $arrParam[$item[0]] = $item[1];
            }
        }
        $arrParam = Add_S($arrParam);
        return $arrParam;
    }

}

