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
        'user_id' =>
        array (
            'type' => 'number',
            'required' => true,
            'label' => app::get('sysuser')->_('会员用户名'),
        ),
        'crm_user_id' =>
        array (
            'type' => 'number',
            'required' => true,
            'label' => app::get('sysuser')->_('crm上的会员id'),
            'order' => 40,
        ),
    ),

    'primary' => 'user_id',
    'index' => array(
        'ind_crm_user_id' => ['columns' => ['crm_user_id']],
    ),

    'comment' => app::get('sysuser')->_('商店会员与crm_user_id绑定关系'),
);
