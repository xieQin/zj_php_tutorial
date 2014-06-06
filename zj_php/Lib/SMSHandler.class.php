<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SMS
 *
 * @author joy
 */
class SMSHandler {

    private static $instance = null;

    public static function share() {
        if (self::$instance == null) {
            self::$instance = new self ();
        }
        return self::$instance;
    }

    /**
     * 多条短信用 ',' 分隔 
     * @param type $phones
     * @param type $content
     */
    public function send($phones, $content) {

         if (!ereg("^.*$",$phones)){
             return false;
         }
		 
		 if(strlen($phones) < 3){
			return false;
		 }
         
         $key = getC("SMS_KEY");
         $sucretKey = getC("SMS_SUCRET");
         
         $url = "http://sms.bechtech.cn/Api/send/data/json?accesskey={$key}&secretkey={$sucretKey}&mobile={$phones}&content=".urlencode($content);
         //my_log($url);
         if (CURLHandler::share()->query($url) === FALSE) {
             return false;
         }
         
         return true;
    }
}

