<?php
/**
 * Created by Visual Studio Code.
 * User: Chen Kaisen
 * Date: 2019/2/16
 * Time: 11:11
 */

/*
 * 根据`order`表的信息更新 scode
 * 并且修改给用户的credit
 */
function updateScode($uid){
    if(!empty($uid)){
        $order=new Model('`order`');
        $users=new Model('users');

        $orders=$order->query('SELECT `order`.*,item.iid,item.`name`,item.`owner`,item.limit_time FROM `order` JOIN item ON item.iid=`order`.item_id WHERE `owner`= '.$uid.' OR renter_id= '.$uid);

        foreach($orders as $seq => $r){
            if($r['scode'] === '3'&&strlen($r['owner_review'])&&strlen($r['renter_review'])){
                $order->update(
                    array(
                        "oid = :oid",
                        ":oid" => $r['oid'],
                    ),
                    array(
                        "scode" => 4,
                    )
                );
            }//若双方都已评论则订单完成

            $r['due_time']=date("Y-m-d H:i:s",strtotime("+".$r['limit_time']." day",strtotime(@$r['rent_time'])));
            if($r['scode'] === '2'&&(strtotime('now') > strtotime($r['due_time']))){
                $curren_creidt=$users->find(array("uid = :uid",":uid" => $r['renter_id']))['credit'];
                $users->update(
                    array(
                        "uid = :uid",
                        ":uid" => $r['renter_id'],
                    ),
                    array(
                        "credit" => intval($curren_creidt)-10 
                    )
                );
                $order->update(
                    array(
                        "oid = :oid",
                        ":oid" => $r['oid'],
                    ),
                    array(
                        "scode" => 6,
                    )
                );
                
            }//若当前时间超过到期时间，则更新scode 并且修改users.credit
        }
    }
}
