<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author guocheng
 */
class system_ctl_apiTool extends desktop_controller{
	public function index()
	{
		if (config::get('app.debug') && kernel::single('desktop_user')->is_super() )
        {
            $apitestUrl = url::route('topdev.index');
            echo "<script>window.open('".$apitestUrl."')</script>";exit;
        }
        echo "<script>alert('暂时不支持api测试工具');</script>";exit;
		
	}
}