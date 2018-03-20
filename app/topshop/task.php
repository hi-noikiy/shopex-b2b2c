<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topshop_task{

    public function post_install($dbver)
    {
        app::get('sysconf')->setConf('topshop.firstSetup', 'true');
    }

    public function post_update($dbver){
        if($dbver['dbver'] < 0.2)
        {
            $list = redis::scene('system')->keys('open_newdecorate_status_*');
            if($list){
                foreach ($list as $key) {
                    $shopId = end(explode('_', $key));
                    $value = redis::scene('system')->get(explode(':', $key)[1]);
                    redis::scene('shopDecorate')->hset('wapdecorate_status','shop_'.$shopId,$value);
                    redis::scene('system')->del(explode(':', $key)[1]);
                }
            }
        }
        if($dbver['dbver'] < 0.3){
            $shell = kernel::single('base_shell_webproxy');
            $shell->exec_command('install sysfinance');
        }
    }
}

