<?php
if(@!file_get_contents("install/.installed")){
    exit("请先安装");
}
define('APP_DIR', realpath('./'));
require(APP_DIR.'/protected/lib/flash.php');
