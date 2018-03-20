<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysapp_push_util{

    public function getPushPlugin()
    {
        $plugin = $this->__getPlugin();
        return $this->__genObject($plugin);
    }

    public function getPlugin(){
        return $this->__getPlugin();
    }

    private function __getPlugin(){
        return 'igexin';
    }

    private function __genObject($type)
    {
        $className = $this->__getClassName($type);
        return new $className;
    }

    private function __getClassName($type){
        $className = [
            'igexin' => 'sysapp_push_plugin_igexin',
        ];
        return $className[$type];
    }

}

