<?php
return  array(
    'columns'=> array(
        'shop_id' => array(
            'type' => 'string',
            'comment' => app::get('sysopen')->_('店铺ID'),
            'label' => app::get('sysopen')->_('店铺名称'),
            'in_list' => true,
            'default_in_list' => true,
            'width' => '30',
            'order' => 10,
        ),
        'node_id' => array(
            'type' => 'string',
            'label' => app::get('sysopen')->_('店铺节点ID'),
            'in_list' => true,
            'default_in_list' => true,
            'width' => '30',
            'order' => 10,
        ),
        'node_token' => array(
            'type' => 'string',
            'label' => app::get('sysopen')->_('店铺节点token'),
            'in_list' => true,
            'default_in_list' => true,
            'width' => '30',
            'order' => 10,
        ),
        'certi_id' => array(
            'type' => 'string',
            'label' => app::get('sysopen')->_('店铺证书ID'),
            'in_list' => true,
            'default_in_list' => true,
            'width' => '30',
            'order' => 10,
        ),
        'certi_token' => array(
            'type' => 'string',
            'label' => app::get('sysopen')->_('证书token'),
            'in_list' => true,
            'default_in_list' => true,
            'width' => '30',
            'order' => 10,
        ),
        'url' => array(
            'type' => 'string',
            'label' => app::get('sysopen')->_('url'),
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'primary' => 'shop_id',
    'comment' => app::get('sysopen')->_('店铺绑定shopex矩阵信息'),
);

