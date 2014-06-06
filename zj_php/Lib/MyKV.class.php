<?php

/**
 * Key => Value 存储技术。
 * 本地环境用 File 方式实现；SAE环境用SaeKV实现；BAE环境用Redis实现；
 * BAE 环境需要开启应用的Redis功能，还需要设置配置参数 BAE_FOR_MYKV_REDIS_NAME
 * 
 * @author joy
 */
abstract class MyKV {

    public static function getKV() {
        $plat = getC("SERVER_PLAT");
        if ($plat == "SAE") {
            return new SAE_KV();
        } else if ($plat == "BAE") {
            return new BAE_KV();
        } else {
            return new My_KV();
        }
    }

    abstract function set($key, $value);

    abstract function get($key);

    abstract function delete($key);

    abstract function getAll();
}

class My_KV extends MyKV {

    function set($key, $value) {
        FileHandler::updateFile(array_to_string($value), ZJ_PHP_TMP_PATH . "kv_data/", $key);
        return true;
    }

    function get($key) {
        if (is_file(ZJ_PHP_TMP_PATH . "kv_data/" . $key)) {
            $value = FileHandler::readFileContent(ZJ_PHP_TMP_PATH . "kv_data/" . $key);
            return string_to_array($value);
        }
        return null;
    }

    function delete($key) {
        if (is_file(ZJ_PHP_TMP_PATH . "kv_data/" . $key)) {
            FileHandler::deleteFile(ZJ_PHP_TMP_PATH . "kv_data/" . $key);
        }
        return true;
    }

    function getAll() {
        $allKey = FileHandler::ls(ZJ_PHP_TMP_PATH . "kv_data/");
        $rt = array();
        foreach ($allKey as $value) {
            $tmpv = explode("/", $value);
            end($tmpv);
            $key = current($tmpv);
            $rt[$key] = $this->get($key);
        }

        return $rt;
    }

}

class SAE_KV extends MyKV {

    private $kv;

    function __construct() {
        $this->kv = new SaeKV();
        if (!$this->kv->init()) {
            $this->kv = null;
        }
    }

    function set($key, $value) {
        if ($this->kv) {
            return $this->kv->set($key, $value);
        }

        return false;
    }

    function get($key) {
        if ($this->kv) {
            return $this->kv->get($key);
        }

        return false;
    }

    function delete($key) {
        if ($this->kv) {
            return $this->kv->delete($key);
        }

        return false;
    }

    function getAll() {
        $ret = array();
        $get = $this->kv->pkrget('', 100);
        while (true) {
            $ret += $get;
            end($get);
            $start_key = key($get);
            $i = count($get);
            if ($i < 100)
                break;
            $get = $this->kv->pkrget('', 100, $start_key);
        }

        return $ret;
    }

}

class BAE_KV extends MyKV {

    private $redis;

    function __construct() {
        $dbname = getC("BAE_FOR_MYKV_REDIS_NAME");
        $host = getenv('HTTP_BAE_ENV_ADDR_REDIS_IP');
        $port = getenv('HTTP_BAE_ENV_ADDR_REDIS_PORT');
        $user = getenv('HTTP_BAE_ENV_AK');
        $pwd = getenv('HTTP_BAE_ENV_SK');

        try {
            $redis = new Redis();
            $ret = $redis->connect($host, $port);
            if ($ret === false) {
                die($redis->getLastError());
            }

            $ret = $redis->auth($user . "-" . $pwd . "-" . $dbname);
            if ($ret === false) {
                die($redis->getLastError());
            }

            $this->redis = $redis;
        } catch (RedisException $e) {
            die("Uncaught exception " . $e->getMessage());
        }
    }

    function set($key, $value) {
        if ($this->redis) {
            return $this->redis->set($key, array_to_string($value));
        }

        return false;
    }

    function get($key) {
        if ($this->redis) {
            $value = $this->redis->get($key);
            return string_to_array($value);
        }

        return false;
    }

    function delete($key) {
        if ($this->redis) {
            return $this->redis->del($key);
        }

        return false;
    }

    function getAll() {
        die("Bae plat cannot use MyKY/getAll() function! ");
    }

}

