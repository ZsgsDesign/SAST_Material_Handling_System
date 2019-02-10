<?php

class ItemController extends BaseController
{

    public function actionDetail()
    {
        $this->iid=$iid=arg('iid');
        $this->isMyItem=IsMyItem($iid);
        $this->url="item/detail/".$iid;

        $user=new Model("users");
        $item=new Model("item");
        $order=new Model("order");
        $item_res=$item->find(array("iid=:iid",":iid" => $iid));

        if(empty($iid)||empty($item_res)){
            $this->title = "很抱歉，您查看的物品找不到了！";
            $this->display("404/item.html"); //实现了物品的 404 页面
            return;
        }
        else{
            $this->item_info = array(
                "iid" => $item_res["iid"],
                "name" => $item_res["name"],
                "scode" => $item_res["scode"],
                "owner" => $item_res["owner"],
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
            $user_res=$user->find(array("uid=:uid",":uid" => $item_res["owner"]));
            $item_count=count($item->findAll(array("owner=:owner",":owner" => $item_res["owner"],)));
            $order_count=count($order->query("SELECT `order`.*,item.`owner` FROM `order` JOIN item ON `order`.item_id=item.iid WHERE `owner`=".$user_res['uid']));
            $this->publisher_info = array(
                "publisher" => $user_res["real_name"],
                "publisher_credit" => $user_res["credit"], //出借者信用
                "publisher_order_count" => $order_count, //总出借笔数
                "publisher_item_count" => $item_count, //发布物品数
            );//TODO 上面两处空缺需要一些sql方面的高级操作，后续再补
            
        }
        $this->title=$this->item_info["name"]." - 物品详情";
    }

    public function actionNew()
    {
        $this->url="item/new";
        $this->title="发布物品";
        $this->iid=-1;
        $this->item_info = array( //这里设置默认的内容
            "name" => "",
            "limit_time" => 1,//借用时限
            "count" => 1,
            "credit_limit" => 0, //信用分限制
            "location" => "", //物品地点
            "desc" => "" //物品描述
        );
    }

    public function actionEdit()
    {
        $this->iid=$iid=arg('iid');
        $this->url="item/edit/".$iid;
        $this->title="编辑物品";
        if(empty($iid) || !IsMyItem($iid)) { //只有自己的物品才可以编辑
            $this->jump("{$this->MHS_DOMAIN}/item/new");
        }
        $item=new Model("item");
        $item_res=$item->find(array("iid=:iid",":iid" => $iid));

        if(empty($item_res)) //确保物品存在
            $this->jump("{$this->MHS_DOMAIN}/item/new");
        //TODO 测试一下这些还原有没有问题
        $desc = str_replace("/", "\\" ,$item_res["dec"]);
        $desc = str_replace("\\n","\n",$desc);
        $desc = str_replace('\"','"',$desc);

        $this->item_info = array(
            "name" => $item_res["name"],
            "limit_time" => $item_res["limit_time"],//借用时限
            "count" => $item_res["count"],
            "credit_limit" => $item_res["credit_limit"], //信用分限制
            "location" => $item_res["location"], //物品地点
            "desc" => $desc //物品描述
        );
        //dump($this->item_info);
        $this->display("item_new.html"); //共用前端

    }

}