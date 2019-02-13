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
    }
}
