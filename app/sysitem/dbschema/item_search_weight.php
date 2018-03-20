<?php
return  array(
    'columns'=> array(
        'item_id' => array(
            'type' => 'table:item',
            'required' => true,
            'comment' => app::get('sysitem')->_('商品id'),
        ),
        'default_weight' => array(
            'type' => 'number',
            'default' => 0,
            'label' => app::get('sysitem')->_('综合权重'),
            'comment' => app::get('sysitem')->_('综合权重'),
            'in_list' => true,
            'default_in_list' => false,
        ),
    ),
    'primary' => 'item_id',
    'comment' => app::get('sysitem')->_('商品搜索权重表'),
);

