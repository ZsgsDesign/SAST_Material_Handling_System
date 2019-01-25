<?php

class ItemController extends BaseController
{

    public function actionDetail()
    {
        $this->url="item/detail";
        $this->title="物品详情";
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

    public function actionTest()
    {
        $this->url="item/edit";
        $this->title="发布物品";
    }

}