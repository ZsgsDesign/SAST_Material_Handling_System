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
                    'scode' => "1",//此处约定物品无库存的状态码为0，物品有库存为1，物品下架为-1，让该物品对owner隐藏为-2
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

            $fileTypes = array('jpg', 'jpeg', 'png'); //支持的图片格式
            $picMaxSize = 1024 * 1024; //图片限制大小
            $targetPath = CONFIG::GET("MHS_PIC_SERVICE_ROOT");

            if (!empty($_FILES)) {
                $ext = pathinfo($_FILES['pic']['name'])['extension'];
                if($_FILES['pic']['type'] == "" || !in_array($ext,$fileTypes)) // 确认文件类型
                    ERR::Catcher(200001);
                else if($_FILES['pic']['size'] > $picMaxSize) //大小过大
                    ERR::Catcher(200002);
                else {
                    if(file_exists($targetPath.$iid))
                        unlink($targetPath.$iid);//已存在则删除
                    move_uploaded_file($_FILES['pic']['tmp_name'],$targetPath.$iid);
                }
            }
            else if($old_iid != -1) {  //编辑物品模式
                if(file_exists($targetPath.$old_iid))
                    copy($targetPath.$old_iid,$targetPath.$iid);
            }
            SUCCESS::Catcher($old_iid == -1 ? "发布成功！" : "编辑成功！" ,array(
                'itemid'=>$iid, //传回物品id
            ));
        }
    }

    public function actionDeleteItem(){
        if (!($this->islogin)) {
            ERR::Catcher(2001);
        }
        else{
            $iid=arg('iid');
            if(empty($iid)){
                ERR::Catcher(1003);
            }
            else{
                $items=new Model('item');
                $items->update(
                    array(
                        "iid = :iid AND owner = :owner",
                        ":iid" => $iid,
                        ":owner" => $this->userinfo['uid']
                    ),
                    array(
                        "scode" => -2
                    )
                );
                SUCCESS::Catcher("成功删除!");
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
        if (!($this->islogin)) {
            ERR::Catcher(2001);
            return;
        }
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
        if (!($this->islogin)) {
            ERR::Catcher(2001);
            return;
        }
        //约定order的scode 1 为等待取用， 2为成功取用等待归还 ， 3为已归还待评价(即二人至少有一人未评价)  ， 4为订单完成  , 5 订单意外取消， 6超时未归还
        $order=new Model('`order`');
        $cart=new Model('cart');
        $item=new Model('item');
        $iid=arg('iid');
        $count=arg('count');
        if(!empty($iid) && !empty($count)){
            if(IsMyItem($iid))
                ERR::Catcher(1004);// 防止自己给自己的物品下单
            else {
                $current_count=intval($item->query("SELECT item.count FROM item WHERE item.iid = ".$iid." ;")[0]['count']);
                if(intval($count) > 0 && ($current_count >= intval($count)) ){
                    $oid=$order->create(
                        array(
                            'scode' => 1,
                            'item_id' => intval($iid),
                            'create_time' =>date("Y-m-d H:i:s",time()),
                            'renter_id' => $this->userinfo['uid'],
                            'count' => intval($count),
                            'owner_checked' => 1
                        )
                    );
                    $cart->delete(array(
                        "user = :user AND item_id = :item",
                        ":user" => $this->userinfo['uid'],
                        ":item" => $iid,
                    ));
                    $scode = $current_count - intval($count) == 0 ? 0 : 1 ;  //更新库存信息
                    $item->update(
                        array(
                            "iid = :iid",
                            ":iid" => $iid
                        ),
                        array(
                            "scode" => $scode,
                            "count" => $current_count - intval($count)
                        )
                    );
                    // $name=($order->query("SELECT `order`.*,item.iid,item.name FROM `order` JOIN item ON `order`.item_id = item.iid"))[0]['name'];//TODO 我是想着要不要返回物品的名字，然后提示XXX物品下单成功
                    SUCCESS::Catcher("下单成功",array(
                        'oid' => $oid,
                    ));
                }
                else{
                    ERR::Catcher(1004);//参数错误
                }
            }
        }
        else{
            ERR::Catcher(1003);//参数不全
        }
    }
    public function actionOperateOrder(){
        if (!($this->islogin)) {
            ERR::Catcher(2001);
            return;
        }
        $order=new Model('`order`');
        $item=new Model('item');
        $users=new Model('users');
        $oid=arg('oid');
        $operation=arg('operation');//可能的操作有      确认取用     取消订单       归还
        $order_res = $order->find(array(
            'oid=:id',
            'id' => $oid
        ));
        $owner_id=($order->query("SELECT `order`.oid,`order`.item_id,`item`.iid,`item`.`owner` FROM `order` JOIN `item` ON `order`.item_id = `item`.iid WHERE `order`.oid = ".$oid))[0]['owner'];
        if($operation==='confirm'){ //确认取用
            if($order_res['scode'] != 1) //只有在待确认的情况下才能确认取用
                ERR::Catcher(2008); //请不要皮这个系统
            else if($order->update(
                array(
                    "oid = :oid AND renter_id = :renter_id",
                    ':oid' => $oid,
                    ":renter_id" => $this->userinfo['uid'],
                ),
                array(
                    "scode" => 2,
                    "rent_time" => date("Y-m-d H:i:s",time()),
                    "owner_checked" => 2
                )
            ) > 0){
                 //判断影响行数以防止他人进行确认
                 alterCredit($owner_id,5);
                 SUCCESS::Catcher("取用成功！");
            }
            else{
                ERR::Catcher(1004);
            }
        }
        else if($operation==='cancel'){ //取消订单，TODO 这里双方都可以在确认取用前取消订单
            if($order_res['renter_id'] === $this->userinfo['uid'] || $owner_id === $this->userinfo['uid']){
                if($order_res['scode'] != 1){
                    ERR::Catcher(2008);
                } //只有在待确认的情况下才能取消订单
                else{
                    $order->update(
                        array(
                            "oid = :oid",
                            ':oid' => $oid,
                        ),
                        array(
                            "scode" => 5,//scode 5 为订单意外取消
                            "return_time" => date("Y-m-d H:i:s",time()),
                            "owner_checked" => 5,
                            "renter_checked" => 5
                        )
                    );
                    $res=$order->query("SELECT `order`.oid,`order`.create_time,`order`.item_id,`order`.count AS add_count,item.iid,item.count,item.scode FROM `order` JOIN `item` ON `item`.iid = `order`.item_id WHERE oid = ".$oid)[0];
                    $new_count=intval($res['count']) + intval($res['add_count']);
                    $new_scode = (intval($res['scode']) == 0 ? 1 : intval($res['scode']));  //更新物品状态码（没有库存的情况下）
                    $item->update(
                        array(
                            "iid = :iid",
                            ":iid" => $res['iid']
                        ),
                        array(
                            "count" => $new_count,
                            "scode" => $new_scode
                        )
                    );
                    if(strtotime('now') - strtotime($res['create_time']) > 3600){
                        alterCredit($this->userinfo['uid'],-5);
                    }
                    SUCCESS::Catcher("取消成功！");
                }
            }
            else{
                ERR::Catcher(1004);
            }
        }
        else if($operation==='return'){
            if($order_res['scode'] != 2&& $order_res['scode'] != 6) //只有在待归还或超时的情况下才能进行确认归还
                ERR::Catcher(2008);
            else if($owner_id===$this->userinfo['uid']){
                $order->update(
                    array(
                        "oid = :oid",
                        ':oid' => $oid,
                    ),
                    array(
                        "scode" => 3,//scode 5 为订单意外取消
                        "return_time" => date("Y-m-d H:i:s",time()),
                        "renter_checked" => 3
                    )
                );
                $res=$order->query("SELECT `order`.oid,`order`.item_id,`order`.renter_id,`order`.count AS add_count,item.iid,item.count,item.scode FROM `order` JOIN `item` ON `item`.iid = `order`.item_id WHERE oid = ".$oid)[0];
                $new_count=intval($res['count']) + intval($res['add_count']);
                $new_scode = $res['scode'] == 0 ? 1 : $res['scode'];  //更新物品状态码（没有库存的情况下）
                $item->update(
                    array(
                        "iid = :iid",
                        ":iid" => $res['iid']
                    ),
                    array(
                        "count" => $new_count,
                        "scode" => $new_scode
                    )
                );
                alterCredit($res['renter_id'],$order_res['scode'] === '2'?5:2);
                SUCCESS::Catcher("归还成功！");
            }
            else{
                ERR::Catcher(1004);
            }
        }
        else{
            ERR::Catcher(1003);
        }
    }
    public function actionReviewOrder(){
        if (!($this->islogin)) {
            ERR::Catcher(2001);
            return;
        }
        //TODO 可以考虑使用对象序列化 让renter_review 和owner_review 字段 再存放评价的文字内容
        $order=new Model('`order`');
        $users=new Model('users');
        $oid=arg('oid');
        $review=arg('review');// 评价的内容
        $content=empty(arg('content'))?NULL:arg('content');//评价的文字内容 允许用户的文字评价为空
        if(!empty($oid)&&(!empty($review)||$review === '0')){
            $order_res=($order->query("SELECT `order`.oid,`order`.item_id,`order`.renter_id,`item`.iid,`item`.`owner` FROM `order` JOIN item ON `order`.item_id = item.iid where `order`.oid = ".$oid))[0];
            if($this->userinfo['uid'] === $order_res['renter_id']){
                $order->update(
                    array(
                        "oid = :oid",
                        ":oid" => $oid,
                    ),
                    array(
                        "renter_review" => $review,
                        "renter_review_content" =>$content
                    )
                );
                alterCredit($this->userinfo['uid'],5);
                SUCCESS::Catcher("评价成功！");
            }
            else if($this->userinfo['uid'] === $order_res['owner']){
                $order->update(
                    array(
                        "oid = :oid",
                        ":oid" => $oid,
                    ),
                    array(
                        "owner_review" => $review,
                        "owner_review_content" => $content
                    )
                );
                alterCredit($this->userinfo['uid'],5);
                SUCCESS::Catcher("评价成功！");
            }
            else{
                ERR::Catcher(1004);
            }
        }
        else{
            ERR::Catcher(1003);//参数不全
        }
    }
    public function actionClearChecked(){
        $type=arg("type");//可能的值有A和B，
        $type=strtoupper($type);
        $order=new Model('`order`');
        if($type === 'A'){
            $order->update(
                array(
                    "renter_id = :renter_id AND ( renter_checked = 1 OR renter_checked = 2 OR renter_checked = 3 OR renter_checked = 5 ) ",
                    ":renter_id" => $this->userinfo['uid']
                ),
                array(
                    "renter_checked" => NULL
                )
            );
            $target_oid=@array_column($order->query("SELECT `order`.oid,`order`.item_id,`order`.owner_checked,item.iid,item.`owner` FROM `order` JOIN item ON `order`.item_id = item.iid WHERE (`order`.owner_checked = 1 OR `order`.owner_checked = 2 OR `order`.owner_checked = 3 OR `order`.owner_checked = 5) AND item.`owner` = ".$this->userinfo['uid']),'oid');
            foreach($target_oid as $seq => $oid){
                $order->update(
                    array(
                        "oid = :oid",
                        ":oid" => $oid
                    ),
                    array(
                        "owner_checked" => NULL
                    )
                );
            };
            SUCCESS::Catcher("清空成功");
        }
        else if($type === 'B'){
            $order->update(
                array(
                    "renter_id = :renter_id AND ( renter_checked = 6 )",
                    ":renter_id" => $this->userinfo['uid']
                ),
                array(
                    "renter_checked" => NULL
                )
            );
            $target_oid=array_column($order->query("SELECT `order`.oid,`order`.item_id,`order`.owner_checked,item_id,item.`owner` FROM `order` JOIN item ON `order`.item_id = item.iid WHERE (`order`.owner_checked = 6) AND item.`owner` = ".$this->userinfo['uid']),'oid');
            foreach($target_oid as $seq => $oid){
                $order->update(
                    array(
                        "oid = :oid",
                        ":oid" => $oid
                    ),
                    array(
                        "owner_checked" => NULL
                    )
                );
            };
            SUCCESS::Catcher("清空成功");
        }
        else{
            ERR::Catcher(1004);//参数非法
        }
    }
}