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

function scan_dir($dir, $filter = array()){
    if(!is_dir($dir)){
        return false;
    }
    $files = array_diff(scandir($dir), array('.', '..'));
    if(is_array($files)){
        foreach($files as $key => $value){
            if(is_dir($dir . '/' . $value)){
                $files[$value] = scan_dir($dir . '/' . $value, $filter);
                unset($files[$key]);
                continue;
            }
            $pathinfo = pathinfo($dir . '/' . $value);

            $extension = array_key_exists('extension', $pathinfo) ? $pathinfo['extension'] : '';
            if(!empty($filter) && !in_array($extension, $filter)){
                unset($files[$key]);
            }
        }
    }
    unset($key, $value);
    return $files;
}

function searchZip($dir, $filter = array()){
    if(!is_dir($dir)){
        return false;
    }
    $files = array_diff(scandir($dir), array('.', '..'));
    if(is_array($files)){
        foreach($files as $key => $value){
            if(is_dir($dir . '/' . $value)){
                $files[$value] = scan_dir($dir . '/' . $value, $filter);
                unset($files[$key]);
                continue;
            }
            ///**********  将压缩包文件解压  *************/
            if(preg_match('/(.*)(\.)zip$/i', $value)){
                $pathinfo = pathinfo($dir . '/' . $value);

                $extension = array_key_exists('extension', $pathinfo) ? $pathinfo['extension'] : '';
                if(!empty($filter) && !in_array($extension, $filter)){
                    unset($files[$key]);
                }
            }else{
                unset($files[$key]);
            }
        }
    }
    unset($key, $value);
    return $files;
}

// 被解压文件的名称
function unZip($fileName, $dir){
    $zip      = zip_open($fileName);
    $dirnames = dirname($dir);

    if($zip){
        //if(is_resource($zip)){
            while($zip_entry = zip_read($zip)){
                if(zip_entry_open($zip, $zip_entry, "r")){
                    $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));    // 读取zip文件, 并制定读取的长度

                    $fname = $dirnames . '/' . zip_entry_name($zip_entry);
                    if(is_dir($fname))                                           // 如果是目录则创建目录(目录是未创建的)
                    {
                        mk_dir($dirnames . '/' . zip_entry_name($zip_entry));
                    }else                                                         // 是文件
                    {
                        mk_dir(dirname($fname));
                        file_put_contents($fname, $buf);                          // 将读取到的内容直接写入文件
                    }
                    zip_entry_close($zip_entry);
                }
            }
            zip_close($zip);

            //if(@unlink($fileName)){
            //    echo "已删除压缩源文件: $fileName<br>";
            //}
       // }
    }
}

// 循环创建目录
function mk_dir($dir, $mode = 0777){
    if(is_dir($dir) || @mkdir($dir, $mode)){
        return true;
    }
    if(!mk_dir(dirname($dir), $mode)){
        return false;
    }
    return @mkdir($dir, $mode);
}

/**
 * 读取文件内容后进行删除文件
 *
 * @param dir
 */
function delfiles($dir, $files){
    foreach($files as $file){
        $filepath = $dir . "/" . $file;
        @unlink($filepath);
    }
}

function delfilesw($filepath){
    //echo $filepath;die;
    unlink($filepath);
}

function unzipp($zip){
    if(is_resource($zip)){
        while($zip_entry = zip_read($zip)){
            $fp = fopen("/var/www/vhosts/website/httpdocs/zip/" . zip_entry_name($zip_entry), "w");
            if(zip_entry_open($zip, $zip_entry, "r")){
                $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                fwrite($fp, "$buf");
                zip_entry_close($zip_entry);
                fclose($fp);
            }
        }
        zip_close($zip);
    }
}