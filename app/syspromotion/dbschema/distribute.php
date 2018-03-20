<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    'columns' => array(
        'distribute_id' => array(
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('优惠发放活动ID'),
            'comment' => app::get('syspromotion')->_('优惠发放活动ID'),
        ),

        'distribute_name' => array(
            'type' => 'string',
            'length' => 100,
            'required' => true,
            'default' => '',
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault' => true,
            'is_title' => true,
            'label' => app::get('syspromotion')->_('优惠发放活动名称'),
            'comment' => app::get('syspromotion')->_('优惠发放活动名称'),
        ),

        'user_filter' => array(
            'type' =>'serialize',
            'default' => 0,
            'required' => true,
            'label'   => app::get('syspromotion')->_('目标会员过滤器'),
            'comment' => app::get('syspromotion')->_('目标会员过滤器'),
            'width' => 100,
        ),

        'discount_type' => array(
            'type' =>array(
                'hongbao' => app::get('syspromotion')->_('红包'),
                'voucher'  => app::get('syspromotion')->_('购物券'),
            ),
            'required' => true,
            'label'   => app::get('syspromotion')->_('优惠发放类型'),
            'comment' => app::get('syspromotion')->_('优惠发放类型'),
        ),

        'discount_param' => array(
            'type' =>'serialize',
            'label'   => app::get('syspromotion')->_('优惠发放的详细参数'),
            'comment' => app::get('syspromotion')->_('优惠发放的详细参数'),
            'width' => 100,
        ),

        'remind_way' => array(
            'type' => array(
                'both' => app::get('syspromotion')->_('短信和邮件'),
                'sms' => app::get('syspromotion')->_('手机'),
                'email' => app::get('syspromotion')->_('邮件'),
                'none' => app::get('syspromotion')->_('不提醒'),
            ),
            'default' => 'none',
            'required' => true,
            'comment' => app::get('syspromotion')->_('开售提醒'),
            'label' => app::get('syspromotion')->_('开售提醒'),
        ),

        'sms_tmpl'    => array(
            'type'    => 'text',
            'default' => '',
            'label'   => app::get('syspromotion')->_('短信模板'),
            'comment' => app::get('syspromotion')->_('短信模板'),
        ),

        'email_tmpl'  => array(
            'type'    => 'text',
            'default' => '',
            'label'   => app::get('syspromotion')->_('邮件模板'),
            'comment' => app::get('syspromotion')->_('邮件模板'),
        ),

        'status' => array( //这个字段我预留一下，以后用来监控执行状态的
            'type' => array(
                'created' => app::get('syspromotion')->_('创建完成'),
                'running' => app::get('syspromotion')->_('执行中'),
                'exception' => app::get('syspromotion')->_('异常'),
                'complete' => app::get('syspromotion')->_('执行结束'),
            ),
            'default' => 'created',
            'in_list' => false,
            'default_in_list' => false,
            'width' => '100',
            'order' => 15,
            'label' => app::get('syspromotion')->_('状态'),
            'comment' => app::get('syspromotion')->_('状态'),
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

    ),
    'primary' => 'distribute_id',
    'comment' => app::get('syspromotion')->_('优惠定向发放表'),
);

