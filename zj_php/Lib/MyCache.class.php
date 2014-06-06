<?php

/**
 * Memcache 操作
 * 
 * 说明： BAE 还没测通
 *
 * @author joy
 */
abstract class MyCache {

    private static $_instance;

    static function share() {
        if (!(self::$_instance instanceof self)) {
            $plat = getC("SERVER_PLAT");
            if ($plat == "SAE") {
                self::$_instance = new SAE_Memcache();
            } else if ($plat == "BAE") {
                self::$_instance = new BAE_Memcache();
            } else {
                self::$_instance = new My_Memcache();
            }
        }

        return self::$_instance;
    }

    /**
     * 设置缓存（推荐使用），$value地址应用，要求是变量；
     * @param string $key
     * @param mixed $value 缓存值，应用类型，所以要用变量形式传递
     * @param long $expire 过期时间，0为永不过期，可使用 unix 时间戳格式或距离当前时间的秒数，设为秒数时不能大于 2592000（30 天）
     */
    abstract function set($key, &$value, $expire = 0);

    /**
     * 设置缓存，$value可以不是变量
     * @param type $key
     * @param type $value
     * @param type $expire
     */
    function set2($key, $value, $expire = 0) {
        $this->set($key, $value, $expire);
    }

    abstract function get($key);

    abstract function delete($key);

    function getAll() {
        
    }

}

class My_Memcache extends MyCache {

    private $_mem;

    private function getMem() {
        if ($this->_mem == null) {
            $this->_mem = memcache_connect(getC("MEMCACHE_SERVER"), getC("MEMCACHE_PORT"));
        }
        return $this->_mem;
    }

    function __destruct() {
        if ($this->_mem != null) {
            $this->_mem->close();
            $this->_mem = null;
        }
    }

    function set($key, &$value, $expire = 0) {
        $mem = $this->getMem();
        $mem->set($key, $value, MEMCACHE_COMPRESSED, $expire);
    }

    function get($key) {
        $mem = $this->getMem();
        return $mem->get($key);
    }

    function delete($key) {
        $mem = $this->getMem();
        $mem->delete($key, 0);
    }

    function getAll() {
        $mem = $this->getMem();
        $result = $mem->getExtendedStats('slabs');
        return $result;
    }

}

class BAE_Memcache extends MyCache {

    private $_mem;

    private function getMem() {
        if ($this->_mem == null) {
            require_once ('BaeMemcache.class.php');
            $this->_mem = new BaeMemcache();
        }
        return $this->_mem;
    }

    function set($key, &$value, $expire = 0) {
        //print_r("$key");
        $m = $this->getMem();
        $m->set($key, $value, MEMCACHE_COMPRESSED, $expire);
    }

    function get($key) {
        $m = $this->getMem();
        return $m->get($key);
    }

    function delete($key) {
        $m = $this->getMem();
        $m->delete($key);
    }

}

class SAE_Memcache extends MyCache {

    private $_mem;

    private function getMem() {
        if ($this->_mem == null) {
            $this->_mem = memcache_init();
        }
        return $this->_mem;
    }

    function set($key, &$value, $expire = 0) {
        $m = $this->getMem();
        $m->set($key, $value, MEMCACHE_COMPRESSED, $expire);
    }

    function get($key) {
        $m = $this->getMem();
        return $m->get($key);
    }

    function delete($key) {
        $m = $this->getMem();
        $m->delete($key);
    }
}