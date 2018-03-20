<?php

/**
 * ShopEx LuckyMall
 *
 * @author     ajx
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return  array(
    'columns' => array(
        'subsidy_no' => array(
            'type' => 'bigint',
            'unsigned' => true,
            'required' => true,
            'in_list' => true,
            'is_title' => true,
            'filterdefault' => true,
            'default_in_list' => true,
            'label' => app::get('sysclearing')->_('账单补贴编号'),
            'width' => '15',
        ),
        'voucher_id' => array(
            'type' => 'table:voucher@syspromotion',
            'required' => true,
            'comment' => app::get('sysclearing')->_('购物券id'),
            'label' => app::get('sysclearing')->_('购物券名称'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'shop_id' => array(
            'type' => 'table:shop@sysshop',
            'label' => app::get('sysclearing')->_('所属商家'),
            'width' => 110,
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'tradecount' => array(
            'type' => 'number',
            'default' => '0',
            'label' => app::get('sysclearing')->_('订单数量'),//子订单
            'required' => true,
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'subsidy_fee' => array(
            'type' => 'money',
            'default'=>0,
            'label' => app::get('sysclearing')->_('补贴金额'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'status' => array(
            'type' => array(
                '1'=>'未补贴',
                '2'=>'已补贴',
            ),
            'default' => '1',
            'label' => app::get('sysclearing')->_('补贴状态'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'account_start_time' => array(
            'type' => 'time',
            'label' => app::get('sysclearing')->_('账单开始时间'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'account_end_time' => array(
            'type' => 'time',
            'label' => app::get('sysclearing')->_('账单结束时间'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'subsidy_time' => array(
            'type' => 'time',
            'label' => app::get('sysclearing')->_('补贴时间'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
    ),

    'primary' => 'subsidy_no',
    'index' => array(
        'ind_shop_id' => ['columns' => ['shop_id']],
        'ind_subsidy_time' => ['columns' => ['subsidy_time']],
    ),
    'comment' => app::get('sysclearing')->_('平台补贴汇总'),
);

