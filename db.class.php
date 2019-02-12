<?php
class db
{

//成员方法   是用来执行sql语句的方法
    public function Query($sql,$type=1)
//两个参数：sql语句，判断返回1查询或是增删改的返回
    {
//造一个连接对象，参数是上面的那四个
        $db = new mysqli($this->host,$this->users,$this->password,$this->dbname);
        $r = $db->query($sql);
            return $r;
    }

}
?>