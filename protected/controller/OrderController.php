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
        //上面的参数是通过购物车下单时使用的，
        //下面的参数是立即购买使用的
        $iid=arg('iid');
        $count=arg('count');

        $this->title="创建订单";
        if(!$this->islogin)
            $this->jump("{$this->MHS_DOMAIN}/account/");

        if(!empty($selected)&&empty($iid)&&empty($count)){
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
            $total_item=count($cart_res);
        }
        else if(empty($selected)&&!empty($iid)&&!empty($count)){
            if(intval($count) > 0){
                $item=new Model('item');
                $item_res=$item->query("SELECT item.`name`,item.scode,item.`owner`,item.location,users.real_name,users.uid,users.avatar FROM item JOIN users ON users.uid = item.`owner` WHERE item.iid = ".$iid)[0];
                $total_count=$count;
                $total_item=1;
                $cart_new_res=array(
                        0 => [
                        "real_name" => $item_res['real_name'],
                        "avatar" => $item_res['avatar'],
                        "items" => [
                            [
                                "user" => $this->userinfo['uid'],
                                "item_id" => $iid,
                                "name" => $item_res['name'],
                                "scode" => $item_res['scode'],
                                "owner" => $item_res['owner'],
                                "location" => $item_res['location'],
                                "real_name" => $item_res['real_name'],
                                "uid" => $item_res['uid'],
                                "avatar" =>$item_res['avatar'],
                                "count" => $count
                            ]
                        ]
                    ]
                );
            }
            else{
                $this->jump("{$this->MHS_DOMAIN}/account/");
            }
        }
        else{
            $this->jump("{$this->MHS_DOMAIN}/account/");
        }
        $this->total_count=$total_count;
        $this->total_item=$total_item;
        $this->order_item=$cart_new_res;
    }

}