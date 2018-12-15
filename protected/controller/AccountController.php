<?php
class AccountController extends BaseController
{
    private function account_err_report($msg, $current=1)
    {
        $this->current=$current;
        return $this->msg1=$msg;
    }

    public function actionIndex()
    {
        $this->current=0;
        if ($this->islogin) {
            $this->jump("{$this->PB_DOMAIN}/");
        } else {
            $this->title="登录 / 注册";
        }
        $this->url="ucenter";
        $this->msg1=$this->msg2="";
        $action=arg("action");
        if ($action==="register") {

            $db=new Model("users");
            $password=arg("password");
            $email=arg("email");
            $pattern="/^(\w){6,20}$/";
            if (empty($password) || empty($email)) {
                return self::account_err_report("请不要皮这个系统");
            }
            if (!preg_match($pattern, $password)) {
                return self::account_err_report("请设置6位以上100位以下密码，只能包含字母、数字及下划线");
            }

            if (filter_var($email, FILTER_VALIDATE_EMAIL)==false) {
                return self::account_err_report("请输入合法的邮箱");
            }

            $username=strtoupper(explode('@', $email)[0]);
            $SID=$username;
            $email_domain=explode('@', $email)[1];

            if ($email_domain!="njupt.edu.cn") {
                return self::account_err_report("请使用NJUPT校内邮注册");
            }

            $result=$db->find(array("email=:email",":email"=>$email));

            if ($result) {
                return self::account_err_report("邮箱已被使用");
            }

            $ip=getIP();
            $rtime=date("Y-m-d H:i:s");
            $OPENID=sha1(strtolower($email).CONFIG::GET("PB_SALT").md5($password));
            $user=array(
                'rtime'=>$rtime,
                'name'=>$username,
                'real_name'=>$SID,
                'SID'=>$SID,
                'password'=>md5($password),
                'email'=>$email,
                'ip'=>$ip,
                'OPENID'=>$OPENID,
                'avatar'=>"https://static.1cf.co/img/avatar/default.png"
            );
            $uid=$db->create($user);
            $_SESSION['OPENID']=$OPENID;

            $this->jump("{$this->PB_DOMAIN}/");

        //echo json_encode($output);
        } elseif ($action==="login") { //如果是登录

            $email=arg("email");
            $password=arg("password");

            if (empty($password) || empty($email)) {
                return self::account_err_report("请不要皮这个系统", 0);
            }

            $OPENID=sha1(strtolower($email).CONFIG::GET("PB_SALT").md5($password));
            $db=new Model("users");
            $result=$db->find(array("OPENID=:OPENID",":OPENID"=>$OPENID));
            if (empty($result)) {
                return self::account_err_report("邮箱或密码错误", 0);
            } else {
                $_SESSION['OPENID']=$OPENID;
                $this->jump("{$this->PB_DOMAIN}/");
            }
        }
    }

    public function actionLogout()
    {
        session_unset();
        session_destroy();
        $this->jump("{$this->PB_DOMAIN}/");
    }
    
}
