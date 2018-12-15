# SAST_Material_Handling_System
Material Handling System for SAST

## Install
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

			"MHS_CDN"=>"https://static.1cf.co",
			"MHS_DOMAIN"=>"",
			"MHS_SALT"=>""
		);
		return $config[$KEY];
	}
	

}

```

The type in the configuration of your mysql server to this file.

**NOTICE :** Normally, you only need to set fields with DEBUG.