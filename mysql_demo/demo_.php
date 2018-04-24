<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/24
 * Time: 15:58
 */
/***
 * 此文件可以单独运行,无需关联其它文件
 */
$a = microtime(true);
date_default_timezone_set('Asia/Shanghai');
set_time_limit(0);
ini_set('memory_limit', '100M');
ini_set('pcre.backtrack_limit', '-1');

class mysql{
    private $dbConfig;
    public  $errno = 0;

    public function __construct($dbConfig){
        $this->dbConfig = $dbConfig;
    }

    public function __destruct(){
        @mysql_close();
    }

    private function connectiondb(){
        $connect = null;
        $connect = @mysql_connect($this->dbConfig['DB_HOST'], $this->dbConfig['DB_USER'], $this->dbConfig['DB_PWD']);
        if($connect != null){
            if(isset($this->dbConfig['DB_CHARSET'])){
                @mysql_query("SET NAMES " . $this->dbConfig['DB_CHARSET'], $connect);
            }
            return $connect;
        }else{
            return false;
        }
    }

    private function selectdb(){
        if($this->connectiondb() != false){
            return @mysql_select_db($this->dbConfig['DB_NAME']);
        }else{
            return false;
        }
    }

    public function execute($sqls){
        $rt          = 0;
        $this->errno = 0;
        if($sqls != null && $this->selectdb() != false){
            @mysql_query('start transaction');
            @mysql_query('SET autocommit=0');
            foreach($sqls as $sql){
                @mysql_query($sql);
            }
            $eno = @mysql_errno();
            if($eno){
                @mysql_query('rollback');
                $this->errno = $eno;
            }else{
                $rt = @mysql_query('commit');
            }
            $this->__destruct();
        }
        return $rt;
    }
}

/*****************************处理dbf文件 开始***********************************/
function sh_hq(&$rv, $rk){
    $rv = preg_replace("/-.---|-/si", "null", $rv);
}

function sz_hq(&$rv, $rk){
}

function sz_xx(&$rv, $rk){
    $rv = trim($rv);
    $t  = strpos($rv, ".");
    if($t === 0){
        $rv = "0" . $rv;
    }
    if($rk == 2){
        $rv = addslashes($rv);
    }
}

function sz_xxn(&$rv, $rk){
    $rv = trim($rv);
    $t  = strpos($rv, ".");
    if($t === 0){
        $rv = "0" . $rv;
    }
    if($rk == 3){
        $rv = addslashes($rv);
    }
}

function myReadFile(&$dbObj, &$cacaheObj, $configs){
    if(file_exists($configs['tmpSjPath'])){
        $lasttime = file_get_contents($configs['tmpSjPath']);
    }else{
        $lasttime = "";
    }

    $sql        = "";
    $rt         = -1;
    $ct         = "";
    $cacheKeySj = "";
    $fields     = array();
    if(!file_exists($configs['dbfname'])){
        exit();//文件不存在
    }
    $fdbf         = fopen($configs['dbfname'], 'r');
    $buf          = fread($fdbf, 32);
    $header       = unpack("VRecordCount/vFirstRecord/vRecordLength", substr($buf, 4, 8));
    $goon         = true;
    $unpackString = '';
    while($goon && !feof($fdbf)){ // read fields:
        $buf = fread($fdbf, 32);
        if(substr($buf, 0, 1) == chr(13)){
            $goon = false;
        }else{         // end of field list
            $field = unpack("a11fieldname/A1fieldtype/Voffset/Cfieldlen/Cfielddec", substr($buf, 0, 18));
            //echo 'Field: ' . json_encode($field) . '<br/>';
            $unpackString .= "A$field[fieldlen]$field[fieldname]/";
            array_push($fields, $field);
        }
    }
    fseek($fdbf, $header['FirstRecord'] + 1); // move back to the start of the
    // first record (after the field definitions)
    for($i = 1; $i <= $header['RecordCount']; $i++){
        $buf    = fread($fdbf, $header['RecordLength']);
        $record = unpack($unpackString, $buf);
        if(!isset($record[$configs['key']])){
            continue;
        }
        if($record[$configs['key']] == '000000'){
            $sfm = trim($record[$configs['time']]);
            if(strlen($sfm) == 5){
                $sfm = "0" . $sfm;
            }
            $tmpLasttime = date("Y-m-d H:i:s", strtotime($record[$configs['date']] . $sfm));
            if($tmpLasttime != $lasttime){
                $lasttime = $tmpLasttime;
                if($configs['cacheKeySj'] == 1){
                    //缓存key的一部分
                    $cacheKeySj = date("YmdHis", strtotime($record[$configs['date']] . $sfm));
                }
                file_put_contents($configs['tmpSjPath'], $lasttime);
                if($configs['is_gl'] == 1 && $tmpLasttime < date("Y-m-d") . ' 09:25:00'){
                    fclose($fdbf);//没有新数据,结束流程
                    exit();
                }
                if($configs['is_gl'] == 1 && $tmpLasttime >= date("Y-m-d") . ' 15:00:00'){
                    $lasttime = date("Y-m-d") . ' 15:00:00';
                }
            }else{
                fclose($fdbf);//没有新数据,结束流程
                exit();
            }
        }else{
            $vt   = "";
            $code = "";
            $name = "";
            $rk   = 0;
            foreach($record as $k => $rv){
                $rv = preg_replace("/ /si", "", $rv);
                $rv = iconv('GBK', 'UTF-8//IGNORE', $rv);
                $configs['tbname']($rv, $rk);//每种类型数据处理不一样
                if($i == 2){
                    if($rk - 1 > 0){
                        $ct .= "`f" . ($rk - 1) . "`,";
                    }
                }
                if($rk == 0){
                    $code = $rv;
                }else{
                    if($rk == 1){
                        $name = $rv;
                    }else{
                        $vt .= "'" . $rv . "',";
                    }
                }
                $rk++;
            }
            $sql .= "(" . $vt . "'" . $lasttime . "','" . $code . "','" . $name . "'),";
            $cacaheObj->set($configs['tbname'] . "_" . $code . $cacheKeySj, $vt . "'" . $lasttime . "','" . $code . "','" . $name, false, 28800);
        }
    }
    fclose($fdbf);
    if($sql != ""){
        $sql  = "INSERT INTO " . $configs['tbname'] . "(" . $ct . "`f0`,`code`,`name`) VALUES " . $sql;
        $sqls = array("DELETE FROM " . $configs['tbname'] . " WHERE f0 = '" . $lasttime . "'", rtrim($sql, ','));
        $rt   = $dbObj->execute($sqls);
    }
    return $rt;
}

