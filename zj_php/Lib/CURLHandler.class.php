<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CURLHandler
 *
 * @author joy
 */
class CURLHandler {

    private static $instance = null;

    public static function share() {
        if (self::$instance == null) {
            self::$instance = new self ();
        }
        return self::$instance;
    }

    public function query($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        
        return $output;
    }
}
