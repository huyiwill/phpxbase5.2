<?
/* load the required classes */
require_once "Column.class.php";
require_once "Record.class.php";
require_once "Table.class.php";
require_once "mysql_demo/mysql.php";
require_once "sqlserver/server.php";

require_once "oracle/oracle.php";
require_once "mysql_demo/dbconfig.php";

require_once "ext/readfile.php"; //读取文件
require_once "ext/dbftosql.php";

header("Content-type:text/html;charset=utf-8");

/*********  oracle  delete  *****************************************/
$oci    = new Oracle();
$oracle = $oci->connect('system', '123456', '192.168.0.185/ncee', 'zhs16gbk');
$oci->delete('LUQU', $oracle);

/*********  oracle  delete  *****************************************/

/**
 * connect mysql
 */
$db   = new mysql();
$link = $db->connect2();
$db->delete('dbf', '');

$dir   = 'dbf';
$files = readfiles($dir);

if(!is_array($files) || empty($files)){
    echo "dbf文件已经读取完了,请生产";
}

foreach($files as $filename){
    $filepath = $dir . "/" . $filename;
    dbftosql($filepath);
}

/* 防止重复读取，进行删除已读取的文件 */
delfiles($dir, $files);

?>