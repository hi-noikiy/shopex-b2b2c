<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysapp_push_igexin_util{

    public function getAppId(){
        return kernel::single('sysapp_push_config')->getConfig('igexin', 'appid');
    }

    public function getAppKey(){
        return kernel::single('sysapp_push_config')->getConfig('igexin', 'appkey');
    }

    public function getMasterSecret(){
        return kernel::single('sysapp_push_config')->getConfig('igexin', 'mastersecret');
    }
}

