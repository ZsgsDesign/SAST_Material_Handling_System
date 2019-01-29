<?php

class ItemController extends BaseController
{

    public function actionDetail()
    {
        $oid=arg('oid');
        $this->url="item/detail";
        //TODO 当$oid($oid为订单的编号)为空，显示的是测试内容，后期记得改为跳转至404页面
        if(empty($oid)){
            $this->item_info = array(
                "name" => "你好,这是个测试样例,查询物品请传入iid的值",
                "create_time" => "2019/1/27 20:00", //发布时间
                "limit_time" => "3",//借用时限
                "count" => "1",
                "credit_limit" => "60", //信用分限制
                "location" => "未知", //物品地点
                "order_count" => "1000", //出借笔数
                "desc" => "物品的描述" //物品描述
            );
            $this->publisher_info = array(
                "publisher" => "出借人",
                "publisher_credit" => "60", //出借者信用
                "publisher_order_count" => "60", //总出借笔数
                "publisher_item_count" => "100" //发布物品数
            );
        }
        else{
            $order=new Model("`order`");
            $user=new Model("users");
            $item=new Model("item");
            $order_res=$order->find(array(
                "oid=:oid",
                ":oid" => $oid,
            ));
            $user_res=$user->find(array(
                "uid=:uid",
                ":uid" => $order_res["renter_id"],
            ));
            $this->publisher_info = array(
                "publisher" => $user_res["name"],
                "publisher_credit" => $user_res["credit"], //出借者信用
                "publisher_order_count" => "暂时空缺", //总出借笔数
                "publisher_item_count" => "暂时空缺" //发布物品数
            );//TODO上面两处空缺需要一些sql方面的高级操作，后续再补
            $item_res=$item->find(array(
                "iid=:iid",
                ":iid" => $order_res["item_id"],
            ));
            $this->item_info = array(
                "name" => $item_res["name"],
                "scode" => $item_res["scode"],
                "owener" => $item_res["owner"],
                "create_time" => $item_res["create_time"], //发布时间
                "limit_time" => $item_res["limit_time"],//借用时限
                "count" => $item_res["count"],
                "picture" => $item_res["pic"],
                "gcount" => $item_res["gcount"],
                "mcount" => $item_res["mcount"],
                "bcount" => $item_res["bcount"],
                "credit_limit" => $item_res["credit_limit"], //信用分限制
                "location" => $item_res["location"], //物品地点
                "order_count" => $item_res["order_count"], //出借笔数
                "desc" => $item_res["dec"] //物品描述
            );
        }
        $this->title=$this->item_info["name"]." - 物品详情";
    }

    public function actionNew()
    {
        $this->url="item/new";
        $this->title="发布物品";
    }

    public function actionEdit()
    {
        $this->url="item/edit";
        $this->title="发布物品";
    }

}