<?php

return array(
    //本地数据库
    "DB_HOST" => "localhost",
    "DB_USER" => "root",
    "DB_PWD" => "",
    "DB_NAME" => "test2",
//    //SAE
//    "DB_HOST" => SAE_MYSQL_HOST_M . ":" .SAE_MYSQL_PORT,
//    "DB_USER" => SAE_MYSQL_USER,
//    "DB_PWD" => SAE_MYSQL_PASS,
//    "DB_NAME" => SAE_MYSQL_DB,
//    //BAE
//    "DB_HOST" => getenv('HTTP_BAE_ENV_ADDR_SQL_IP') . ":" .getenv('HTTP_BAE_ENV_ADDR_SQL_PORT'),
//    "DB_USER" => getenv('HTTP_BAE_ENV_AK'),
//    "DB_PWD" => getenv('HTTP_BAE_ENV_SK'),
//    "DB_NAME" => "RhPGKfvFHtpfaAbLvvSp",
    
    "SMS_KEY" => "51",
    "SMS_SUCRET" => "4385856d676e468b099b375425d788ad575913b5",
    "ca" => array("11", "22"),
    
    "upload_server" => "http://localhost/68_zj_php/trunk/zj_php/add-tools/UploadService/upload_service.php",
    "upload_sign" => "WERGHJMOIJNBGHJLIM1234567890",

    //配置邮箱
    "SMTP_SERVER" => "smtp.exmail.qq.com", //smtp服务器
    "SMTP_PORT" => 25, //smtp服务端口
    "SMTP_USER" => "service@zsgjs.com", //smtp帐号
    "SMTP_PASS" => "8ik,8ik,", //smtp密码
    "SMTP_FROMMAIL" => "service@zsgjs.com", //发送email
);
?>
