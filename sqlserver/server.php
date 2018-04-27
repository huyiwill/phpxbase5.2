<?php

class Sqlserver{

    var $error_log = array();
    var $sql_log   = array();
    var $query_id;
    var $num_rows;
    var $conn;

    //connection
    function sqlsrv($server, $user, $pass, $dbname){
        $this->conn = sqlsrv_connect($server, array('UID' => $user, 'PWD' => $pass, 'Database' => $dbname));
        if($this->conn === false){
            $this->error_log[] = sqlsrv_errors();
           // print_r($this->error_log);
            die(33);
        }
    }

    //query source
    function query($sql){
        $stmt            = sqlsrv_query($this->conn, $sql);
        $this->sql_log[] = $sql;
        if($stmt === false){
            $this->error_log[] = sqlsrv_errors();
        }else{
            $this->query_id = $stmt;
            $this->num_rows = $this->affectedRows();
        }
        return true;
    }

    //fetch data
    function fetch_all($sql){
        $this->query($sql);
        $data = array();
        while($row = @sqlsrv_fetch_array($this->query_id, SQLSRV_FETCH_ASSOC)){
            $data[] = $row;
        }
        return $data;
    }

    // $DB->count(select   *   from  users)
    function fetch_one($sql){

        $this->query($sql);
        return sqlsrv_fetch_array($this->query_id, SQLSRV_FETCH_ASSOC);
    }

    // $DB->count(select   count(*)   from  users)
    function count($sql){

        $count = $this->fetch_one($sql);
        return $count[""];
    }

    function affectedRows(){
        return ($this->query_id) ? @sqlsrv_num_rows($this->query_id) : false;
    }
}

?>