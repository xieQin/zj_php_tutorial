<?php

/**
 * 分布式任务队列服务，用来以异步HTTP方式执行用户任务
 * 
 * 说明： 
 * 1. BAE 还没测通；
 * 2. My 要用Memceche功能
 * 
 * @author joy
 */
abstract class MyTaskQueue {

    /**
     * 生成 TaskQueue 设置实例
     * @param type $queueName 指定队列名称, SAE和BAE平台需要指定，本地环境暂时未使用。
     * @return 
     */
    static function getQueue($queueName) {
        $plat = getC("SERVER_PLAT");
        if ($plat == "SAE") {
            return new SAE_TaskQueue($queueName);
        } else if ($plat == "BAE") {
            return new BAE_TaskQueue($queueName);
        } else {
            return new My_TaskQuery();
        }
    }

    /**
     * 添加任务列表
     */
    function addTask($array) {
        
    }

    /**
     * 将任务推入队列
     */
    function push() {
        
    }

    /**
     * 检测执行任务,SAE和BAE平台会自动执行，本地环境需要调用该方法执行任务 
     */
    static function checkAndDo() {
        $plat = getC("SERVER_PLAT");
        if ($plat == "OWN") {
            return My_TaskQuery::checkAndDo();
        }
    }

}

class My_TaskQuery extends MyTaskQueue {

    private $arrTask;
    private $mk = "ZJ_PHP_MYTASKQUEUE_MEMCHECK_KEY_IJEJJFYEIR";

    function addTask($array) {
        $this->arrTask = $array;
    }

    function push() {
        if (!$this->arrTask)
            return false;

        $filename = null;
        foreach ($this->arrTask as $task) {
            $filename = microtime(ture) * 10000 . rand(1000, 9999);
            FileHandler::updateFile($task, ZJ_PHP_TMP_PATH . "taskqueue/", $filename);
        }

        return true;
    }

    /**
     * 检测执行任务
     */
    static function checkAndDo() {
        $t1 = time();
        while (true) {
            if (time() - $t1 > 10) {
                return;
            }

            $files = FileHandler::ls(ZJ_PHP_TMP_PATH . "taskqueue", 1);
            if (count($files) <= 0) {
                return;
            }

            $file = $files[0];
            $url = file_get_contents($file);
            if (is_file($file)) {
                FileHandler::deleteFile($file);
            } else {
                continue;
            }

            $s = new Snoopy;
            $s->fetch($url);
        }
    }

}

class SAE_TaskQueue extends MyTaskQueue {

    private $saeQueue = null;

    function __construct($queueName) {
        $this->saeQueue = new SaeTaskQueue($queueName);
    }

    function addTask($array) {
        foreach ($array as $u)
        {
            $this->saeQueue->addTask($u);
        }
    }

    function push() {
        return $this->saeQueue->push();
    }

}

class BAE_TaskQueue extends MyTaskQueue {

    private $queueName;
    private $arrTask;

    function __construct($queueName) {
        $this->queueName = $queueName;

        require_once 'BaeTaskQueueManager.class.php';
        require_once 'BaeTaskQueue.class.php';
    }

    function addTask($array) {
        $this->arrTask = $array;
    }

    function push() {

        $tqMgr = BaeTaskQueueManager::getInstance();
        if (NULL == $tqMgr) {
            return false;
        }

        $qName = 'testQueue';
        $tq = $tqMgr->create($qName, BaeTaskQueueManager::QUEUE_FETCHURL);
        if (is_null($tq)) {
            return false;
        }

        foreach ($this->arrTask as $url) {
            $task = array(
                BaeTaskQueue::FETCHURL_URL => $url,
            );
            $tq->push($task);
        }
        $tqMgr->remove($qName);

        return false;
    }

}
