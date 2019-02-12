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
        $good_count=$user->query("SELECT SUM(gcount) FROM item WHERE owner=:uid", array(":uid"=>$this->userinfo['uid']));
        $mid_count=$user->query("SELECT SUM(mcount) FROM item WHERE owner=:uid", array(":uid"=>$this->userinfo['uid']));
        $bad_count=$user->query("SELECT SUM(bcount) FROM item WHERE owner=:uid", array(":uid"=>$this->userinfo['uid']));
        $br_count=count($user->query("SELECT * FROM `order` WHERE renter_id=:uid", array(":uid"=>$this->userinfo['uid'])));
        $pb_count=count($user->query("SELECT * FROM item WHERE owner=:uid", array(":uid"=>$this->userinfo['uid'])));
        $this->wtf_info = array(
           "good_count" => $good_count[0]["SUM(gcount)"],
           "mid_count" => $mid_count[0]["SUM(mcount)"],
           "bad_count" => $bad_count[0]["SUM(bcount)"],
           "br_count" => $br_count,
           "pb_count" => $pb_count,
        );

        $item = new model('item');
        $this->items_info = $item->findAll(array("owner=:uid",":uid" => $uid),'create_time DESC');
        //dump($this->items_info);
    }
}
