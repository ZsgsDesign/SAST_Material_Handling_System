<?php
session_start();
include ("db.class.php");
$db = new db();
//判断用余额是否满足
$name=$_SESSION['name'];
//获取到用户名
$sye = "select * from  users= '{$name}'";
$ye = $db->query($sye);
$ye[0][0];//这是信用分余额
$ann=array();
if(!empty($_SESSION["order"]))
{
    $ann=$_SESSION["order"];
}
$zhonglei = count($ann);
$aa=0;//总价格
foreach($ann as $k)
{
    $k[0];//物品代号
    $k[1];//物品数量
    $sql1="select limit_time from item where iid='{$k[0]}'";
    $limit_time=$db->Query($sql1,0);
   foreach($limit_time as $n)
    {
        $aa=$aa + $n[0]*$k[1];
    }


}
//判断余额是否满足
if($ye[0][0]>=$aa)
{
    //信用分够,判断库存

    foreach($ann as $v)
    {
        $skc = "select iid,scode from item WHERE iid='{$v[0]}'";
        //物品代号$v[0]
        $akc = $db->query($skc);
        $akc[0][1];//库存
        //比较是否满足库存
        if($akc[0][1]<$v[1])
        {
            echo "{$akc[0][0]}库存不足";
            //退出
            exit;
        }

    }
//提交订单：
//i.    从用户账户中扣除本次购买所用的信用分
//ii.    从商品库存中扣除本次每种商品的购买数量
//iii.    向订单表和订单内容表中加入本次购买的商品信息
    //扣除信用分余额
$skcye = "update users set credit = credit-{$aa} WHERE name = '{$name}'";
    $db->query($skcye,0);
    //扣除库存
    foreach($ann as $v)
    {
        $skckc = "update item set scode = scode-{$v[1]} WHERE iid='{$v[0]}'";
        //物品代号$v[0]
        $db->query($skckc,0);
    }
    //添加订单信息
    //取当前时间
    $create_time = time();
    //自动生成订单号
    $oid= date("YmdHis");
    $sdd = "insert into order VALUES ('{$oid}','$name','$create_time')";
    $db->query($sdd,0);
    //添加订单内容
    foreach ($ann as $v)
    {
        $sddxq = "insert into order VALUES ('','$oid','{$v[0]}','{$v[1]}')";
        $db->query($sddxq,0);
    }


}
else
{
    echo "信用分不足";
    exit;
}
?>