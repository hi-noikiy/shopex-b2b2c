<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */

//商品与促销规则关联表
return  array(
    'columns' => array(
        'fulldiscount_id' => array(
            'type' => 'number',
            'required' => true,
            'comment' => app::get('syspromotion')->_('满折ID'),
        ),
        'item_id' => array(
            'type' => 'number',
            'required' => true,
            'comment' => app::get('syspromotion')->_('商品ID'),
        ),
        'sku_ids' => array(
            'type' => 'string',
            'comment' => app::get('syspromotion')->_('逗号隔开的参加促销的SKU_ID集合'),
        ),
        'shop_id' => array(
            'type' => 'number',
            'required' => true,
            'label' => app::get('syspromotion')->_('所属商家'),
            'comment' => app::get('syspromotion')->_('所属商家的店铺id'),
        ),
        'leaf_cat_id' => array(
            'type' => 'number',
            'required' => true,
            'comment' => app::get('syspromotion')->_('商品关联的平台叶子节点分类ID'),
        ),
        'title' => array(
            'type' => 'string',
            'length' => 90,
            'required' => true,
            'comment' => app::get('syspromotion')->_('商品名称'),
        ),
        'image_default_id' => array(
            'type' => 'string',
            'length' => 250,
            'comment' => app::get('syspromotion')->_('商品图片'),
        ),
        'price' => array(
            'type' => 'money',
            'required' => true,
            'label' => app::get('syspromotion')->_('商品价格'),
            'comment' => app::get('syspromotion')->_('商品价格'),
        ),
        'promotion_tag' => array(
            'type' => 'string',
            'length' => 10,
            'default' => 0,
            'required' => true,
            'label' => app::get('syspromotion')->_('促销标签'),
        ),
        'start_time' => array(
            'type' => 'time',
            'default'=> 0,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'label' => app::get('syspromotion')->_('起始时间'),
            'comment' => app::get('syspromotion')->_('起始时间'),
        ),
        'end_time' => array(
            'type' => 'time',
            'default'=> 0,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => false,
            'filterdefault'=>true,
            'label' => app::get('syspromotion')->_('截止时间'),
            'comment' => app::get('syspromotion')->_('截止时间'),
        ),
        'status' => array(
            'type' => 'bool',
            'default' => '0',
            'required' => true,
            'label' => app::get('syspromotion')->_('是否生效中'),
            'comment' => app::get('syspromotion')->_('是否生效中'),
        ),
    ),
    'primary' => ['fulldiscount_id', 'item_id'],
    'index' => array(
        'ind_shop_id' => ['columns' => ['shop_id']],
        'ind_leaf_cat_id' => ['columns' => ['leaf_cat_id']],
    ),
    'comment' => app::get('syspromotion')->_('商品与促销规则关联表'),
);
