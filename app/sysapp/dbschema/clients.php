<?php
/**
* ShopEx licence
*
* @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
* @license  http://ecos.shopex.cn/ ShopEx License
*/

return  array(
    'columns' => array(
        'client_id' => array(
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'comment' => app::get('sysapp')->_('终端客户端ID'),
        ),
        'plugin' => array(
            'type' => array(
                'igexin' => '个推',
                'mipush' => '小米推送',
            ),
            'comment' => app::get('sysapp')->_('插件'),
            'label' => app::get('sysapp')->_('插件'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'type'=>array(
            'type' => array(
                'ios' => '苹果IOS设备',
                'android' => '安卓设备',
            ),
            'comment' => app::get('sysapp')->_('终端平台'),
            'label' => app::get('sysapp')->_('终端平台'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'clientid' => array(
            'type' => 'string',
            'length' => 64,
            'default' => '',
            'label' => app::get('sysapp')->_('终端客户端ID(推送消息用)'),
            'comment' => app::get('sysapp')->_('终端客户端ID(推送消息用)'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'token' => array(
            'type' => 'string',
            'length' => 128,
            'default' => '',
            'label' => app::get('sysapp')->_('终端客户推送消息签名'),
            'comment' => app::get('sysapp')->_('终端客户推送消息签名'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'user_id' => array(
            'type' => 'number',
            'comment' => app::get('sysapp')->_('会员id, 记录这个app谁在登录'),
        ),
    ),
    'primary' => 'client_id',
    'index' => array(
        'ind_clientid' => array(
            'columns' => array('clientid'),
            'prefix' => 'unique',
        ),
    ),
    'comment' => app::get('sysapp')->_('app安装记录表'),
);

