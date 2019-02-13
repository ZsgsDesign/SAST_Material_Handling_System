<?php

class AjaxController extends BaseController
{
    public function actionPublishItem(){
        if (!($this->islogin)) {
            ERR::Catcher(2001);
        }
        else if (!(arg("name"))) {
            ERR::Catcher(100001);
            }
        else if (!(arg("location"))) {
                ERR::Catcher(100004);
            }
        else if (!(arg("desc"))) {
                ERR::Catcher(100005);
            }
        else if (!(arg("number"))) {
                ERR::Catcher(100006);
            }
        else{
            $item=new Model("item");
            $old_iid=arg("iid");
            $name=arg("name");
            $timeLimit=arg("timeLimit");
            $location=arg("location");
            $creditRequired=arg("creditRequired");
            $number=arg("number");
            $desc=arg("desc");
            $desc = str_replace("\\", "/" ,$desc);  // 斜杠问题
            $desc = str_replace("\n","\\n",$desc); //解决换行问题
            $desc = str_replace('"','\"',$desc);  // 双引号bug修复

            if($old_iid != -1) {  //编辑物品模式
                if(!IsMyItem($old_iid)) {
                    ERR::Catcher(2008); //防止修改他人物品
                    return;
                }
                else{
                    $item->update(array("iid = :iid", ":iid" => $old_iid),array('scode' => '-1'));
                    //旧物品下架处理（存档机制） ，这里是为了防止他人查历史订单时 ，查到新的物品
                }
            }

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
            //TODO 编辑物品后还要更新物品图片
            $result = UploadPic($iid); //最后处理图片
            if($result != 200){
                ERR::Catcher($result); //上传图片失败
                $item->delete(array("iid=:id",":id"=>$iid)); //TODO 这个删除好像有问题
            }
            else
            {
                SUCCESS::Catcher($old_iid == -1 ? "发布成功！" : "编辑成功！" ,array(
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

    public function actionRestoreItem(){
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
            if(!IsMyItem($iid)) { //防止上架他人物品
                ERR::Catcher(2003);
            }
            else if($item_res['scode'] >= 0)
                ERR::Catcher(100007); //已经上架了
            else if($item_res['count'] == 0){
                $item->update(array(
                    "iid = :iid",
                    ":iid" => $iid,
                ),
                array(
                        'scode' => '0'
                ));
                SUCCESS::Catcher("上架成功！");
            }
            else{
                $item->update(array(
                    "iid = :iid",
                    ":iid" => $iid,
                ),
                array(
                        'scode' => '1'
                ));
                SUCCESS::Catcher("上架成功！");
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
    public function actionCreateOrder(){
        //约定order的scode 1 为等待取用， 2为等待归还 ， 3为已借用待评价  ， 4为订单完成  , 5 订单意外取消
        $order=new Model('`order`');
        $iid=arg('iid');
        $count=arg('count');
        if(!empty($iid)&&!empty($count)){
            $oid=$order->create(
                array(
                    'scode' => 1,
                    'item_id' => intval($iid),
                    'create_time' =>date("Y-m-d H:i:s",time()),
                    'renter_id' => $this->userinfo['uid'],
                    'count' => intval($count),
                )
            );
            SUCCESS::Catcher("添加成功！",array(
                'oid' => $oid,
            ));
        }
        else{
            ERR::Catcher(1003);
        }
    }
    public function actionOperateOrder(){
        $order=new Model('`order`');
        $oid=arg('oid');
        $operation=arg('operation');//可能的操作有      确认取用     取消订单       归还
        if($operation==='confirm'){
            $order->update(
                array(
                    "oid = :oid AND renter_id = :renter_id",
                    ':oid' => $oid,
                    ":renter_id" => $this->userinfo['uid'],
                ),
                array(
                    "scode" => 2,
                    // "create_time" => date("Y-m-d H:i:s",time()),
                )
            );
            SUCCESS::Catcher("取用成功！");
        }
        else if($operation==='cancel'){
            $order->update(
                array(
                    "oid = :oid AND renter_id = :renter_id",
                    ':oid' => $oid,
                    ":renter_id" => $this->userinfo['uid'],
                ),
                array(
                    "scode" => 5,//scode 5 为订单意外取消
                )
            );
            SUCCESS::Catcher("取消成功！");
        }
        else if($operation==='return'){
            $order->update(
                array(
                    "oid = :oid AND renter_id = :renter_id",
                    ':oid' => $oid,
                    ":renter_id" => $this->userinfo['uid'],
                ),
                array(
                    "scode" => 3,//scode 5 为订单意外取消
                )
            );
        }
        else{
            ERR::Catcher(1003);
        }
    }
    public function actionReviewOrder(){
        $order=new Model('`order`');
        $type=arg('type');// 判断这是来自renter 或者是 owner 的评价 它的可能的值为 owner或者renter
        $oid=arg('oid');
        $review=arg('review');// 评价的内容
        if(!empty($type)&&!empty($uid)&&!empty($review)){
            if($type==='renter'){
                $order->update(
                    array(
                        "oid = :oid AND renter_id = :renter_id",
                        ':oid' => $oid,
                        ':renter_id' => $this->userinfo['uid'],
                    ),
                    array(
                        'renter_review' => $review,
                    )
                );
            }
            else if($type==='owner'){
                $owner_id=($order->query("SELECT `order`.oid,`order`.item_id,`item`.iid,`item`.`owner` FROM `order` JOIN `item` ON `order`.item_id = `item`.iid ;"))[0]['owner'];
                if($owner_id===$this->userinfo['uid']){
                    $order->update(
                        array(
                            "oid = :oid",
                            ":oid" => $oid,
                        ),
                        array(
                            "owner_review" => $review,
                        )
                    );
                }
                else{
                    ERR::Catcher(1004);//当前登录的用户的id与该订单的owner_id不一致
                }
            }
            else{
                ERR::Catcher(1003);//参数不全
            }
        }
    }
}