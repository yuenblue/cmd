<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/4
 * Time: 17:44
 */
date_default_timezone_set('Asia/Shanghai');

//echo date("j");


$aa = array(1,2,3);
$aa["aa"]="aaa";
$aa["AA"]="AAA";
var_dump($aa);
//$bb=;















function logNativeMsg($msg,$time=0){
    //创建目录
    $dir = "logs";
    if(!is_dir($dir)){
        mkdir("logs");
    }

    //使用日期创建文件
    $dirName = "logs".DIRECTORY_SEPARATOR.date("Y-m");
    if(!is_dir($dirName)){
        mkdir($dirName);
    }

    //写入日志
    if(empty($time)){
        $time=time();
    }
    $time_str = date("Y-m-d H:i:s",$time);
    $filename = $dirName.DIRECTORY_SEPARATOR.date("Y-m-d").".log";
    file_put_contents($filename,$time_str."#".$msg."\r\n",FILE_APPEND);
}

//logNativeMsg("哈哈哈哈");
//logNativeMsg("哈哈哈哈");


