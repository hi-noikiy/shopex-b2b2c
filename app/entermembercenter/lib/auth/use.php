<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class entermembercenter_auth_use
{
	public function pre_ceti_use()
	{
		if(!base_certi::certi_id() || !base_certi::token()){
			return false;
		}

		return true;
	}

	public function login_verify()
	{

        $result = prism::check();
        
        if($result) return "";

        $active_url = url::route('shopadmin', ['app' => 'entermembercenter', 'ctl' => 'register']);
		header('Location: '.$active_url);exit;
	}

}
