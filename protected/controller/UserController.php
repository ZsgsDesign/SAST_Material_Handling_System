<?php
class UserController extends BaseController
{
    public function actionIndex()
    {
        if(!$this->islogin) {
            $this->jump("{$this->MHS_DOMAIN}/account/");
        }//需要先登录才能查看任何人的主页

        if(arg('uid'))
            $uid = arg('uid');
        else
            $uid = $this->userinfo['uid'];
        $this->uinfo = getuserinfo_id($uid);

        $this->current_tab = empty(arg('tab')) ? 'basic' :arg('tab');
        $this->isMe = $uid == $this->userinfo['uid'];
        $this->call = $this->isMe ? "我" : $this->uinfo['real_name'];
        $this->url="usercenter";
        $this->title="个人中心";

        $user=new Model("users");
        $good_count=count($user->query("SELECT `order`.*, item.* FROM `order` JOIN item ON `order`.item_id=item.iid WHERE `order`.renter_id=:uid AND `order`.owner_review=1 OR item.owner=:uid AND `order`.renter_review=1", array(":uid"=>$uid)));
        $mid_count=count($user->query("SELECT `order`.*, item.* FROM `order` JOIN item ON `order`.item_id=item.iid WHERE `order`.renter_id=:uid AND `order`.owner_review=0 OR item.owner=:uid AND `order`.renter_review=0", array(":uid"=>$uid)));
        $bad_count=count($user->query("SELECT `order`.*, item.* FROM `order` JOIN item ON `order`.item_id=item.iid WHERE `order`.renter_id=:uid AND `order`.owner_review=-1 OR item.owner=:uid AND `order`.renter_review=-1", array(":uid"=>$uid)));
        $br_count=count($user->query("SELECT * FROM `order` WHERE renter_id=:uid", array(":uid"=>$uid)));
        $pb_count=count($user->query("SELECT * FROM item WHERE owner=:uid", array(":uid"=>$uid)));
        $this->wtf_info = array(
           "good_count" => $good_count,
           "mid_count" => $mid_count,
           "bad_count" => $bad_count,
           "br_count" => $br_count,
           "pb_count" => $pb_count,
        );

        $item = new model('item');
        $this->items_info = $item->findAll(array("owner=:uid",":uid" => $uid),'create_time DESC');
        //dump($this->items_info);



        $order=new Model('`order`');
        $order_reses=($order->query("SELECT a.*,users.real_name FROM (SELECT `order`.*,item.iid,item.`name`,item.`owner`,item.location,item.`dec`,item.limit_time FROM `order` JOIN item ON `order`.item_id = item.iid) AS a JOIN users ON users.uid=a.`owner` where a.renter_id= ".$this->userinfo['uid']." OR a.`owner` = ".$this->userinfo['uid'].""));
        foreach($order_reses as $key => $order_res){
            if($order_res['scode'] === '3'&&strlen($order_res['owner_review'])&&strlen($order_res['renter_review'])){
                $order->update(
                    array(
                        "oid = :oid",
                        ":oid" => $order_res['oid'],
                    ),
                    array(
                        "scode" => 4,
                    )
                );
                $order_res=($order->query("SELECT a.*,users.real_name FROM (SELECT `order`.*,item.iid,item.`name`,item.`owner`,item.location,item.`dec`,item.limit_time FROM `order` JOIN item ON `order`.item_id = item.iid) AS a JOIN users ON users.uid=a.`owner` where oid = ".$order_res['oid']));
            }
            $order_reses[$key]['due_time']=date("Y-m-d H:i:s",strtotime("+".$order_res['limit_time']." day",strtotime(@$order_res['rent_time'])));
            if($order_res['scode'] === '2'&&(strtotime('now') > strtotime($order_reses[$key]['due_time']))){
                $order->update(
                    array(
                        "oid = :oid",
                        ":oid" => $order_res['oid'],
                    ),
                    array(
                        "scode" => 6,
                    )
                );
                
                $order_res=($order->query("SELECT a.*,users.real_name FROM (SELECT `order`.*,item.iid,item.`name`,item.`owner`,item.location,item.`dec`,item.limit_time FROM `order` JOIN item ON `order`.item_id = item.iid) AS a JOIN users ON users.uid=a.`owner` where oid = ".$order_res['oid']));
                $order_reses[$key]['due_time']=date("Y-m-d H:i:s",strtotime("+".$order_res['limit_time']." day",strtotime(@$order_res['rent_time'])));
            }
        }
        $this->orders=$order_reses;
    }
}
