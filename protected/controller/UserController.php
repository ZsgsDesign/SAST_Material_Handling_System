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

        $order=new Model('`order`');
        $order_res=$order->query("SELECT a.*,users.real_name,users.uid,renter.real_name AS renter_real_name FROM (SELECT `order`.*,item.`name`,item.iid,item.`owner` FROM `order` JOIN item ON item.iid = `order`.item_id) as a JOIN users ON users.uid=a.`owner` JOIN users as renter ON renter.uid=a.renter_id WHERE a.renter_id = ".$this->userinfo['uid']." OR a.`owner` = ".$this->userinfo['uid']." ORDER BY a.oid DESC");
        $this->orders=$order_res;
        $order_rent_res=$order->query("SELECT `order`.oid,`order`.scode,`order`.renter_id FROM `order` WHERE renter_id = ".$this->userinfo['uid']);
        $order_rent_res_scode=array_count_values(array_column($order_rent_res,'scode'));
        $this->rent_count=[
            "scode1" => empty(@$order_rent_res_scode['1'])?0:$order_rent_res_scode['1'],
            "scode2" => empty(@$order_rent_res_scode['2'])?0:$order_rent_res_scode['2'],
            "scode3" => empty(@$order_rent_res_scode['3'])?0:$order_rent_res_scode['3']
        ];

        $order_owner_res=$order->query("SELECT `order`.oid,`order`.scode,`order`.item_id,item.iid,item.`owner` FROM `order` JOIN item ON item.iid = `order`.item_id WHERE item.`owner` = ".$this->userinfo['uid']);
        $order_owner_res_scode=array_count_values(array_column($order_owner_res,'scode'));
        $this->owner_count=[
            "scode1" => empty(@$order_owner_res_scode['1'])?0:$order_owner_res_scode['1'],
            "scode2" => empty(@$order_owner_res_scode['2'])?0:$order_owner_res_scode['2'],
            "scode3" => empty(@$order_owner_res_scode['3'])?0:$order_owner_res_scode['3']
        ];
        
        $typeA=array();
        $typeB=array();
        $owner_res=$order->query("SELECT `order`.oid,`order`.owner_checked,`order`.item_id,item.`owner`,item.iid FROM `order` JOIN item ON item.iid = `order`.item_id WHERE item.`owner` = ".$this->userinfo['uid']);
        $renter_res=$order->query("SELECT `order`.oid,`order`.renter_id,`order`.renter_checked FROM `order` WHERE `order`.renter_id = ".$this->userinfo['uid']);
        foreach($owner_res as $seq => $value){
            if($value['owner_checked'] == 1||$value['owner_checked'] == 2||$value['owner_checked'] == 3||$value['owner_checked'] == 5){
                array_push($typeA,$value['oid']);
            }
            else if($value['owner_checked'] == 6){
                array_push($typeB,$value['oid']);
            }
        };
        foreach($renter_res as $seq => $value){
            if($value['renter_checked'] == 1||$value['renter_checked'] == 2||$value['renter_checked'] == 3||$value['renter_checked'] == 5){
                array_push($typeA,$value['oid']);
            }
            else if($value['renter_checked'] == 6){
                array_push($typeB,$value['oid']);
            }
        };
        $this->typeA=$typeA;
        $this->typeB=$typeB;
    }
}