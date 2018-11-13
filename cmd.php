<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/2
 * Time: 11:22
 */
set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');
$time_now = time();
set_error_handler('cmderror');
function cmderror($type, $message, $file, $line)
{
    throw new \Exception('cmderror' . "#" . $message);
}
include_once "common.php";

try {
//    $dbh = new PDO('mysql:host=localhost;dbname=qrcode', "root", "root");
    $dbh = new PDO('mysql:host=localhost;dbname=sdgbwl', "sdgbwl", "a4d6E2z6");
    $sql="set names utf8";
    $re=$dbh->exec($sql);
    logNativeMsg("设置字符编码".$re);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
/////////////////////////////////////////////////////


$weightGrantDay=1;//每月的第几天发放加权分红


try {
    logNativeMsg("settleBonus...");
    settleBonus();
    logNativeMsg("settleBonus finish!");
    logNativeMsg("grantBonus...");
    grantBonus();
    logNativeMsg("grantBonus finish!");
} catch (Exception $e) {
    logNativeMsg($e->getMessage());
}


try {
    logNativeMsg("settleWeight...");
    settleWeight();
    logNativeMsg("settleWeight finish!");
    logNativeMsg("grantWeight...");
    grantWeight();
    logNativeMsg("grantWeight finish!");
} catch (Exception $e) {
    logNativeMsg($e->getMessage());
}


//logNativeMsg("aaaaaa");
//logNativeMsg("哈哈哈啊哈哈");

////////////////////////////////////////////////////
//////////////////////结算分期分红//////////////////////

function settleBonus()
{
    global $dbh, $time_now;
    //判断是否符合条件
    //判断时间是否符合条件
    //等待结算状态的记录
    $sql = sprintf("select id,status,grant_time from rc_shopper_bonus where status=%d", ShoperBonusModel::WAIT_GRANT_TIME);
    $sth = $dbh->query($sql);
    if ($sth === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception("获取结算分期分红计划失败！" . $erro_info[2]);
    }
    $re = $sth->fetchAll(PDO::FETCH_ASSOC);
    if ($re === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception("读取结算分期分红计划失败！" . $erro_info[2]);
    }
    $arrResult=array();
    foreach ($re as $item){
        if($item["grant_time"]<=$time_now){
            //保存待变更结算计划
            $arrResult[]=$item;
        }
    }
    $sth=$dbh->prepare("update rc_shopper_bonus set status=:status where id=:id");
    //结算
    foreach ($arrResult as $item){
        $re = $sth->execute(array(":status"=>ShoperBonusModel::WAIT_GRANT,":id"=>$item["id"]));
        if($re===false){
            $erro_info = $dbh->errorInfo();
            throw new Exception("变更结算分期分红计划失败！#{$item["id"]}#" . $erro_info[2]);
        }
    }

}

//////////////////////发放分期分红/////////////////////////
function grantBonus(){
    global $dbh, $time_now;
    try {
        $sql = sprintf("select id,uid,level_name,bonus from rc_shopper_bonus where status=%d", ShoperBonusModel::WAIT_GRANT);
        $sth = $dbh->query($sql);
        if ($sth === false) {
            $erro_info = $dbh->errorInfo();
            throw new Exception("初始化待发放结算分期分红计划失败！" . $erro_info[2]);
        }
        $re = $sth->fetchAll(PDO::FETCH_ASSOC);
        if ($re === false) {
            $erro_info = $dbh->errorInfo();
            throw new Exception("获取待发放结算分期分红计划失败！" . $erro_info[2]);
        }

        foreach ($re as $item) {
            //发放
            $sth = $dbh->prepare("UPDATE  rc_user set bonus=bonus+:money where id=:uid");
            if ($sth === false) {
                $erro_info = $dbh->errorInfo();
                throw new Exception("准备发放分期分红失败！#{$item['aid']}#" . $erro_info[2]);
            }
            $re = $sth->execute(array(":money" => $item['bonus'], ":uid" => $item["uid"]));
            if ($re === false) {
                $erro_info = $dbh->errorInfo();
                throw new Exception("发放分期分红失败！#{$item['aid']}#" . $erro_info[2]);
            }
            //变更发放状态
            $sth = $dbh->prepare("UPDATE  rc_shopper_bonus set status=:status where id=:id");
            if ($sth === false) {
                $erro_info = $dbh->errorInfo();
                throw new Exception("准备变更发放分期分红发放状态失败！#{$item['aid']}#" . $erro_info[2]);
            }
            $re = $sth->execute(array(":status" => ShoperBonusModel::HAS_GRANT, ":id" => $item["id"]));
            if ($re === false) {
                $erro_info = $dbh->errorInfo();
                throw new Exception("变更发放状态发放分期分红失败！#{$item['aid']}#" . $erro_info[2]);
            }
            //发放日志
            logUserMsg($item["uid"], "恭喜获得用户晋级分期分红", "用户消费级别：{$item['level_name']},获取分期分红{$item['bonus']}", MsgStatus::WAIT_FOR_READ, $time_now, MsgLevel::IMPORTANT);
            logUserMoney($item["uid"], $item['bonus'], MoneyEvent::GRANT_BONUS, $time_now, MoneyType::BONUS, "晋级分期分红");
            //发放消息
        }
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}


//getIncome();
//getWeightNumber();
//settleWeight();
//grantWeight();
//////////////////////结算加权分红////////////////////////

function settleWeight()
{
    global $dbh, $time_now;
    $arrTime = getWeightDateTime();
    $time_start = $arrTime["start"];
    $time_end = $arrTime["end"];
//获取已结算用户
    $sql = "select weight_id from rc_shopper_bonus_weight where add_time>={$time_start} and add_time<= {$time_end}";
    $sth = $dbh->query($sql);
    if ($sth === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception("准备获取已结算加权分红用户失败！" . $erro_info[2]);
    }
    $arrPlan = $sth->fetchAll(PDO::FETCH_COLUMN, 0);
    if ($arrPlan === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception("获取已结算加权分红用户失败！" . $erro_info[2]);
    }
//$arrPlan_ids=array();
//foreach ($arrPlan as $item){
//    $arrPlan_ids[]=$item["weight_id"];
//}
//获取所有加权用户
    $sql = "select id,uid from rc_shopper_weight";
    $sth = $dbh->query($sql);
    if ($sth === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception("准备获取所有加权分红用户失败！" . $erro_info[2]);
    }
    $arrUser = $sth->fetchAll(PDO::FETCH_ASSOC);
    if ($arrUser === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception("获取所有加权分红用户失败！" . $erro_info[2]);
    }
//寻找未结算用户
    $arrResult = array();
    foreach ($arrUser as $item) {
        if (!in_array($item["id"], $arrPlan)) {
            $arrResult[] = $item;
        }
    }
//var_dump($arrResult);
//结算
    foreach ($arrResult as $item) {
        $sql = sprintf("insert into rc_shopper_bonus_weight(uid,weight_id,grant_time,status,add_time)VALUES (%d,%d,%d,%d,%d)",
            $item["uid"], $item["id"], 0, ShoperBonusModel::WAIT_GRANT, $time_now
        );
        $re = $dbh->exec($sql);
    }
}

/////////////////////发放加权分红////////////////////////////

function grantWeight()
{
    global $dbh, $time_now,$weightGrantDay;
    $today = date("j");
    if($today!=$weightGrantDay){
        return;
    }
    //判断是否到发放时间
    try {

        //

        $arrTime = getWeightDateTime();
        $time_start = $arrTime["start"];
        $time_end = $arrTime["end"];
        //遍历表rc_shopper_bonus_weight
       // $sql = "select a.id as aid,a.status  from rc_shopper_bonus_weight a LEFT JOIN rc_shopper_weight b on a.weight_id=b.id where add_time>={$time_start} and add_time<= {$time_end}";
        $sql=sprintf("select a.id as aid,a.uid,a.status,b.level_code,b.level_name  from rc_shopper_bonus_weight a LEFT JOIN rc_shopper_weight b on a.weight_id=b.id where a.status=%d and a.add_time>=%d and a.add_time<= %d",ShoperBonusModel::WAIT_GRANT,$time_start,$time_end);
        $sth = $dbh->query($sql);
        if ($sth === false) {
            $erro_info = $dbh->errorInfo();
            throw new Exception($erro_info[2]);
        }
        $arrPlan = $sth->fetchAll(PDO::FETCH_ASSOC);
        //统计本月毛收入
        $incomeConfig = getIncome();
        //计算加权
        $weightConfig = getWeightConfig();
        //获取各星级人数
        $weightUsers = getWeightNumber();
        //加工数据
        $userMoneyConfig = getMoneyGrant($incomeConfig, $weightConfig, $weightUsers);
        //计算各级别各星级的分多少
        foreach ($arrPlan as $item) {
            //增加用户分红
            //        $money= $incomeConfig[$item["level_code"]]*$weightConfig[$item["level_name"]]/$weightUsers[$item["level_code"]];
            $money = $userMoneyConfig[$item["level_code"]][$item["level_name"]];
            if($money>0){
                $sth = $dbh->prepare("UPDATE  rc_user set bonus=bonus+:money where id=:uid");
                if ($sth === false) {
                    $erro_info = $dbh->errorInfo();
                    throw new Exception("准备发放加权分红失败！#{$item['aid']}#" . $erro_info[2]);
                }
                $re = $sth->execute(array(":money" => $money, ":uid" => $item["uid"]));
                if ($re === false) {
                    $erro_info = $dbh->errorInfo();
                    throw new Exception("发放加权分红失败！#{$item['aid']}#" . $erro_info[2]);
                }
            }

            //添加发放日志
            logUserMsg($item["uid"], "恭喜获得用户每月分红", "用户消费级别：{$item['level_name']},获取每月收益分红{$money}", MsgStatus::WAIT_FOR_READ, $time_now, MsgLevel::IMPORTANT);
            logUserMoney($item["uid"], $money, MoneyEvent::GRANT_BONUS, $time_now, MoneyType::BONUS, "每月加权分红");
            //标志记录为已发放
            $sql = "update rc_shopper_bonus_weight set status= :status where id=:id";
            $sth = $dbh->prepare($sql);
            if ($sth === false) {
                $erro_info = $dbh->errorInfo();
                throw new Exception("准备发放加权分红失败！#{$item['aid']}#" . $erro_info[2]);
            }
            $re = $sth->execute(array(":status" => ShoperBonusModel::HAS_GRANT, ":id" => $item["aid"]));
            if ($re === false) {
                $erro_info = $dbh->errorInfo();
                throw new Exception("标志发放加权分红记录为已发放失败！#{$item['aid']}#" . $erro_info[2]);
            }
        }
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function logUserMsg($uid, $tile, $msg, $status, $addtime, $msg_level)
{
    global $dbh, $time_now;
    $sql = "insert into rc_user_msg(uid,title,msg,status,addtime,msg_level)VALUES (:uid,:title,:msg,:status,:addtime,:msg_level)";
    $sth = $dbh->prepare($sql);
    if ($sth === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception("发送用户消息失败！#{$uid}#" . $erro_info[2]);
    }
    $re = $sth->execute(array(":uid" => $uid, ":title" => $tile, ":msg" => $msg, ":status" => $status, ":addtime" => $addtime, ":msg_level" => $msg_level));
    if ($re === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception($erro_info[2]);
    }
}

function logUserMoney($uid, $money, $event, $addtime, $money_type, $remark)
{
    global $dbh, $time_now;
    $sql = "insert into rc_money_log(uid,money,event,addtime,money_type,remark)VALUES (:uid,:money,:event,:addtime,:money_type,:remark)";
    $sth = $dbh->prepare($sql);
    if ($sth === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception("发送用户消息失败！#{$uid}#" . $erro_info[2]);
    }
    $re = $sth->execute(array(":uid" => $uid, ":money" => $money, ":event" => $event, ":addtime" => $addtime, ":money_type" => $money_type, ":remark" => $remark));
    if ($re === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception($erro_info[2]);
    }
}

//获取各星级各消费级别待发放分红
function getMoneyGrant($incomeConfig, $weightConfig, $weightUsers)
{
    $arr = array();
    for ($i = 0; $i < 6; ++$i) {
        $arr[$i] = array();
        foreach ($weightConfig as $k => $v) {
            $income = 0;
            if (isset($incomeConfig[$i])) {
                $income = $incomeConfig[$i][0];
            }
            $users = 0;
            if (isset($weightUsers[$i])) {
                $users = $weightUsers[$i][0];
            }
            if ($income == 0 || $users == 0) {
                $arr[$i][$k] = 0;
                continue;
            }
            $arr[$i][$k] = $income * $v / $users;
        }
    }
    return $arr;
}

function getIncome()
{

    global $dbh, $time_now;

    $arrTime = getWeightDateTime();
    $time_start = $arrTime["start"];
    $time_end = $arrTime["end"];
    $sql = "select level_code,sum(money) as income from rc_service_income_log where addtime>={$time_start} and addtime<= {$time_end} GROUP by level_code";
    $sth = $dbh->query($sql);
    if ($sth === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception($erro_info[2]);
    }
    $re = $sth->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
    if ($re === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception($erro_info[2]);
    }
    return $re;
}

function getWeightNumber()
{
    global $dbh, $time_now;

    //获取各星级人数
    $sql = "select level_code,count(id) num from rc_shopper_weight GROUP BY level_code";
    $sth = $dbh->query($sql);
    if ($sth === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception($erro_info[2]);
    }
    $re = $sth->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
    if ($re === false) {
        $erro_info = $dbh->errorInfo();
        throw new Exception($erro_info[2]);
    }
    return $re;
}