/*****************************处理dbf文件 结束***********************************/
$lastPath = dirname(__FILE__);//上一级目录的绝对路径

if(isset($argv[1])){
    $dbfname = $argv[1];
}else{
    //$dbfname = "SJSZS.DBF";
    exit();//参数不存在
}
$cacheConfigs = array(
    'CACHE_MEMCACHE' => array(
        array('192.168.1.101', 11211),
    ),
);
$dbConfigs    = array(
    'DB_TYPE'    => 'mysql',
    'DB_HOST'    => '192.168.1.101',
    'DB_NAME'    => 'db_hq',
    'DB_USER'    => 'cc',
    'DB_PWD'     => '123456',
    'DB_CHARSET' => 'UTF8',
);
$cacaheObj    = new Memcache();
foreach($cacheConfigs['CACHE_MEMCACHE'] as $v){
    $cacaheObj->addServer($v[0], $v[1]);
}
$configs               = array();
$configs['dbfname']    = $dbfname;
$configs['cacheKeySj'] = 0;
$configs['is_gl']      = 0;//是否过滤9:25分以前和15:00:00以后的数据
$configs['market']     = 'SZ';
if(strpos($dbfname, "show2003.") !== false){
    $configs['tbname']     = "sh_hq";
    $configs['key']        = "S1";
    $configs['date']       = "S6";
    $configs['time']       = "S2";
    $configs['cacheKeySj'] = 1;
    $configs['type']       = 1;
    $configs['is_gl']      = 1;
    $configs['market']     = 'SH';
}else{
    if(strpos($dbfname, "SJSHQ.") !== false){
        $configs['tbname']     = "sz_hq";
        $configs['key']        = "HQZQDM";
        $configs['date']       = "HQZQJC";
        $configs['time']       = "HQCJBS";
        $configs['cacheKeySj'] = 1;
        $configs['type']       = 2;
        $configs['is_gl']      = 1;
    }else{
        if(strpos($dbfname, "SJSXX.") !== false){
            $configs['tbname'] = "sz_xx";
            $configs['key']    = "XXZQDM";
            $configs['date']   = "XXZQJC";
            $configs['time']   = "XXBLDW";
        }else{
            if(strpos($dbfname, "SJSXXN.") !== false){
                $configs['tbname'] = "sz_xxn";
                $configs['key']    = "XXZQDM";
                $configs['date']   = "XXZQJC";
                $configs['time']   = "XXBLDW";
            }
        }
    }
}
if(!isset($configs['tbname'])){
    exit();
}else{
    $configs['tmpSjPath'] = $lastPath . "/tmp_" . $configs['tbname'] . "_sj.txt";
}

$dbObj = new mysql($dbConfigs);
$b     = microtime(true);
$rt    = myReadFile($dbObj, $cacaheObj, $configs);
$c     = microtime(true);
echo $configs['tbname'] . (number_format(($b - $a), 10, '.', '')) . "s" . "|" . (number_format(($c - $b), 10, '.', '')) . "s" . "|" . (number_format(($c - $a), 10, '.', '')) . "s" . "--" . $rt . chr(10);