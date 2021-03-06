<?php

class ERR {
    
    /**
     * An old-fashioned error catcher mainly to provide error description
     * existed here only to avoid direct SQL database access
     * return a hundred present pure string
     *
     * @author John Zhang
     * @param string $ERR_CODE
     */

    public static function Catcher($ERR_CODE)
    {
        if(($ERR_CODE<1000)) $ERR_CODE=1000;
        $output=array(
             'ret' => $ERR_CODE,
            'desc' => self::Desc($ERR_CODE),
            'data' => null
        );
        exit(json_encode($output));
    }
     
    private static function Desc($ERR_CODE)
    {
        $ERR_DESC=array(
            
            '1000' => "Unspecified Error",  /** Under normal condictions those errors shouldn't displayed to end users unless they attempt to do so
                                             *  some submissions should be intercepted by the frontend before the request sended 
                                             */
            '1001' => "Internal Sever Error : SECURE_VALUE 非法",
            '1002' => "内部服务器错误：操作失败",
            '1003' => "内部服务器错误：参数不全",
            '1004' => "内部服务器错误：参数非法",
            '1005' => "内部服务器错误：文件类型不被支持",
            '1006' => "内部服务器错误：输入过长",

            '2000' => "Account-Related Error",

            '2001' => "请先登录",
            '2002' => "未找到该用户",
            '2003' => "您的权限不足",
            '2004' => "用户名或密码错误",
            '2005' => "用户重复授权",
            '2006' => "无法撤销自己授权",
            '2007' => "激活邮件发送过于频繁",
            '2008' => "请不要皮这个系统",
            '2009' => "密码错误",
            '2010' => "请设置6位以上100位以下密码，只能包含字母、数字及下划线",

            '3000' => "sastMHS-Related Error",

            '3005' => "乔波",  // Reserved for Copper in memory of OASIS and those who contributed a lot

            '100001' => "请填写物品名称",
            '100002' => "找不到该物品",
            '100003' => "物品已下架",
            '100004' => "请填写物品位置",
            '100005' => "请填写物品描述",
            '100006' => "请填写物品数量",
            '100007' => "物品已上架",

            '200001' => "不受支持的图片格式",
            '200002' => "图片过大（大于1MB）",
            '200003' => "非法的图片操作",

        );
        return isset($ERR_DESC[$ERR_CODE])?$ERR_DESC[$ERR_CODE]:$ERR_DESC['1000'];
    }

}
