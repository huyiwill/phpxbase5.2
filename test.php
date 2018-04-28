<?
/* load the required classes */
require_once "extension/Column.class.php";
require_once "extension/Record.class.php";
require_once "extension/Table.class.php";
require_once "sqlserver/server.php";

require_once "oracle/oracle.php";
require_once "mysql_demo/dbconfig.php";

require_once "ext/readfile.php"; //读取文件
require_once "ext/dbftosql.php";

header("Content-type:text/html;charset=utf-8");
set_time_limit(0);
/*********  oracle  delete  *****************************************/
$oci    = new Oracle();
$oracle = $oci->connect(ORACLE_USER, ORACLE_PWD, ORACLE_HOST, ORACLE_CHARSET);
$oci->delete('LUQU', $oracle);

/*********  oracle  delete  *****************************************/

/**
 * connect mysql
 */
//$db   = new mysql();
//$link = $db->connect2();
//$db->delete('dbf', '');

$dir = ZIP_DIR;
//$files = readfiles($dir);

$zips = scan_dir($dir, array('zip'));
//先解压
foreach($zips as $k => $zip){
    exec("cd dbf && unzip " . $zip . "&& rm -f " . $zip, $res);
}
//再次读取所有dbf
$files = scan_dir($dir, array('dbf'));

if(!is_array($files) || empty($files)){
    echo "dbf文件已经读取完了,请生产";
    return false;
}

foreach($files as $filename){
    $filepath = $dir . "/" . $filename;

    dbftosql($filepath);
}

/* 防止重复读取，进行删除已读取的文件 */
if(ISDELDBF == "1"){
    delfiles($dir, $files);
}

?>