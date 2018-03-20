<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' =>
    array (
        'cat_id' =>
        array(
            'type' => 'table:cat@syscategory',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('sysaftersales')->_('三级类目id'),
        ),
        'cat_name' => array(
            //'type' => 'varchar(100)',
            'type' => 'string',
            'length' => 100,
            'required' => true,
            'is_title' => true,
            'default' => '',
            'label' => app::get('syscategory')->_('分类名称'),
            'width' => 110,
            'searchtype' => 'has',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'refund_days' =>
        array(
            'type' => 'integer',
            'required' => true,
            'default' => -1,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('sysaftersales')->_('退货期限'),
            'comment' => app::get('sysaftersales')->_('退货期限'),
        ),
        'changing_days' =>
        array(
            'type' => 'integer',
            'in_list' => true,
            'required' => true,
            'default' => -1,
            'default_in_list' => true,
            'label' => app::get('sysaftersales')->_('换货期限'),
            'comment' => app::get('sysaftersales')->_('换货期限'),
        ),
    ),
    'primary' => 'cat_id',
    'index' => array(
        'ind_cat_id' => ['columns' => ['cat_id']],
    ),
    'comment' => app::get('sysaftersales')->_('特殊品类售后设置表'),
);
