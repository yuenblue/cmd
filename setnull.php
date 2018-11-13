<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/13
 * Time: 20:10
 */

set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');
$time_now = time();
$dbh = new PDO('mysql:host=localhost;dbname=mmm', "root", "root");
$sql="set names utf8";
$re=$dbh->exec($sql);

$sql = "show tables;";
$sth = $dbh->query($sql);
$re = $sth->fetchAll();
foreach ($re as $item){
    $sql = "desc {$item[0]};";
    $sth = $dbh->query($sql);
    $re_colum = $sth->fetchAll();
    foreach ($re_colum as $attr){
        if(empty($attr["Key"])&&$attr["Null"]=="NO"){
            $sql = "ALTER TABLE `mmm`.`{$item[0]}` CHANGE COLUMN `{$attr['Field']}` `{$attr['Field']}` {$attr['Type']} NULL DEFAULT NULL ;";
            $re = $dbh->exec($sql);
            var_dump($re);
        }
    }

}
