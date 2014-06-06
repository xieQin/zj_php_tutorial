<?php

/**
 * 
 */
define('WEB_LANG', 'utf-8');
define("ZJ_PHP_PATH", substr(dirname(__FILE__), 0, -7) . '/');
define("ZJ_PHP_TMP_PATH", ZJ_PHP_PATH . "tmp/");

define("ZJ_PHP_VERSION", "1.016");

$zj_config = array(
    "APP_PATH" => "./",
    "TPL_PATH" => "", // view path
    "APP_URL" => "", // APP的Url路径
    "APP_URL_ACTIONTAG" => "index.php/", //Url中的action的标记, 根据apache配置来设置，比如设为: A/
    "AUTO_LOAD_PATH" => array(),
    //运行平台标记 OWN|SAE|BAE
    "SERVER_PLAT" => "OWN",
    //数据库
    "DB_HOST" => "",
    "DB_USER" => "",
    "DB_PWD" => "",
    "DB_NAME" => "",
    //Memcache（云平台不需要配置）
    "MEMCACHE_SERVER" => "localhost",
    "MEMCACHE_PORT" => "11211",
    //bae上保存MyKV数据的Redis库名称，其他平台不用配置
    "BAE_FOR_MYKV_REDIS_NAME" => "fOPXSzkCqKXRohWswsAf",
    //配置短信
    "SMS_KEY" => "",
    "SMS_SUCRET" => "",
    //配置邮箱
    "SMTP_SERVER" => "smtp.exmail.qq.com", //smtp服务器
    "SMTP_PORT" => 25, //smtp服务端口
    "SMTP_USER" => "test@zsgjs.com", //smtp帐号
    "SMTP_PASS" => "zsgjs1234", //smtp密码
    "SMTP_FROMMAIL" => "test@zsgjs.com", //发送email
    "CORS" => "", //JS跨域资源共享 Cross-Origin Resource Sharing, 即配置 Access-Control-Allow-Origin 属性
);



