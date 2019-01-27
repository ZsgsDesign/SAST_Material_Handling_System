<?php

class AjaxController extends BaseController
{
    public function actionPublishItem()
    {
        if (!($this->islogin)) {
            ERR::Catcher(2001);
        }
        if (!(arg("name")))
        {
            ERR::Catcher(100001); //提示名称为空
        }
        SUCCESS::Catcher("发布成功！",array(
            'itemid'=>10000, //传回物品id
        ));
    }
}