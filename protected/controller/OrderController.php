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
        $this->title="创建订单";
    }

}