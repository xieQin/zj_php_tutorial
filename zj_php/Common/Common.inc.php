<?php

/**
 * （需要时）自动加载类文件
 * @param type $class
 * @return type
 */
function __autoload($class) {
    $arr = getC("AUTO_LOAD_PATH");
    $classFullPath = null;
    foreach ($arr as $path) {
        $file1 = $path . "/" . "{$class}.class.php";
        if (file_exists($file1)) {
            $classFullPath = $file1;
            break;
        }

        $file2 = $path . "/" . "class.{$class}.php";
        if (file_exists($file2)) {
            $classFullPath = $file2;
            break;
        }
    }

    require_once "$classFullPath";
}

/*
 * 使用中用 $db = D() 的方式获取数据库连接
 */
$_dbsss = null;

function D() {
    global $_dbsss;
    if (!($_dbsss instanceof MyDb)) {
        $_dbsss = new MyDb();
    }

    return $_dbsss;
}

function UD() {
    global $_dbsss;
    if ($_dbsss instanceof MyDb) {
        $_dbsss->close();
        $_dbsss = null;
    }
}

function M($table) {
    return new MyModel($table);
}

function MC() {
    return MyCache::share();
}

/**
 * 过滤 数组 中的不安全字符和函数
 * @param type $array
 * @return type
 */
function Add_S($array) {
    foreach ($array as $key => $value) {
        if (!is_array($value)) {
            $value = str_replace("&#x", "& # x", $value); //过滤一些不安全字符
            $value = preg_replace("/eval/i", "eva l", $value); //过滤不安全函数
            !get_magic_quotes_gpc() && $value = addslashes($value);
            $array[$key] = $value;
        } else {
            $array[$key] = Add_S($array[$key]);
        }
    }
    return $array;
}

/**
 * 设置 $zj_config
 * @global array $zj_config
 * @param type $key
 * @param type $value
 */
function setC($key, $value) {
    global $zj_config;
    $zj_config[$key] = $value;
}

/**
 * 取 $zj_config 中指定key的值
 * @global array $zj_config
 * @param type $key
 */
function getC($key) {
    global $zj_config;
    return $zj_config[$key];
}

/**
 * 添加自动加载类文件路径
 * @global array $zj_config
 * @param type $path
 * @return type
 */
function add_autoload_path($path, $withSubDir = false) {
    global $zj_config;
    $paths[] = $path;
    if($withSubDir){
        $paths = array_merge($paths, sub_dir_ls($path));
    }

    foreach ($paths as $tmpP) {
        $tmpP = str_replace('\\', '/', $tmpP);
        $tmpP = trim($tmpP, "/\\");
        if (!in_array($tmpP, $zj_config["AUTO_LOAD_PATH"])) {
            $zj_config["AUTO_LOAD_PATH"][] = $tmpP;
        }
    }
}

/**
 * 添加配置文件
 * @global type $zj_config
 * @param type $configFile
 * @return type
 */
function add_app_config($app_config_file) {
    global $zj_config;

    if (!is_file($app_config_file)) {
        return;
    }

    $name = include $app_config_file;
    if (is_array($name)) {
        $zj_config = array_merge($zj_config, $name);
    }

    //my_log($zj_config);
}

function DES($osTag, $debug = false) {
    if ($debug == true) {
        return DES::share("debug");
    }
    return DES::share($osTag);
}

/** View ************************************************* */

/**
 * 加载一个 view 或 View模块
 * @param type $vPath view相对应用配置参数 "TPL_PATH" 的路径
 * @param type $vName view名称 
 * @param type $vData view处理数据
 */
function renderView($vPath, $vName, &$vData = null) {
    Runtime::load($vPath, $vName, $vData);
}

function renderString($strData) {
    echo $strData;
}

function renderJson($jsonData) {
    echo json_encode($jsonData);
}

function U($url_path) {
    return Runtime::url($url_path);
}

function UA($url_path) {
    return Runtime::url_action($url_path);
}

function PDU($desparam, $os, $deskey) {
    return Runtime::parse_des_urlparam($desparam, $os, $deskey);
}