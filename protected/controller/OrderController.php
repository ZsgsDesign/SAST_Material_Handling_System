<?php

class OrderController extends BaseController
{

    public function actionView()
    {
        $this->url="order/view";
        $this->title="查看订单";
        $oid=arg('oid');
        $order=new Model('`order`');
        $users=new Model('`user`');
        if(empty($oid)){
            return $this->jump("{$this->MHS_DOMAIN}/user?tab=order");
        }
        else{
            $order_res=($order->query("SELECT a.*,users.real_name,renter.real_name AS renter_real_name FROM (SELECT `order`.*,item.iid,item.`name`,item.`owner`,item.location,item.`dec`,item.limit_time FROM `order` JOIN item ON `order`.item_id = item.iid) AS a JOIN users ON users.uid=a.`owner` JOIN users as renter ON renter.uid=a.renter_id where ( a.renter_id= ".$this->userinfo['uid']." OR a.`owner` = ".$this->userinfo['uid']." ) AND a.oid=".$oid))[0];
            if($order_res['scode'] === '3'&&strlen($order_res['owner_review'])&&strlen($order_res['renter_review'])){
                $order->update(
                    array(
                        "oid = :oid",
                        ":oid" => $oid,
                    ),
                    array(
                        "scode" => 4,
                    )
                );
                $order_res=($order->query("SELECT a.*,users.real_name,renter.real_name AS renter_real_name FROM (SELECT `order`.*,item.iid,item.`name`,item.`owner`,item.location,item.`dec`,item.limit_time FROM `order` JOIN item ON `order`.item_id = item.iid) AS a JOIN users ON users.uid=a.`owner` JOIN users as renter ON renter.uid=a.renter_id where ( a.renter_id= ".$this->userinfo['uid']." OR a.`owner` = ".$this->userinfo['uid']." ) AND a.oid=".$oid))[0];
            }
            $order_res['due_time']=date("Y-m-d H:i:s",strtotime("+".$order_res['limit_time']." day",strtotime(@$order_res['rent_time'])));
            if($order_res['scode'] === '2'&&(strtotime('now') > strtotime($order_res['due_time']))){
                $curren_creidt=$users->find(array("uid = :uid",":uid" => $order_res['renter_id']))['credit'];
                $users->update(
                    array(
                        "uid = :uid",
                        ":uid" => $order_res['renter_id'],
                    ),
                    array(
                        "credit" => intval($curren_creidt)-10 
                    )
                );
                $order->update(
                    array(
                        "oid = :oid",
                        ":oid" => $oid,
                    ),
                    array(
                        "scode" => 6,
                    )
                );
                
                $order_res=($order->query("SELECT a.*,users.real_name,renter.real_name AS renter_real_name FROM (SELECT `order`.*,item.iid,item.`name`,item.`owner`,item.location,item.`dec`,item.limit_time FROM `order` JOIN item ON `order`.item_id = item.iid) AS a JOIN users ON users.uid=a.`owner` JOIN users as renter ON renter.uid=a.renter_id where ( a.renter_id= ".$this->userinfo['uid']." OR a.`owner` = ".$this->userinfo['uid']." ) AND a.oid=".$oid))[0];
                $order_res['due_time']=date("Y-m-d H:i:s",strtotime("+".$order_res['limit_time']." day",strtotime(@$order_res['rent_time'])));
            }
            $this->order=$order_res;
        }
    }

    public function actionCreate()
    {
        $this->url="order/create";
        $selected=arg('item');//不传值 默认全选
        $this->title="创建订单";
        $cart=new Model('cart');
        $sql="SELECT a.*,users.real_name FROM (SELECT cart.*,item.`name`,item.scode,item.`owner`,item.location FROM cart JOIN item ON cart.item_id=item.iid) AS a JOIN users ON a.`owner`=users.uid WHERE a.scode = 1 AND a.`user`= ".$this->userinfo['uid']." ";
        if(!empty($selected)){
            $items_sql=implode(" OR item_id=",$selected);
            $sql=$sql." AND( item_id=".$items_sql.")";
        }
        $cart_res=$cart->query($sql);
        $total_count=array_sum(array_column($cart_res,'count'));
        $this->total_count=$total_count;
        $this->order_item=$cart_res;
    }

}