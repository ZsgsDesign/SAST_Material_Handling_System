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

        $item = new model('item');
        $this->items_info = $item->findAll(array("owner=:uid",":uid" => $uid));
        //dump($this->items_info);
    }
}
