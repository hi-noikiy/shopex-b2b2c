<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    'columns' => array(
        'distribute_detail_id' => array(
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('优惠发放活动ID'),
            'comment' => app::get('syspromotion')->_('优惠发放活动ID'),
        ),

        'distribute_id' => array(
            'type' => 'table:distribute@syspromotion',
            'in_list' => false,
            'comment' => app::get('syspromotion')->_('优惠发放活动ID'),
        ),

        'user_id' => array(
            'type' => 'table:user@sysuser',
            'label' => app::get('syspromotion')->_('优惠发放活动ID'),
            'comment' => app::get('syspromotion')->_('优惠发放活动ID'),
        ),

        'discount_type' => array(
            'type' =>array(
                'hongbao' => app::get('syspromotion')->_('红包'),
                'coupon'  => app::get('syspromotion')->_('优惠券'),
            ),
            'required' => true,
            'label'   => app::get('syspromotion')->_('优惠发放类型'),
            'comment' => app::get('syspromotion')->_('优惠发放类型'),
        ),

        'discount_param' => array(
            'type' =>'serialize',
            'default' => 0,
            'required' => true,
            'label'   => app::get('syspromotion')->_('优惠发放的详细参数'),
            'comment' => app::get('syspromotion')->_('优惠发放的详细参数'),
            'width' => 100,
        ),

        'discount_detail_param' => array(
            'type' =>'serialize',
            'default' => 0,
            'label'   => app::get('syspromotion')->_('优惠发放的详细参数精确到个人信息'),
            'comment' => app::get('syspromotion')->_('优惠发放的详细参数精确到个人信息'),
            'width' => 100,
        ),

        'created_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'order' => 15,
            'label' => app::get('syspromotion')->_('创建时间'),
            'comment' => app::get('syspromotion')->_('创建时间'),
        ),
        'status' => array(
            'type' =>array(
                'fine'       => app::get('syspromotion')->_('一切正常'),
                'exception'  => app::get('syspromotion')->_('存在异常'),
            ),
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'label'   => app::get('syspromotion')->_('状态量'),
            'comment' => app::get('syspromotion')->_('状态量'),
        ),
        'exceptionMsg' => array(
            'type' => 'string',
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('syspromotion')->_('异常信息(只保留最后一条)'),
            'label' => app::get('syspromotion')->_('异常信息(只保留最后一条)'),
        ),
    ),
    'primary' => 'distribute_detail_id',
    'index' => array(
        'ind_distribute_id' => ['columns' => ['distribute_id']],
        'ind_user_id' => ['columns' => ['user_id']],
    ),
    'comment' => app::get('syspromotion')->_('优惠定向发放表'),
);



