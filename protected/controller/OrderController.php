<?php

class OrderController extends BaseController
{

    public function actionView()
    {
        $this->url="order/view";
        $this->title="查看订单";
        $oid=arg('oid');
        if(!$this->islogin)
            $this->jump("{$this->MHS_DOMAIN}/account/?return=orders");

        $order=new Model('`order`');
        $users=new Model('`user`');
        if(empty($oid)){
            return $this->jump("{$this->MHS_DOMAIN}/orders");
        }
        else{
            $order_res=$order->query("SELECT a.*,users.real_name,users.avatar,renter.real_name AS renter_real_name,renter.avatar AS renter_avatar FROM (SELECT `order`.*,item.iid,item.`name`,item.`owner`,item.location,item.`dec`,item.limit_time FROM `order` JOIN item ON `order`.item_id = item.iid) AS a JOIN users ON users.uid=a.`owner` JOIN users as renter ON renter.uid=a.renter_id where ( a.renter_id= ".$this->userinfo['uid']." OR a.`owner` = ".$this->userinfo['uid']." ) AND a.oid=".$oid);
            if(count($order_res)){
                $order_res=$order_res[0];
                $order_res['due_time']=date("Y-m-d H:i:s",strtotime("+".$order_res['limit_time']." day",strtotime(@$order_res['rent_time'])));
                $this->order=$order_res;
                if($this->userinfo['uid'] == $order_res['owner']){
                    $order->update(
                        array(
                            "oid = :oid",
                            ":oid" => $oid
                        ),
                        array(
                            "owner_checked" => NULL
                        )
                    );
                }
                else if($this->userinfo['uid'] == $order_res['renter_id']){
                    $order->update(
                        array(
                            "oid = :oid",
                            ":oid" => $oid
                        ),
                        array(
                            "renter_checked" => NULL
                        )
                    );
                }
            }
            else{
                return $this->jump("{$this->MHS_DOMAIN}/orders");
            }
        }
    }

    public function actionCreate()
    {
        $this->url="order/create";
        $selected=arg('item');//不传值 默认全选
        $this->title="创建订单";
        if(!$this->islogin)
            $this->jump("{$this->MHS_DOMAIN}/account/");

        $cart=new Model('cart');
        $sql="SELECT a.*,users.real_name,users.uid,users.avatar FROM (SELECT cart.*,item.`name`,item.scode,item.`owner`,item.location FROM cart JOIN item ON cart.item_id=item.iid) AS a JOIN users ON a.`owner`=users.uid WHERE a.scode = 1 AND a.`user`= ".$this->userinfo['uid']." ";
        if(!empty($selected)){
            $items_sql=implode(" OR item_id=",$selected);
            $sql=$sql." AND( item_id=".$items_sql.")";
        }
        $cart_res=$cart->query($sql);
        $cart_new_res = [];
        foreach ($cart_res as $r){
            if(!array_key_exists($r['uid'],$cart_new_res)){
                $cart_new_res[$r['uid']]['real_name'] = $r['real_name'];
                $cart_new_res[$r['uid']]['avatar'] = $r['avatar'];
                $cart_new_res[$r['uid']]['items'] = [];
            }
            array_push($cart_new_res[$r['uid']]['items'],$r);
        }

        $total_count=array_sum(array_column($cart_res,'count'));
        $this->total_count=$total_count;
        $this->total_item=count($cart_res);
        $this->order_item=$cart_new_res;
    }

}