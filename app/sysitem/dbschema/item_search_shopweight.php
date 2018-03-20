<?php
return  array(
    'columns'=> array(
        'shop_id' => array(
            'type' => 'number',
            'required' => true,
            'comment' => app::get('sysitem')->_('店铺id'),
        ),
        'custom_weight' => array(
            'type' => 'number',
            'default' => 0,
            'label' => app::get('sysitem')->_('自定义权重'),
            'comment' => app::get('sysitem')->_('自定义权重'),
            'in_list' => true,
            'default_in_list' => false,
        ),
    ),
    'primary' => 'shop_id',
    'comment' => app::get('sysitem')->_('店铺自定义搜索权重表'),
);
