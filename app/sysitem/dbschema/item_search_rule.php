<?php
return  array(
    'columns'=> array(
        'rule_id' => array(
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'comment' => app::get('sysitem')->_('规则id'),
        ),
        'name' => array(
            'type' => 'string',
            'length' => 20,
            'label' => app::get('sysitem')->_('规则名称'),
            'comment' => app::get('sysitem')->_('规则名称'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'desc' => array(
            'type' => 'string',
            'length' => 50,
            'label' => app::get('sysitem')->_('规则描述'),
            'comment' => app::get('sysitem')->_('规则描述'),
            'in_list' => true,
            'default_in_list' => false,
        ),
        'rule' => array(
            'type' => 'text',
            'label' => app::get('sysitem')->_('权重比例'),
            'comment' => app::get('sysitem')->_('权重比例'),
        ),
        'created_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('sysitem')->_('创建时间'),
            'label' => app::get('sysitem')->_('创建时间'),
        ),
        'modified_time' => array(
            'type' => 'last_modify',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'order' => 18,
            'label' => app::get('systrade')->_('修改时间'),
            'comment' => app::get('systrade')->_('修改时间'),
        ),
    ),
    'primary' => 'rule_id',
    'comment' => app::get('sysitem')->_('商品搜索权重配置表'),
);

