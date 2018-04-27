<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/25
 * Time: 11:35
 */
function dbftosql($filepath){
    $db     = new mysql();
    $oci    = new Oracle();
    $oracle = $oci->connect('system', '123456', '192.168.0.185/ncee', 'zhs16gbk');//, 'AL32UTF8'

    /* create a table object and open it */
    $table = new XBaseTable($filepath);
    $table->open();

    $columnName = 'ZKZH,XM,KSH,YXMC,ZYMC,PCMC,KLMC,JHMC,LQSJ,YXDH,PCDM,KLDM,JHXZDM,LQZT,SFZH';
    //进行覆盖  删除之前所有记录
    //$db->delete('dbf', '');
    $data = array();
    while($record = $table->nextRecord()){
        $info = array();
        foreach($table->getColumns() as $i => $c){
            $info[] = $record->getString($c);
        }
        $data[] = $info;
        $key    = explode(',', $columnName);
        $data   = array_combine($key, $info);

        /*********  oracle  insert  *****************************************/
        $oci->insertonee($data, $oracle, "LUQU");
        /**********  oracle  *****************************************/
        $db->insert($data, 'dbf');
    }
    echo "系统已将文件" . $filepath . "数据导入数据库,时间:" . date("Y-m-d H:i:s", time()) . "\n";
    echo "记录条数：" . ($table->getRecordCount() - 1) . "\n";


    /**
     * 写入sqlserver数据库日志表
     */

    $sqlsrv = new Sqlserver();
    $sqlsrv->sqlsrv('117.169.96.166', 'sa', ':DianShi@2017', 'Athena');
    $add_time = time();
    $ip       = $_SERVER['SERVER_ADDR'];
    $sql      = "insert into sys_log ('title','type','remark','add_person','add_time','ip') values('系统','service','导入学生信息','system',{$add_time},{$ip})";
    $a        = $sqlsrv->query($sql);
    var_dump($a);
    /*************************************************s*/

    /* close the table */
    $table->close();
}