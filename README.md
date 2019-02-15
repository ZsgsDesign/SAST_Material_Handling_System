# SAST_Material_Handling_System
Material Handling System for SAST

## Install Automatically

just access `install/index.php`.

## Install Manully
Create a new php file named CONFIG.php in /protected/model/ and insert:
```
<?php

class CONFIG {
	
	/**
	 * CONFIG
	 *
	 * @author John Zhang
	 * @param string $KEY
	 */

	public static function GET($KEY)
	{
		$config=array(
			"MHS_DEBUG_MYSQL_HOST"=>"",
			"MHS_DEBUG_MYSQL_PORT"=>"",
			"MHS_DEBUG_MYSQL_USER"=>"",
			"MHS_DEBUG_MYSQL_DATABASE"=>"",
			"MHS_DEBUG_MYSQL_PASSWORD"=>"",

			"MHS_MYSQL_HOST"=>"",
			"MHS_MYSQL_PORT"=>"",
			"MHS_MYSQL_USER"=>"",
			"MHS_MYSQL_DATABASE"=>"",
			"MHS_MYSQL_PASSWORD"=>"",

			"MHS_CDN"=>"https://cdn.mundb.xyz",
			"MHS_DOMAIN"=>"",
			"MHS_SALT"=>""，

			"MHS_PIC_SERVICE_ROOT" =>  realpath(dirname(__FILE__).'/../../').'/pics/' //图片存放目录
		);
		return $config[$KEY];
	}
	

}

```

The type in the configuration of your mysql server to this file. Next you need to import the `sastmhs.lite.sql` to your database.

**NOTICE :** Normally, you only need to set fields with DEBUG.

## NOTICE

For better reference, all materials below would be written in Chinese.

# SAST物品借还系统

这是SAST物品借还系统的独立源代码，为ATSAST源代码的一部分，本系统代码将会被整合于ATSAST，但将作为单独部分开源并可独立使用。

## 基本功能

预期实现的基本功能包括但不限于：

1. 首页
    1. 展示所有可借物品；
    1. 提供物品搜索功能；
    1. 提供基于最新发布、最多借用、最受好评、信用优先的排序功能；
    1. 提供筛选功能
1. 一键发布
    1. 照片的上传与裁剪；
    1. 物品名称、限时时长、剩余件数等的填写与上传；
1. 借还记录
    1. 展示所有历史订单的状态；
1. 个人中心
    1. 展示信用分等信息；
    1. 可供其他人查看；

## 额外要求

+ 开发过程中，开发团队应该完成开发日志；
+ 编写项目文档（介绍各个功能）与接口文档（如果有）

## 推荐

后端框架：`FlashPHP`

前端库：`Bootstrap Material Design`

数据库：`MySQL`

基本语言：`PHP`、`JS`、`CSS`

## 其他注意事项

因项目为ATSAST源码一部分，项目协议转为AGPL。