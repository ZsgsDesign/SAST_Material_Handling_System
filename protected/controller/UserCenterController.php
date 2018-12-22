<?php
class UserCenterController extends BaseController
{
    public function actionIndex()
    {
        $userinfo = getuserinfo(@$_SESSION['OPENID']);
        $this->url="usercenter";
        $this->title="用户中心";       
        if(!$this->islogin)
        {
            $this->jump("{$this->MHS_DOMAIN}/account/");
        }
    }
}
