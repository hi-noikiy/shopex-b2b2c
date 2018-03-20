<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 class sysfinance_ctl_admin_guaranteelog extends desktop_controller
 {

    /**
     *@brief 保证金操作日志
     *
     *@param $shop_id
     *
     *@return
     *
     */
    public function index()
    {
        $shopId = intval(input::get('shop_id'));

        return $this->finder('sysfinance_mdl_guaranteeMoney_oplog',array(
            'title' => app::get('sysfinance')->_('保证金交易流水'),
            'use_buildin_delete' => false,
            'use_buildin_filter' => true,
            'use_buildin_export' => true,
        ));
    }

    public function _views()
    {
        $sub_menu = array(
            1=>array(
                'label' => app::get('sysfinance')->_('全部'),
                'optional' => true,
                'filter' => array(
                ),
            ),
            2=>array(
                'label' => app::get('sysfinance')->_('充值'),
                'optional' => false,
                'filter' => array(
                    'op_type' => 'recharge',
                ),
            ),
            3=>array(
                'label' => app::get('sysfinance')->_('扣款'),
                'optional' => false,
                'filter' => array(
                    'op_type' => 'expense',
                ),
            ),
        );

        return $sub_menu;
    }
 }