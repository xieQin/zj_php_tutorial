<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MyDb
 *
 * @author joy
 */
class MyDb {

    //put your code here

    private $conn;

    private function getConn() {

        if ($this->conn == null) {

            $this->conn = new mysqli(getC("DB_HOST"), getC("DB_USER"), getC("DB_PWD"), getC("DB_NAME"));
        }
        if ($this->conn->connect_errno) {
            die("db connect_errno: {$this->conn->connect_errno}");
        }

        return $this->conn;
    }

    private function fetch($result, $rtRowNum = 0) {
        $array = array();

        if ($result instanceof mysqli_stmt) {
            $result->store_result();

            $variables = array();
            $data = array();
            $meta = $result->result_metadata();

            while ($field = $meta->fetch_field())
                $variables[] = &$data[$field->name];

            call_user_func_array(array($result, 'bind_result'), $variables);

            $i = 0;
            while ($result->fetch()) {
                $array[$i] = array();
                foreach ($data as $k => $v)
                    $array[$i][$k] = $v;
                $i++;

                if ($rtRowNum > 0 && $i >= $rtRowNum) {
                    break;
                }
            }
        } elseif ($result instanceof mysqli_result) {
            $i = 0;
            while ($row = $result->fetch_assoc()) {
                $array[] = $row;
                $i++;
                if ($rtRowNum > 0 && $i >= $rtRowNum) {
                    break;
                }
            }
        }

        return $array;
    }

    /**
     * 统一的操作数据库方法
     * 如果是查询，返回SELECT查询结果；
     * 如果是修该表，返回操作影响记录条数（和插入记录的主键值（需是自增））
     * 
     * @param string $sql Sql语句，为防止Sql注入，参数建议用 ？绑定值的方式传递；
     * @param array $paraArr Sql语句的绑定参数，顺序要和 $sql语句对应；
     * @param int $rtRowNum 查询返回的记录条数，0 返回所有记录
     * @return mixed
     */
    function query($sql, $paraArr = null, $rtRowNum = 0) {

        $conn = $this->getConn();
        $stmt = $conn->prepare($sql);

        if (is_array($paraArr) && count($paraArr) > 0) {
            $types = "";
            $variables = array();
            $variables[] = &$types;
            for ($n = 0; $n < count($paraArr); $n++) {
                $types .= "s";
                $variables[] = &$paraArr[$n];
            }
            //my_log($variables);
            call_user_func_array(array($stmt, 'bind_param'), $variables);
        }

        $stmt->execute();

        //返回SELECT查询结果
        if ($stmt->field_count > 0) {
            $rt = $this->fetch($stmt, $rtRowNum);
            $stmt->close();
            return $rt;
        }

        //Array ( [af] => 1 [id] => 14 ) 
        //返回修改库操作影响记录条数 和 插入记录的主键值（需是自增）
        $rt = array('af' => $stmt->affected_rows, 'id' => $stmt->insert_id);
        $stmt->close();
        return $rt;
    }

    function query_array($sql, $paraArr = null) {
        return $this->query($sql, $paraArr);
    }

    function query_one($sql, $paraArr = null) {
        $arr = $this->query($sql, $paraArr, 1);
        if (is_array($arr) && count($arr) > 0) {
            return $arr[0];
        }
    }

    function query_int($sql, $paraArr = null) {
        $row = $this->query_one($sql, $paraArr);
        if (is_array($row)) {
            foreach ($row as $int) {
                return $int - 0;
            }
        }

        return 0;
    }

    function close() {
        $conn = $this->getConn();
        $conn->close();
        $this->conn = null;
        //$this = null;
    }

    function __destruct() {
        $this->close();
    }

}

