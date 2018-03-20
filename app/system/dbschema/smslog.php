<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    'columns' => array(
        'sms_id' => array(
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'comment' => app::get('system')->_('ID'),
        ),
        'mobiles' => array(
            'type' => 'text',
            'comment' => app::get('system')->_('手机号码'),
            'label' => app::get('system')->_('手机号码'),
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => true,
            'is_title' => true,
            'width' => 200,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'status' => array(
            'type' => array(
                'fail' => app::get('system')->_('失败'),
                'succ' => app::get('system')->_('成功'),
                'progress' => app::get('system')->_('发送中'),
            ),
            'default' => 'progress',
            'required' => true,
            'width' => 40,
            'filtertype' => 'yes',
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('system')->_('发送状态'),
            'label' => app::get('system')->_('发送状态'),
        ),
        'msg' => array(
            'type' => 'string',
            'length' => '255',
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('system')->_('备注'),
            'label' => app::get('system')->_('备注'),
        ),
        'message' => array(
            'type' => 'string',
            'length' => '255',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('system')->_('发送内容'),
            'label' => app::get('system')->_('发送内容'),
        ),
        'send_time' => array(
            'type' => 'time',
            'required' => true,
            'comment' => app::get('system')->_('发送时间'),
            'label' => app::get('system')->_('发送时间'),
            'filtertype' => 'yes',
            'filterdefault' => true,
            'width' => 120,
            'default_in_list' => true,
            'in_list' => true,
            'orderby' => true,
        ),
    ),
    'primary' => 'sms_id',
    'index' => array(
        'ind_send_time' => ['columns' => ['send_time']],
        // 'ind_mobiles' => ['columns' => ['mobiles']],
    ),
    'comment' => app::get('system')->_('短信发送记录'),
);
