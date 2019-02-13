<?php

class OrderController extends BaseController
{

    public function actionView()
    {
        $this->url="order/view";
        $this->title="查看订单";
    }

    public function actionCreate()
    {
        $this->url="order/create";
        $selected=arg('item');
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