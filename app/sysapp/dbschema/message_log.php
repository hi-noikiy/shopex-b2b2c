<?php
/**
* ShopEx licence
*
* @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
* @license  http://ecos.shopex.cn/ ShopEx License
*/

return  array(
    'columns' => array(
        'msg_id' => array(
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'comment' => app::get('sysapp')->_('推送消息记录id'),
        ),
        'plugin' => array(
            'type' => array(
                'igexin' => '个推',
                'mipush' => '小米推送',
            ),
            'comment' => app::get('sysapp')->_('终端类型'),
            'label' => app::get('sysapp')->_('终端平台'),
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'type'=>array(
            'type' => array(
                'all' => '全部设备推送',
                'allwithparams' => '全部设备推送并带参数',
            ),
            'comment' => app::get('sysapp')->_('推送方式'),
            'label' => app::get('sysapp')->_('推送方式'),
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'title' => array(
            'type' => 'text',
            'default' => '',
            'label' => app::get('sysapp')->_('推送消息标题'),
            'comment' => app::get('sysapp')->_('推送消息标题'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'message' => array(
            'type' => 'text',
            'default' => '',
            'label' => app::get('sysapp')->_('推送消息内容'),
            'comment' => app::get('sysapp')->_('推送消息内容'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'client_ids' => array(
            'type' => 'serialize',
            'comment' => app::get('sysapp')->_('接收设备'),
            'label' => app::get('sysapp')->_('接收设备'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'params' => array(
            'type' => 'serialize',
            'comment' => app::get('sysapp')->_('穿透参数'),
            'label' => app::get('sysapp')->_('穿透参数'),
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'primary' => 'msg_id',
    'comment' => app::get('sysapp')->_('app安装记录表'),
);

