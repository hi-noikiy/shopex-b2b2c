<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
* @table member_voucher;
*
* @package Schemas
* @version $
* @copyright 2010 ShopEx
* @license Commercial
*/

return  array(
    'columns' => array(
        'voucher_code' => array(
            //'type' => 'varchar(32)',
            'type' => 'string',
            'length' => 32,
            'required' => true,
            //'pkey' => true,
            'label' => app::get('sysuser')->_('购物券号码'),
            'comment' => app::get('sysuser')->_('购物券号码'),
        ),
        'user_id' => array(
            'type' => 'number',
            'required' => true,
            //'pkey'=>true,
            'label' => app::get('sysuser')->_('会员ID'),
            'comment' => app::get('sysuser')->_('会员ID'),
        ),
        'voucher_id' => array(
            'type' => 'number',
            'required' => true,
            'default' => 0,
            'label' => app::get('sysuser')->_('会员购物券ID'),
            'comment' => app::get('sysuser')->_('会员购物券ID'),
        ),
        'voucher_name' => array(
            'type' => 'string',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 110,
            'label' => app::get('syspromotion')->_('购物券名称'),
            'comment' => app::get('syspromotion')->_('购物券名称'),
        ),
        'obtain_desc' => array(
            //'type' => 'varchar(255)',
            'type' => 'string',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 110,
            'label' => app::get('sysuser')->_('领取方式'),
            'comment' => app::get('sysuser')->_('领取方式'),
        ),
        'obtain_time' => array(
            'type' => 'time',
            'label' => app::get('sysuser')->_('购物券获得时间'),
            'comment' => app::get('sysuser')->_('购物券获得时间'),
        ),
        'tid' => array(
            //'type' => 'bigint unsigned',
            'type' => 'string',
            'unsigned' => true,
            'comment' => app::get('sysuser')->_('使用该购物券的订单号'),
        ),
        'is_valid' => array(
            'type' => array(
                '0' => app::get('sysuser')->_('已使用'),
                '1' => app::get('sysuser')->_('有效'),
                '2' => app::get('sysuser')->_('过期'),
            ),
            'default' => 1,
            'required' => true,
            'editable' => false,
            'label' => app::get('sysuser')->_('会员购物券是否当前可用'),
            'comment' => app::get('sysuser')->_('会员购物券是否当前可用'),
        ),
        'limit_cat' => array(
            'type' => 'string',
            'default' => '',
            'required' => true,
            'label' => app::get('syspromotion')->_('支持商品类目'),//逗号隔开的字符串
        ),
        'subsidy_proportion' => array(
            'type' => 'string',
            'default' => '',
            'required' => true,
            'comment' => app::get('syspromotion')->_('平台补贴比例'),
        ),
        'used_platform' => array(
            'type' => 'string',//pc wap app 多个逗号隔开
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('使用平台'),
            'comment' => app::get('syspromotion')->_('使用平台'),
        ),
        'limit_money' => array(
            'type' => 'money',
            'default' => '0',
            'default_in_list' => true,
            'width' => '50',
            'order' => 14,
            'label' => app::get('sysuser')->_('满足条件金额'),
            'comment' => app::get('sysuser')->_('满足条件金额，冗余字段用于查询'),
        ),
        'deduct_money' => array(
            'type' => 'money',
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '50',
            'order' => 14,
            'label' => app::get('sysuser')->_('优惠金额'),
            'comment' => app::get('sysuser')->_('优惠金额，冗余字段用于查询'),
        ),
        'start_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'label' => app::get('sysuser')->_('购物券生效时间'),
            'comment' => app::get('sysuser')->_('购物券生效时间，冗余字段用于查询'),
        ),
        'end_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'label' => app::get('sysuser')->_('购物券失效时间'),
            'comment' => app::get('sysuser')->_('购物券失效时间，冗余字段用于查询'),
        ),
    ),

    'primary' => ['voucher_code', 'user_id'],
    'index' => array(
        'ind_tid' => ['columns' => ['tid']],
    ),
    'comment' => app::get('sysuser')->_('用户购物券表'),
);
