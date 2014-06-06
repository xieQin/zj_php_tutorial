<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

error_reporting(7);
//set_magic_quotes_runtime(0);

require_once 'Common/Config.inc.php';
require_once 'Common/Common.inc.php';
require_once 'Common/Function.inc.php';
require_once 'Common/Runtime.inc.php';

//添加自动加载Class路径
add_autoload_path(ZJ_PHP_PATH."Lib/", true);
add_autoload_path(ZJ_PHP_PATH."Extend/", true);

$_POST = Add_S($_POST);
$_GET = Add_S($_GET);
$_COOKIE = Add_S($_COOKIE);

// 可以用 $q_xx 直接访问 $_REQUEST["aa"];
foreach ($_REQUEST as $key => $value) {
    @eval("\$q_{$key}='{$value}';");
}

//请求的Action和Method，在Runtime的Query对其赋值。
$_QueryAction = "";
$_QueryMethod = "";

ob_start();
//JS跨域
if(getC("CORS")) {
    @header('Access-Control-Allow-Origin: ' . getC("CORS"));
}
@header('Content-Type: text/html; charset=' . WEB_LANG);

//xxx/index.php?doTaskQueue
if(isset($q_doTaskQueue))
{
    MyTaskQueue::checkAndDo();
    exit();
}