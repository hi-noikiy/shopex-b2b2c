<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_enterprise{
	static $enterp;
	static $version;
	static $token;

	/**
	 * 设置版本号
	 * @param string 版本号
	 * @return null
	 */
	static function set_version($version='1.0'){
		self::$version = $version;
	}

	/**
	 * 设置token
	 * @param string token私钥
	 * @return null
	 */
	static function set_token($token='962d93c3702255fc40cbcc6fe766c6a8'){
		self::$token = $token;
	}

	/**
	 * 存储企业帐号和信息
	 * @param mixed - 企业帐号信息
	 * @return boolean true or false
	 */
	static function set_enterprise_info($arr_enterprise){
		app::get('base')->setConf('ecos.enterprise_info', serialize($arr_enterprise));
		return true;
	}

	/**
	 * 获取企业信息
	 * @param string 获取的信息内容
	 * @return string 相应的内容
	 */
	static function get($code='ent_id'){
        if(self::$enterp===null){
			if($serialize_enterp = app::get('base')->getConf('ecos.enterprise_info')){
				$enterprise = unserialize($serialize_enterp);
				self::$enterp = $enterprise;
			}
		}

        return self::$enterp[$code];
    }

	/**
	 * 返回企业号
	 * @param null
	 * @return string
	 */
	static function ent_id() { return self::get('ent_id'); }

	/**
	 * 返回企业密码
	 * @param null
	 * @return string
	 */
	static function ent_ac() { return self::get('ent_ac'); }

	/**
	 * 返回企业登录账号
	 * @param null
	 * @return string
	 */
	static function ent_loginname() { return self::get('ent_loginname'); }
}
