<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sysshop_tasks_closeDemoShop extends base_task_abstract implements base_interface_task
{
    public function exec($params=null)
    {
        $start_time = time() - 30*24*3600;

        $data = [
            'status' => 'dead',
            'close_reason' => app::get('sysshop')->_('系统自动关闭演示用户的站点'),
        ];

        $filter = [
            'shop_type'=> 'self',
            'open_time|sthan' => $start_time,
        ];

        app::get('sysshop')->model('shop')->update($data, $filter);

        return true;
    }
}


