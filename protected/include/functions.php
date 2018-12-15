<?php

function getIP()
{
    if (@$_SERVER["HTTP_X_FORWARDED_FOR"]) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif (@$_SERVER["HTTP_CLIENT_IP"]) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (@$_SERVER["REMOTE_ADDR"]) {
        $ip = $_SERVER["REMOTE_ADDR"];
    } elseif (@getenv("HTTP_X_FORWARDED_FOR")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } elseif (@getenv("HTTP_CLIENT_IP")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } elseif (@getenv("REMOTE_ADDR")) {
        $ip = getenv("REMOTE_ADDR");
    } else {
        $ip = "Unknown";
    }
    return $ip;
}

function is_login()
{
    $is_login=1;
    if ($OPENID=arg("OPENID")) {
        $_SESSION['loginid']=$OPENID;
        $is_login=validateOPENID($OPENID, "app");
    } elseif (!@$_SESSION['OPENID']) {
        $is_login=0;
    } else {
        $is_login=validateOPENID($_SESSION['OPENID'], "browser");
    }
    return $is_login;
}

function validateOPENID($OPENID, $mode='browser')
{
    $user_db=new Model("users");
    $result=$user_db->find(array("OPENID = :OPENID",":OPENID" => $OPENID));
    if (empty($result)) {
        if ($mode=="app") {
            $output=array(
                'status'=>0,
                'info'=>'invalid OPENID'
            );
            echo json_encode($output);
            exit;
        } else {
            session_unset();
            session_destroy();
            return 0;
        }
    } else {
        $_SESSION['uid']=$result['uid'];
        return 1;
    }
}

function getuserinfo($OPENID)
{
    $user_db=new Model("users");
    return $user_db->find(array("OPENID = :OPENID",":OPENID" => $OPENID));
}

function generateRandStr($len)
{
    $code = '';
    for ($i = 0; $i < $len; $i++) {
        $code = $code . substr(str_shuffle('0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM'), 0, 1);
    }
    return $code;
}