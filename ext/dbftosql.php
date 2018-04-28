<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/25
 * Time: 11:35
 */
function dbftosql($filepath){
    //$db     = new mysql();
    $oci    = new Oracle();
    $oracle = $oci->connect(ORACLE_USER, ORACLE_PWD, ORACLE_HOST, ORACLE_CHARSET);//, 'AL32UTF8'

    /* create a table object and open it */
    $table = new XBaseTable($filepath);
    $table->open();

    $columnName = 'ZKZH,XM,KSH,YXMC,ZYMC,PCMC,KLMC,JHMC,LQSJ,YXDH,PCDM,KLDM,JHXZDM,LQZT,SFZH';
    //进行覆盖  删除之前所有记录
    //$db->delete('dbf', '');
    $data = array();
    $success=0;
    while($record = $table->nextRecord()){
        $info = array();
        foreach($table->getColumns() as $i => $c){
            $info[] = $record->getString($c);
        }
        $data[] = $info;
        $key    = explode(',', $columnName);
        $data   = array_combine($key, $info);

        /*********  oracle  insert  *****************************************/
         if($oci->insertonee($data, $oracle, "LUQU")>0)
         {
             $success=$success+1;
         }

        /**********  oracle  *****************************************/
        //$db->insert($data, 'dbf');
    }
    echo "系统已将文件" . $filepath . "数据导入数据库,时间:" . date("Y-m-d H:i:s", time()) . "\n";
    echo "记录条数：" . ($table->getRecordCount() - 1) . "\n";
    echo "成功条数：" . ($success) . "\n";

    /**
     * 写入sqlserver数据库日志表
     */
    try{
        $sqlsrv = new Sqlserver();
        $sqlsrv->sqlsrv(SQSERVER_HOST, SQSERVER_USER, SQSERVER_PWD, SQSERVER_DBNAME);
        $add_time = time();
        $ip       = $_SERVER['SERVER_ADDR'];
        $sql      = "insert into sys_log ('title','type','remark','add_person','add_time','ip') values('高校录取信息','service','此次执行录取人数'.{$success},'system',{$add_time},{$ip})";
        $sqlsrv->query($sql);
        /*************************************************s*/

        /* close the table */
        $table->close();
    }
    catch(Exception $e){
        //print_r($e);
    }
}