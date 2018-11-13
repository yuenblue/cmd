<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/2
 * Time: 14:22
 */
class ShoperBonusModel
{
    const WAIT_GRANT = 0;
    const WAIT_GRANT_TIME = 1;
    const HAS_GRANT = 2;
    const EXPIRE = 3;
    const ABNORMAL = 4;//待发放变为异常

}

function getWeightConfig()
{
    return array(100000 => 0.01, 500000 => 0.01, 1000000 => 0.01);
}
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
function getWeightDateTime(){
    $beginDate=date('Y-m-01', strtotime(date("Y-m-d")));
    $endDate=date('Y-m-d', strtotime("$beginDate +1 month -1 day"));
    $arr=array();
//    echo $beginDate;
//    echo "####";
//    echo strtotime($beginDate." 00:00:00");
//    echo "####";
//    echo $endDate;
//    echo "####";
//    echo strtotime($endDate." 23:59:59");
    $arr["start"]=strtotime($beginDate." 00:00:00");
    $arr["end"]=strtotime($endDate." 23:59:59");
    return $arr;
}

//1.获取上个月第一天及最后一天.
//   echo date('Y-m-01', strtotime('-1 month'));
//   echo "<br/>";
//   echo date('Y-m-t', strtotime('-1 month'));
//   echo "<br/>";
//2.获取当月第一天及最后一天.
//$BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
//   echo $BeginDate;
//   echo "<br/>";
//   echo date('Y-m-d', strtotime("$BeginDate +1 month -1 day"));
//   echo "<br/>";
//3.获取当天年份、月份、日及天数.
//   echo " 本月共有:".date("t")."天";
//   echo " 当前年份".date('Y');
//   echo " 当前月份".date('m');
//   echo " 当前几号".date('d');
//   echo "<br/>";
//4.使用函数及数组来获取当月第一天及最后一天,比较实用
//   function getthemonth($date)
//   {
//       $firstday = date('Y-m-01', strtotime($date));
//       $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
//       return array($firstday,$lastday);
//   }
//   $today = date("Y-m-d");
//   $day=getthemonth($today);
//   echo "当月的第一天: ".$day[0]." 当月的最后一天: ".$day[1];
//   echo "<br/>";



class StoreLevel{
    const LEVEL0=0;
    const LEVEL1=1;
    const LEVEL2=2;
    const LEVEL3=3;
    const LEVEL4=4;
    const LEVEL5=5;
}

class MoneyType
{
    const MONEY = 0;
    const BONUS = 1;
    const AWARD = 2;
    const AWARD_AMOUNT = 3;
    const AWARD_PENDING = 4;
    const COST0 = 5;   //记录用户升级表也使用此常量
    const COST1 = 6;
    const COST2 = 7;
    const COST3 = 8;
    const COST4 = 9;
    const COST5 = 10;
    const COST0_LEVEL = 11;
    const COST1_LEVEL = 12;
    const COST2_LEVEL = 13;
    const COST3_LEVEL = 14;
    const COST4_LEVEL = 15;
    const COST5_LEVEL = 16;

    public static function adapter($str)
    {
        $str = strtoupper($str);
        switch ($str) {
            case "COST0":
                return self::COST0;
            case "COST1":
                return self::COST1;
            case "COST2":
                return self::COST2;
            case "COST3":
                return self::COST3;
            case "COST4":
                return self::COST4;
            case "COST5":
                return self::COST5;
            case "COST0_LEVEL":
                return self::COST0_LEVEL;
            case "COST1_LEVEL":
                return self::COST1_LEVEL;
            case "COST2_LEVEL":
                return self::COST2_LEVEL;
            case "COST3_LEVEL":
                return self::COST3_LEVEL;
            case "COST4_LEVEL":
                return self::COST4_LEVEL;
            case "COST5_LEVEL":
                return self::COST5_LEVEL;
            default:
                throw new \Exception("获取类型失败！");
        }
    }

