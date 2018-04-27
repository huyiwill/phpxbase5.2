<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/25
 * Time: 10:40
 */
class Oracle{
    private $conn;
    private $query;

    function connect($username, $password, $dbname, $charset = 'UTF8', $pconnect = '1'){
        if($pconnect){
            $this->conn = oci_pconnect($username, $password, $dbname, $charset);
        }else{
            $this->conn = oci_connect($username, $password, $dbname, $charset);
        }

        if(!$this->conn){
            echo "连接Oracle出错";
            exit();
        }else{
            return $this->conn;
        }
    }

    function query($sql){
        $this->query = oci_parse($this->conn, $sql);
        oci_execute($this->query);
        $rs = $this->fetch_array();
        return $rs;
    }

    function insertonee($array, $con, $table){
        $query = "insert into " . $table . "(ZKZH,XM,KSH,YXMC,ZYMC,PCMC,KLMC,JHMC,LQSJ,YXDH,PCDM,KLDM,JHXZDM,LQZT,SFZH) values(:ZKZH,:XM,:KSH,:YXMC,:ZYMC,:PCMC,:KLMC,:JHMC,:LQSJ,:YXDH,:PCDM,:KLDM,:JHXZDM,:LQZT,:SFZH)";
        $stid = @oci_parse($con, $query);  //编译
         foreach($array as $k => &$v){
             if($k == 'LQSJ'){
                 oci_bind_by_name($stid,  ":{$k}", date('Y-m-d H:i:s',strtotime($v)));
             }else{
                 oci_bind_by_name($stid,  ":{$k}", $v);
             }
         }
        @oci_execute($stid); //执行
        @oci_free_statement($stid);
        //@oci_close($con); //关闭连接
        return 1;
    }

    function delete($table, $con){
        $query = "delete from {$table}";
        $stid  = oci_parse($con, $query);
        oci_execute($stid, OCI_COMMIT_ON_SUCCESS); //执行
        //检查影响的行数
        oci_free_statement($stid);
        oci_close($con); //关闭连接
        return true;
    }

    function insertone(){
        $con   = oci_connect("user", "password", "ip/sid", "AL32UTF8");  //连接oracle
        $query = "insert into t values (:uname,:email)";  //sql
        $stid  = oci_parse($con, $query);  //编译
        $uname = $_POST['name']; //传递值
        $email = $_POST['email']; //传递值
        oci_bind_by_name($stid, ':uname', $uname);
        oci_bind_by_name($stid, ':email', $email);
        oci_execute($stid); //执行
        oci_free_statement($stid);
        oci_close($con); //关闭连接
        /*
         $stid = oci_parse($con, $query);  //编译
         foreach($data as $k => $v){
             if($k == 'LQSJ'){
                 oci_bind_by_name($stid,  ":$k", date('Y-m-d H:i:s',strtotime($v)));
             }else{
                 oci_bind_by_name($stid,  ":$k", $v);
             }
         }*/
    }

    function fetch_array($type = OCI_ASSOC){
        while($row = oci_fetch_array($this->query, $type)){
            $rs[] = $row;
        }
        if(1 == count($rs)){
            $rs = $rs[0];
        }
        return $rs;
    }

    function db_count(){
        oci_fetch($this->query);
        $count = oci_result($this->query, 1);
        return $count;
    }

    function close(){
        $re = oci_close($this->conn);
        return $re;
    }

    function result(){
        oci_fetch($this->query);
        return oci_result($this->query, 1);
    }

}

//$oci = new Oracle();
//$oci->connect('DBName', 'DBPassword', 'IPAddr/ServiceName');
