<?php

class AjaxController extends BaseController
{
    public function actionPublishItem(){
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
            $desc = str_replace("\\", "/" ,$desc);  // 斜杠问题
            $desc = str_replace("\n","\\n",$desc); //解决换行问题
            $desc = str_replace('"','\"',$desc);  // 双引号bug修复


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

    public function actionRemoveItem(){
        if (!($this->islogin)) {
            ERR::Catcher(2001);
        }
        $iid=arg("iid");
        if (empty($iid)) {
            ERR::Catcher(1003);
        }
        $item=new Model("item");
        $item_res=$item->find(array(
            "iid = :iid",
            ":iid" => $iid,
        ));
        if(!$item_res)
            ERR::Catcher(100002); //没有该物品
        else{
            if(!IsMyItem($iid)) { //防止下架他人物品
                ERR::Catcher(2003);
            }
            else if($item_res['scode'] < 0)
                ERR::Catcher(100003); //已经下架了
            else{
                $item->update(array(
                    "iid = :iid",
                    ":iid" => $iid,
                ),array(
                    'scode' => '-1'
                ));
                SUCCESS::Catcher("下架成功！");
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
            $count=intval($count);
            if(empty($iid)||empty($count)){
                ERR::Catcher(1003);//空iid或空数量或数量为0 报错  参数不全
            }
            else{
                if(($target_item=$item->find(array("iid=:iid",":iid" => $iid)))===false){
                    ERR::Catcher(1004);//当不符合 有此物品， 报错  参数非法
                }
                else{
                    if($count<1){
                        $cart->delete(array(
                            "user= :user AND item_id = :iid",
                            ":user" => $uid,
                            ":iid" => $iid,
                        ));
                        SUCCESS::Catcher("删除成功！");//当数量为负数时，删除此物品
                    }
                    else{
                        if($cart->find(array(
                            "user = :user AND item_id = :iid",
                            ":user" => $uid,
                            ":iid" => $iid,
                        ))===false){
                            if($target_item['scode']==='1'){
                                $cid=$cart->create(
                                    array(
                                        'user' => $uid,
                                        'item_id' => $iid,
                                        'count' => $count,
                                    )
                                );
                                SUCCESS::Catcher("添加成功！",array(
                                    'cid' => $cid,
                                ));
                            }
                            else{
                                ERR::Catcher(1004);//添加的物品状态不为有货时 报错 参数不全
                            }
                        }
                        else{
                            $cart->update(
                                array(
                                    'user = :user AND item_id = :iid',
                                    ":user" => $uid,
                                    ":iid" => $iid,
                                ),
                                array(
                                    "count" => $count,
                                )
                            );
                            SUCCESS::Catcher("修改成功！");
                        }
                    }
                }
            }
        }
    }
}