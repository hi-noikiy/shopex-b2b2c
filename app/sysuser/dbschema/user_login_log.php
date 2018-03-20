<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' => array (
        'log_id' => array (
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'editable' => false,
            'label' => app::get('sysuser')->_('会员登录日志ID'),
        ),
        'user_id' => array(
            'type' => 'table:user',
            'required' =>true,
            'searchtype' => 'has',
            'filtertype' => 'yes',
            'filterdefault' => true,
            'label' => app::get('sysuser')->_('会员id'),
            'comment' => app::get('sysuser')->_('会员id'),
        ),
        'user_name' => array(
            'type' => 'string',
            'label' => app::get('sysuser')->_('登录账号'),
            'comment' => app::get('sysuser')->_('登录使用的用户名(手机、邮箱、用户名)'),
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'yes',
            'filterdefault' => true,
        ),
        'login_way' => array(
            'type' => 'string',
            'label' => app::get('sysuser')->_('登录方式'),
            'comment' => app::get('sysuser')->_('哪种信任登录方式(微信、qq、微博等)'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'login_time' => array(
            'type' => 'time',
            'required' =>true,
            'label' => app::get('sysuser')->_('登录时间'),
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'yes',
            'filterdefault' => true,
        ),
        'login_ip' => array(
            'type' => 'string',
            'required' =>true,
            'label' => app::get('sysuser')->_('登录使用ip'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'login_platform' => array(
            'type' => 'string',
            'required' =>true,
            'label' => app::get('sysuser')->_('登录平台'),
            'in_list' => true,
            'default_in_list' => true,
        )
    ),

    'primary' => 'log_id',
    'index' => array(
        'ind_user_id' => ['columns' => ['user_id']],
    ),

    'comment' => app::get('sysuser')->_('会员登录日志表'),
);
