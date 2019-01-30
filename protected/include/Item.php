<?php
/**
 * Created by PhpStorm.
 * User: kouk0
 * Date: 2019/1/30
 * Time: 13:49
 */

/*
 * 根据iid判断物品是否为本人的物品
 */
function IsMyItem($iid)
{
    $userinfo = getuserinfo(@$_SESSION['OPENID']);
    $items = new Model("item");
    $item_res=$items->find(array(
        "iid=:iid",
        ":iid" => $iid,
    ));
    return $item_res['owner'] == $userinfo['uid'];
}