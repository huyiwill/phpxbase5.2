<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * 读取指定目录的指定文件
 *
 * @param dir  指定的目录
 */
function readfiles($dir){
    /* 读取目录下面所有的dbf文件 */
    $files   = array();
    $handler = opendir($dir);
    while(($filename = readdir($handler)) !== false){
        if($filename != "." && $filename != ".."){
            //输出文件名
            $files[] = $filename;
        }
    }
    closedir($handler);
    return $files;
}

/**
 * 读取文件内容后进行删除文件
 *
 * @param dir
 */
function delfiles($dir, $files){
    foreach($files as $file){
        $filepath = $dir . "/" . $file;
        unlink($filepath);
    }
}

function delfilesw($filepath){
    //echo $filepath;die;
    unlink($filepath);
}