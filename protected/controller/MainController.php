<?php
class MainController extends BaseController
{
    public function actionIndex()
    {
        $this->url="index";
        $this->title="首页";
    }

    public function actionDetail()
    {
        $this->url="detail";
        $this->title="物品详情";
    }

    public function actionCart()
    {
        $this->url="cart";
        $this->title="购物车";
    }

    public function actionMhs()
    {
        $this->jump("{$this->MHS_DOMAIN}/");
    }

    public function actionAccount()
    {
        $this->jump("{$this->MHS_DOMAIN}/account/");
    }
}