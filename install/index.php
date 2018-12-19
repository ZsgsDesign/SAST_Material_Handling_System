<?php

    /**
     * This is a install script
     */

    if (file_exists(".installed")) {
        Header("Location: ../"); //Successfully Installed
    }

    try {
        $_sql = file_get_contents('sastmhs.lite.sql');
        $_arr = explode(';', $_sql);
        class db
        {
            public $host='localhost';
            public $username='root';
            public $password='root';
        }
        $db;
        $db=new db();
        $dsn = "mysql:host=".$db->host.";charset=utf8";
        $db = new PDO($dsn, $db->username, $db->password);
        foreach ($_arr as $_value) {
            $db->query($_value.';');
        }
    } catch (Exception $e) {
        exit("请检查服务器用户名密码是否正确");
    }
    
    try {
        $config=file_get_contents("config.bak");
        file_put_contents("../protected/model/CONFIG.php", $config);
    } catch (Exception $e) {
        exit("请检查服务器权限是否正确");
    }
    
    file_put_contents(".installed", "don't dare me.");
    exit("安装成功，如果需要修改请手动更改CONFIG.php");