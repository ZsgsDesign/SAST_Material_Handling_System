<?php

class AjaxController extends BaseController
{
    public function actionPublishItem()
    {
        if (!($this->islogin)) {
            ERR::Catcher(2001);
        }
        if (!(arg("name"))) {
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
                    'scode' => "1",//此处约定物品无库存的状态码为0，物品有库存为1，物品下架为-1
                    'name' => $name,
                    'count' => $number,
                    "owner" => $this->userinfo['uid'],
                    "dec" => $desc,
                    "create_time"=> date("Y-m-d H:i:s",time()),
                    "limit_time" => $timeLimit,
                    "location" => $location,
                    "credit_limit" => $creditRequired,
                )
            );

            $result = UploadPic($iid);
            if($result != 200){
                ERR::Catcher($result); //上传图片失败
                $item->delete(array("iid=:id",":id"=>$iid));
            }
            else
            {
                SUCCESS::Catcher("发布成功！",array(
                    'itemid'=>$iid, //传回物品id
                ));
            }
        }
    }
    public function actionAddToCart(){
        $iid=arg('iid');
        $count=arg('count');
        $uid=$this->userinfo['uid'];
        $cart=new Model("cart");
        $item=new Model("item");
        if(empty($uid)){
            ERR::Catcher(2001);
        }
        else{
            if(empty($iid)||empty($count)){
                ERR::Catcher(1003);
            }
            else{
                if(intval($count)<1||$item->find(array("iid=:iid and scode=1",":iid" => $iid))===false){
                    ERR::Catcher(1004);
                }
                else{
                    $cid=$cart->create(
                        array(
                            'user' => $uid,
                            'item_id' => $iid,
                            'count' => $count,
                        )
                    );
                    SUCCESS::Catcher("添加成功",array(
                        'cid' => $cid,
                    ));
                }
            }
        }
    }
}