<?php
return  array(
    'columns'=> array(
        'ptmpl_id' => array(
            'type' => 'number',
            'autoincrement' => true,
            'required' => true,
            'comment' => app::get('sysitem')->_('模板id'),
        ),
        'ptmpl_name' => array(
            'type' => 'string',
            'length' => 90,
            'required' => true,
            'default' => '',
            'label' => app::get('sysitem')->_('模板名称'),
            'comment' => app::get('sysitem')->_('模板名称'),
            'in_list' => true,
            'default_in_list' => true,
            'is_title' => true,
            'searchtype' => 'has',
            'filtertype' => 'custom',
            'filterdefault' => true,
        ),
        'content' => array(
            'type' => 'text',
            'comment' => app::get('sysitem')->_('内容'),
            'filtertype' => 'normal',
        ),
        'created_time' => array(
            'type' => 'time',
            'default' => 0,
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('sysitem')->_('创建时间'),
            'comment' => app::get('sysitem')->_('模板创建时间'),
        ),
        'updated_time' => array(
            'type' => 'time',
            'default' => 0,
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('修改时间'),
            'comment' => app::get('syspromotion')->_('最后修改时间'),
        ),
    ),
    
    'primary' => 'ptmpl_id',
    'comment' => app::get('sysitem')->_('app促销页面列表'),
);

