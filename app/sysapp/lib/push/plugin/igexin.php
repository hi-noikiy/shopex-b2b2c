<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sysapp_push_plugin_igexin implements sysapp_push_interface{

    //向所有的用户发送消息
    public function pushAll($title, $text){
        return kernel::single('sysapp_push_igexin_object')->pushAll($title, $text);
    }

    public function pushAllWithParams($title, $text, $params)
    {
        return kernel::single('sysapp_push_igexin_object')->pushAllWithParams($title, $text, $params);
    }

}

