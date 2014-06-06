<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class MyModel {

    private $_table;
    private $_where;
    private $_order;
    private $_limit;
    private $_params;
    private $_fields;
    
    public $debugSql = false;
    
    private function debug_sql($sql, $param)
    {
        if ($this->debugSql) {
            my_log("[sql:]");
            my_log($sql);
            my_log("[params:]");
            my_log($param);
        }
    }

    function __construct($table) {
        $this->_table = $table;
    }

    function where($where, $param = null) {
        $this->_where = $where;
        $this->_order = null;
        $this->_limit = null;
        $this->_fields = null;

        $this->_params = $param;
        return $this;
    }

    function order($order) {
        $this->_order = $order;
        return $this;
    }

    function limit($limit) {
        $this->_limit = $limit;
        return $this;
    }

    private function createSql(&$sql) {
        $sql = " SELECT ";
        if (is_null($this->_fields)) {
            $sql = $sql . " * ";
        } else {
            $sql = $sql . " $this->_fields ";
        }

        $sql = $sql . " from $this->_table ";
        $sql = $sql . " where $this->_where ";

        if (!is_null($this->_order)) {
            $sql = $sql . " order by $this->_order ";
        }

        if (!is_null($this->_limit)) {
            $sql = $sql . " limit $this->_limit ";
        }
    }

    function select($fields = null) {
        $this->_fields = $fields;
        $sql = "";
        $this->createSql($sql);
        
        $this->debug_sql($sql, $this->_params);
        
        $db = D();
        return $db->query_array($sql, $this->_params);
    }

    function find($fields = null) {
        $this->_fields = $fields;
        $sql = "";
        $this->createSql($sql);
        
        $this->debug_sql($sql, $this->_params);
        
        $db = D();
        return $db->query_one($sql, $this->_params);
    }

    function add(&$data) {
        $fields = "";
        $values = "";
        $valueData = array();
        $fg = "";
        foreach ($data as $key => $value) {
            $fields .= $fg . "`$key`";
            $values .= $fg . "?";

            $valueData[] = $value;
            $fg = ',';
        }

        $sql = " insert into {$this->_table} ({$fields}) values ({$values}) ";
        
        $this->debug_sql($sql, $valueData);
        
        $db = D();
        $rt = $db->query($sql, $valueData);
        if (isset($rt["id"])) {
            return $rt["id"];
        }

        return false;
    }
    
    function update(&$data)
    {
        //UPDATE persondata SET ageage=age*2, ageage=age+1; 
        
        $setFields = "";
        $valueData = array();
        $fg = "";
        foreach ($data as $key => $value) {
            $setFields .= $fg . "`$key`=?";
            $valueData[] = $value;
            $fg = ',';
        }
        
        if (is_array($this->_params)) {
            $valueData = array_merge($valueData, $this->_params);
        }
        
        $sql = " update {$this->_table} set {$setFields} where {$this->_where} ";
        
        $this->debug_sql($sql, $valueData);
        
        $db = D();
        $rt = $db->query($sql, $valueData);
        if (isset($rt["af"])) {
            return ($rt["af"] > 0) ? $rt["af"] : true;
        }

        return false;
    }
    
    function delete()
    {
        $sql = " delete from $this->_table where $this->_where ";
        
        $this->debug_sql($sql, $this->_params);
         
        $db = D();
        $rt = $db->query($sql, $this->_params);
        if (isset($rt["af"])) {
            return ($rt["af"] > 0) ? $rt["af"] : true;
        }

        return false;
    }
}

