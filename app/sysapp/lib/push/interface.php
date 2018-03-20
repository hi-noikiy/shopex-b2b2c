<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


interface sysapp_push_interface{
    //向所有的用户发送消息
    public function pushAll($title, $text);
    //向所有的用户发送消息并且带有穿透消息
    public function pushAllWithParams($title, $text, $params);
}


