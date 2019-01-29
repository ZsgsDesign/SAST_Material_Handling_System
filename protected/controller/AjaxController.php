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
        else{
            $item=new Model("item");
            $name=arg("name");
            $timeLimit=arg("timeLimit");
            $location=arg("location");
            $creditRequired=arg("creditRequired");
            $number=arg("number");
            $desc=arg("desc");
            $iid=$item->create(
                array(
                    'scode' => "1",//此处约定物品无库存的状态码为0，物品有库存为1
                    'name' => $name,
                    'count' => $number,
                    "owner" => $this->userinfo['uid'],
                    "dec" => $desc,
                    "create_time"=> date("Y-m-d H:i:s",time()),
                    "limit_time" => $timeLimit,
                    "pic" => "",//等前端部分可以上传图片了再补充
                    "location" => $location,
                    "credit_limit" => $creditRequired,   
                )
            );
            SUCCESS::Catcher("发布成功！",array(
                'itemid'=>$iid, //传回物品id
            ));
        }
        
    }
}