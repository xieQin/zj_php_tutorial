<?php

/**
 * DES 加解密
 * 
 * 注意： 会把加密后字符串中的 “/” 替换成 “@@”，“+” 替换成 “$$”。
 * 
 * @author joy
 */
abstract class DES {

    public static function all_osTag() {
        return array("android", "ios", "wp", "debug");
    }

    public static function share($osTag) {
        if ("ios" == $osTag) {
            return new IOSDES();
        }

        if ("android" == $osTag) {
            return new AndroidDES();
        }

        if ("wp" == $osTag) {
            return new WPDES();
        }

        if ("debug" == $osTag) {
            return new DebugDES();
        }
    }

    public function encode($content, $key) {
        return "";
    }

    public function decode($content, $key) {
        return "";
    }

}

class DebugDES extends DES {

    public function encode($content, $key) {
        return $content;
    }

    public function decode($content, $key) {
        return $content;
    }

}

class AndroidDES extends DES {

    var $key;
    var $iv; //偏移量

    public function encode($content, $key) {
        $this->key = $key;
        $this->iv = $key;

        $size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
        $str = $this->pkcs5Pad($content, $size);
        $data = mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv);
        $ret = base64_encode($data);
        $ret = str_replace("/", "@@", $ret);
        $ret = str_replace("+", "$$", $ret);
        return $ret;
    }

    public function decode($content, $key) {
        $this->key = $key;
        $this->iv = $key;

        $content = str_replace("@@", "/", $content);
        $content = str_replace("$$", "+", $content);
        $content = base64_decode($content);
        $content = mcrypt_cbc(MCRYPT_DES, $this->key, $content, MCRYPT_DECRYPT, $this->iv);
        $content = $this->pkcs5Unpad($content);
        return $content;
    }

    function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    function pkcs5Unpad($text) {
        $pad = ord($text {strlen($text) - 1});
        if ($pad > strlen($text))
            return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
            return false;
        return substr($text, 0, - 1 * $pad);
    }

}

class IOSDES extends DES {

    var $key;

    public function encode($content, $key) {
        $this->key = $key;
        $ivArray = array(0x12, 0x34, 0x56, 0x78, 0x90, 0xAB, 0xCD, 0xEF);
        $iv = null;
        foreach ($ivArray as $element)
            $iv.=CHR($element);


        $size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
        $content = $this->pkcs5Pad($content, $size);

        $data = mcrypt_encrypt(MCRYPT_DES, $this->key, $content, MCRYPT_MODE_CBC, $iv);

        $data = base64_encode($data);
        $data = str_replace("/", "@@", $data);
        $data = str_replace("+", "$$", $data);

        return $data;
    }

    public function decode($content, $key) {
        $this->key = $key;
        $content = str_replace("@@", "/", $content);
        $content = str_replace("$$", "+", $content);

        $ivArray = array(0x12, 0x34, 0x56, 0x78, 0x90, 0xAB, 0xCD, 0xEF);
        $iv = null;
        foreach ($ivArray as $element)
            $iv.=CHR($element);

        $content = base64_decode($content);

        $result = mcrypt_decrypt(MCRYPT_DES, $this->key, $content, MCRYPT_MODE_CBC, $iv);
        $result = $this->pkcs5Unpad($result);

        return urldecode($result);
    }

    function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    function pkcs5Unpad($text) {
        $pad = ord($text {strlen($text) - 1});
        if ($pad > strlen($text))
            return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
            return false;
        return substr($text, 0, - 1 * $pad);
    }

}

class WPDES extends DES {

    private $text;
    private $key;
    private $salt;
    private $iv = 'AAAAAAAAAAAAAAAA';

    private function pbkdf2($p, $s, $c, $dk_len, $algo = 'sha1') {

        // experimentally determine h_len for the algorithm in question
        static $lengths;
        if (!isset($lengths[$algo])) {
            $lengths[$algo] = strlen(hash($algo, null, true));
        }
        $h_len = $lengths[$algo];

        if ($dk_len > (pow(2, 32) - 1) * $h_len) {
            return false; // derived key is too long
        } else {
            $l = ceil($dk_len / $h_len); // number of derived key blocks to compute
            $t = null;
            for ($i = 1; $i <= $l; $i++) {
                $f = $u = hash_hmac($algo, $s . pack('N', $i), $p, true); // first iterate
                for ($j = 1; $j < $c; $j++) {
                    $f ^= ($u = hash_hmac($algo, $u, $p, true)); // xor each iterate
                }
                $t .= $f; // concatenate blocks of the derived key
            }
            return substr($t, 0, $dk_len); // return the derived key of correct length
        }
    }

    private function stripPadding($source){
         $block = 16;
   		 $char = substr($source, -1, 1);
         $num = ord($char);
         //echo "num:";echo $num;echo "<br/>";
	     if($num > 16){
	         return $source;
	     }
		//echo "beforestripPadding:";echo strlen($source);echo "<br/>";
	     $len = strlen($source);
	     for($i = $len - 1; $i >= $len - $num; $i--){
	         if(ord(substr($source, $i, 1)) != $num){
	             return $source;
	         }
	     }
	     $source = substr($source, 0, -$num);
	     //echo "stripPadding:";echo strlen($source);echo "<br/>";
	     
	     
	     return $source;
     }

    private function addpadding($string) {
        $blocksize = 16;
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);

        //echo "addpadding:".$string."<br/>";
        return $string;
    }

    public function encode($content, $key) {
        #    $data = $this->paddingAlgorithm->padData($data, $blockSize);
        #    return $iv . mcrypt_encrypt($this->MCRYPT_DES, $keyBytes, $data, MCRYPT_MODE_CBC, $iv);


        $this->text = $content;
        $this->salt = $key;
        $this->key = $this->pbkdf2($key, $this->salt, 1000, 32);

        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key,
                //pay attention to encode and decode in php
                //$this->addpadding(iconv('GB2312', 'UTF-8', $this->text)), 
                $this->addpadding($this->text), MCRYPT_MODE_CBC, $this->iv);
        $data = base64_encode($crypttext);

        $data = str_replace("/", "@@", $data);
        $data = str_replace("+", "$$", $data);

        return $data;
    }

    public function decode($content, $key) {
        #    $data = $this->paddingAlgorithm->padData($data, $blockSize);
        #    return $iv . mcrypt_encrypt($this->MCRYPT_DES, $keyBytes, $data, MCRYPT_MODE_CBC, $iv);
        $content = str_replace("@@", "/", $content);
        $content = str_replace("$$", "+", $content);

        $this->text = $content;
        $this->salt = $key;
        $this->key = $this->pbkdf2($key, $this->salt, 1000, 32);

        //mcrypt_decrypt($cipher, $key, $data, $mode)
        $crypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key,
                //pay attention to encode and decode in php
                //$this->addpadding(iconv('GB2312', 'UTF-8', $this->text)), 
                base64_decode($this->text), MCRYPT_MODE_CBC, $this->iv);
        $crypttext = $this->stripPadding($crypttext);
        return $crypttext;
    }

}
