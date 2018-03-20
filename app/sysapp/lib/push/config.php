<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


//TODO 以后这个类要重写，写成至少是可以自动扩展那种，现在这样的有点重。。。。
class sysapp_push_config{

    private $__prefix = 'sysapp.message.push.plugin.';

    private function __getKey($plugin){
        return $this->__prefix . $plugin . '.develop.config';
    }

    public function setConfig($plugin, $config)
    {
        $storeKey = $this->__getKey();
        $this->__save($storeKey, $config);
        return true;
    }

    public function getConfig($plugin, $key = null)
    {
        $storeKey = $this->__getKey();
        $config = $this->__read($storeKey);
        if($key)
            return $config[$key];
        return $config;
    }

    private function __save($key, $data)
    {
        return app::get('sysapp')->setConf($key, $data);
    }

    private function __read($key)
    {
        return app::get('sysapp')->getConf($key);
    }
}

