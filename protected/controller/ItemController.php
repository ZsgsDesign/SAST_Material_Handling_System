<?php

class ItemController extends BaseController
{

    public function actionDetail()
    {
        $this->url="item/detail";
        
        $this->item_info = array(
            "name" => "物品的名称",
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