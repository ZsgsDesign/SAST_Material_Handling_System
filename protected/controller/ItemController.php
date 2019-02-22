<?php

class ItemController extends BaseController
{

    public function actionDetail()
    {
        $this->iid=$iid=arg('iid');
        $this->isMyItem=IsMyItem($iid);
        $this->url="item/detail/".$iid;

        $user=new Model("users");
        $item=new Model("item");
        $order=new Model("order");
        $messages=new Model('messages');
        $item_res=$item->find(array("iid=:iid",":iid" => $iid));

        if(empty($iid)||empty($item_res)){
            $this->title = "很抱歉，您查看的物品找不到了！";
            $this->display("404/item.html"); //实现了物品的 404 页面
            return;
        }
        else{
            $this->item_info = array(
                "iid" => $item_res["iid"],
                "name" => $item_res["name"],
                "scode" => $item_res["scode"],
                "owner" => $item_res["owner"],
                "create_time" => $item_res["create_time"], //发布时间
                "limit_time" => $item_res["limit_time"],//借用时限
                "count" => $item_res["count"],
                "picture" => $item_res["pic"],
                "gcount" => $item_res["gcount"],
                "mcount" => $item_res["mcount"],
                "bcount" => $item_res["bcount"],
                "credit_limit" => $item_res["credit_limit"], //信用分限制
                "location" => $item_res["location"], //物品地点
                "order_count" => $item_res["order_count"], //出借笔数
                "desc" => $item_res["dec"] //物品描述
            );
            $user_res=$user->find(array("uid=:uid",":uid" => $item_res["owner"]));
            $item_count=count($item->findAll(array(
                "owner=:owner AND scode > -1", //只显示未下架的物品
                ":owner" => $item_res["owner"]
                )));
            $order_count=count($order->query("SELECT `order`.*,item.`owner` FROM `order` JOIN item ON `order`.item_id=item.iid WHERE `owner`=".$user_res['uid']));
            $this->publisher_info = array(
                "publisher" => $user_res["real_name"],
                "publisher_credit" => $user_res["credit"], //出借者信用
                "publisher_order_count" => $order_count, //总出借笔数
                "publisher_item_count" => $item_count, //发布物品数
            );
            $messages_res=$messages->query("SELECT `messages`.*,`users`.uid,`users`.real_name,`users`.avatar FROM `messages` JOIN `users` ON `messages`.`user_id` = `users`.`uid` WHERE `messages`.item_id = ".$iid);
            $message_info=array();            
            foreach($messages_res as $seq => $value){
                $diff_time = strtotime('now') - strtotime($value['time']);
                if($diff_time < 60){
                    $messages_res[$seq]['time']="刚刚";
                }
                else if($diff_time < 3600){
                    $messages_res[$seq]['time']=round($diff_time/60)." 分钟前";
                }
                else if($diff_time < 86400){
                    $messages_res[$seq]['time']=round($diff_time/3600)." 小时前";
                }
                else if($diff_time < 258200){
                    $messages_res[$seq]['time']=round($diff_time/86400)." 天前";
                }
                else{
                    $messages_res[$seq]['time']=date("Y年m月d日",strtotime($value['time']));
                }
                $messages_res[$seq]['liked']=unserialize($value['liked']) === false ? 0 :count(unserialize($value['liked']));
            }

            foreach($messages_res as $seq => $message){
                if($message['reference'] === NULL||$message['reference'] == -1){
                    array_push($message_info,$message);
                    $message_info[count($message_info)-1]["comments"]=array();
                }
            }
            foreach($messages_res as $seq => $message){
                if($message['reference'] !== NULL && $message['reference'] != -1){
                    $messages_res[$seq]['refer_real_name']=matchColumn($messages_res,'mid',$message['reference'],'real_name');
                    $messages_res[$seq]['refer_id']=matchColumn($messages_res,'mid',$message['reference'],'uid');
                    $messages_res[$seq]['refer_avatar']=matchColumn($messages_res,'mid',$message['reference'],'avatar');
                    $root_message=$message;
                    while($root_message['reference']&&$root_message['reference'] != -1){
                        $root_message=$messages_res[matchColumn($messages_res,'mid',$root_message['reference'],'KEY')];
                    }
                    array_push($message_info[matchColumn($message_info,'mid',$root_message['mid'],'KEY')]['comments'],$messages_res[$seq]);
                }
            }
            foreach($message_info as $seq => $value){
                if(count($value['comments']) === 0){
                    $message_info[$seq]['comments']=NULL;
                }
            }
            for($i=0;$i<count($message_info);$i++){
                if($message_info[$i]['reference'] == -1){
                    array_splice($message_info,$i,1);
                    $i--;
                }
            }

            // dump($message_info);
            $this->messages=$message_info;

        }
        $this->title=$this->item_info["name"]." - 物品详情";
    }

    public function actionNew()
    {
        $this->url="item/new";
        $this->title="发布物品";
        $this->iid=-1;
        $this->item_info = array( //这里设置默认的内容
            "name" => "",
            "limit_time" => 1,//借用时限
            "count" => 1,
            "credit_limit" => 0, //信用分限制
            "location" => "", //物品地点
            "desc" => "" //物品描述
        );
    }

    public function actionEdit()
    {
        $this->iid=$iid=arg('iid');
        $this->url="item/edit/".$iid;
        $this->title="编辑物品";
        if(empty($iid) || !IsMyItem($iid)) { //只有自己的物品才可以编辑
            $this->jump("{$this->MHS_DOMAIN}/item/new");
        }
        $item=new Model("item");
        $item_res=$item->find(array("iid=:iid",":iid" => $iid));

        if(empty($item_res)) //确保物品存在
            $this->jump("{$this->MHS_DOMAIN}/item/new");
        //TODO 测试一下这些还原有没有问题
        $desc = str_replace("/", "\\" ,$item_res["dec"]);
        $desc = str_replace("\\n","\n",$desc);
        $desc = str_replace('\"','"',$desc);

        $this->item_info = array(
            "name" => $item_res["name"],
            "limit_time" => $item_res["limit_time"],//借用时限
            "count" => $item_res["count"],
            "credit_limit" => $item_res["credit_limit"], //信用分限制
            "location" => $item_res["location"], //物品地点
            "desc" => $desc //物品描述
        );
        $this->display("item_new.html"); //共用前端

    }

}