<?php

/**
 * 应用Action的基类
 *
 * @author Joy
 */
abstract class Action {

    /**
     * 执行请求 Action 的 Method 方法之前调用方法。
     */
    function __before() {
        //my_log("__before");
    }

    /**
     * 执行请求 Action 的 Method 方法之后调用方法。
     */
    function __after() {
        //my_log("__after");
    }
}