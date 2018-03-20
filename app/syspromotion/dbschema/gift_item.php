<?php
return array(
    'columns' => array(
        'gift_id' => array(
            'type' => 'number',
            'required' => true,
            'comment' => app::get('syspromotion')->_('赠品方案id'),
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
        'title' => array(
            'type' => 'string',
            'length' => 90,
            'required' => true,
            'comment' => app::get('syspromotion')->_('商品名称'),
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
       'promotion_tag' => array(
            'type' => 'string',
            'length' => 10,
            'default' => 'gift',
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
            'default' => '1',
            'required' => true,
            'label' => app::get('syspromotion')->_('是否生效中'),
            'comment' => app::get('syspromotion')->_('是否生效中'),
        ),
    ),
    'primary' => ['gift_id', 'item_id'],
    'index' => array(
        'ind_cat_id' => ['columns' => ['leaf_cat_id']],
    ),
    'comment' => app::get('syspromotion')->_('商品与促销规则赠品关联表'),

);
