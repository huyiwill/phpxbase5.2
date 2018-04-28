<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/24
 * Time: 17:21
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PWD', 'aa');
define('DB_CHARSET', 'GBK');
define('DB_DBNAME', 'test');

/* oracle数据库配置参数 */
//'system', '123456', '192.168.0.185/ncee', 'zhs16gbk'
define('ORACLE_HOST', '192.168.0.185/ncee');
define('ORACLE_USER', 'system');
define('ORACLE_PWD', '123456');
define('ORACLE_CHARSET', 'zhs16gbk');

/*  sqlserver  数据库配置参数  */
//'117.169.96.166', 'sa', 'DianShi@2017', 'Athena'
define('SQSERVER_HOST', '117.169.96.166');
define('SQSERVER_USER', 'sa');
define('SQSERVER_PWD', 'DianShi@2017');
//define('SQSERVER_CHARSET', 'GBK');
define('SQSERVER_DBNAME', 'Athena');

/*  压缩文件目录位置  */ // D:\A_project\export\phpxbase
//define('ZIP_DIR', '/d/A_project/export/phpxbase5.2');
define('ZIP_DIR', 'd:/A_project/export/phpxbase5.2/dbf');

/* 是否删除dbf文件 */
define('ISDELDBF', '1');