    public static function code2str($code)
    {
        if ($code == self::MONEY) {
            return "现金";
        } elseif ($code == self::BONUS) {
            return "分红";
        } elseif ($code == self::AWARD) {
            return "奖金";
        } else {
//            throw new \Think\Exception("不存在的资金代码！");
            return "未知";
        }
    }

}

class MsgLevel
{
    const NORMAL = 0;
    const IMPORTANT = 1;
}
class MsgStatus{
    const WAIT_FOR_READ=0;
    const HAS_READ=1;
}
class MoneyEvent
{
    const GRANT_MONEY = 0;//发放现金
    const GRANT_BONUS = 1;//发放分红
    const GRANT_AWARD = 2;//发放奖金
    const SETTLE__BONUS = 3;//结算分红
    const SETTLE__AWARD = 4;//结算奖金
    const LEVELUP = 5;//升级
    const WITHDRAW = 6; //申请提现
    const WITHDRAW_PASS = 7;//提现通过
    const WITHDRAW_REFUSE = 8;//提现拒绝
    const COUNT = 9;//计数统计
    const PAY = 10;//用户支付
    const USE_MONEY = 11;//使用余额支付
    const USE_BONUS = 12;//使用分红支付
    const USE_AWARD = 13;//使用奖金支付

    public static function code2str($code)
    {
        if ($code == self::GRANT_MONEY) {
            return "发放现金";
        } elseif ($code == self::GRANT_BONUS) {
            return "发放分红";
        } elseif ($code == self::GRANT_AWARD) {
            return "发放奖金";
        } elseif ($code == self::SETTLE__BONUS) {
            return "结算分红";
        } elseif ($code == self::SETTLE__AWARD) {
            return "结算奖金";
        } elseif ($code == self::LEVELUP) {
            return "升级";
        } elseif ($code == self::WITHDRAW) {
            return "申请提现";
        } elseif ($code == self::WITHDRAW_PASS) {
            return "提现通过";
        } elseif ($code == self::WITHDRAW_REFUSE) {
            return "提现拒绝";
        } elseif ($code == self::COUNT) {
            return "发放现金";
        } elseif ($code == self::PAY) {
            return "用户支付";
        } elseif ($code == self::USE_MONEY) {
            return "使用余额支付";
        } elseif ($code == self::USE_BONUS) {
            return "使用分红支付";
        } elseif ($code == self::USE_AWARD) {
            return "使用奖金支付";
        } else {
            throw new \Exception("不存在的事件代码！");
        }
    }
}

class PayMode
{
    const ALIPAY = 0;
    const WXPAY = 1;
    const MONEY = 2;
    const BONUS = 3;
    const AWARD = 4;
}

class PayLogStatus
{
    const WAIT_FOR_PAY = 0;
    const PAY_SUCSSES = 1;
    const  PAY_CLOSED = 2;
}

class SettleStatus
{
    const WAIT_SETTLE = 0;
    const SETTLED = 1;
}

class UserStatus
{
    const PASSED = 0;//正常用户
    const FROZEN = 1; //禁用用户

    public static function getConfig()
    {
        $arr = array();
        $arr[0] = "正常";
        $arr[1] = "禁用";
        return $arr;
    }
}

class RoleStatus
{
    const WAIT_AUTHSTR = 0;//正常用户
    const PASSED = 1;//待审核用户
    const FROZEN = 2; //冻结用户

    public static function getConfig()
    {
        $arr = array();
        $arr[0] = "待审核";
        $arr[1] = "正常";
        $arr[2] = "冻结";
        return $arr;
    }
}

class AuditStatus
{
    const DEFAULT_STATUS = 0;//后台添加的默认用户状态
    const AUTHSTR = 1;//待审核
    const REFUSED = 2;//审核被拒绝
    const PASSED = 3;//审核通过
    const NOT_SUBMIT = 4;//未提交审核
    const MODIFY_AUTHSTR = 5;

    public static function getConfig()
    {
        $arr = array();
        $arr[0] = "默认";
        $arr[1] = "待审核";
        $arr[2] = "被拒绝";
        $arr[3] = "通过";
        $arr[4] = "未审核";
        return $arr;
    }
}
