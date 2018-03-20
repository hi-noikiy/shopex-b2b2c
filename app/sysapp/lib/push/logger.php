<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sysapp_push_logger
{

    public function add($title, $message, $type, $client_ids, $params)
    {
        $plugin = kernel::single('sysapp_push_util')->getPlugin();

        $log = [
            'plugin' => $plugin,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'client_ids' => $client_ids,
            'params' => $params,
        ];

        return app::get('sysapp')->model('message_log')->save($log);
    }

}

