<?
/* load the required classes */
require_once "Column.class.php";
require_once "Record.class.php";
require_once "Table.class.php";
require_once "mysql_demo/mysql.php";
require_once "mysql_demo/dbconfig.php";

/**
 * connect mysql
 */
$db   = new mysql();
$link = $db->connect2();
//$sql='SELECT * FROM user';
//$rows = $db->fetchAll($sql);
//var_dump($rows);

/* create a table object and open it */
$table = new XBaseTable("luqu_yuluqu20170823_090229.dbf");
$table->open();

echo "<pre>";

$columnName = 'ZKZH,XM,KSH,YXMC,ZYMC,PCMC,KLMC,JHMC,LQSJ,YXDH,PCDM,KLDM,JHXZDM,LQZT,SFZH';

//进行覆盖  删除之前所有记录
$db->delete('dbf', '');

while($record = $table->nextRecord()){
    $info = array();
    foreach($table->getColumns() as $i => $c){
        $info[] = $record->getString($c);
    }
    $key = explode(',', $columnName);
    //$value = implode(',',$info);
    $data = array_combine($key, $info);
    $db->insert($data, 'dbf');
}

echo "已经将dbf数据导入sql数据库中了";

/* close the table */
$table->close();
?>