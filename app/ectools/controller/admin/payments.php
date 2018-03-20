<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class ectools_ctl_admin_payments extends desktop_controller{

    public function index(){
        return $this->finder('ectools_mdl_payments',array(
            'title'=>app::get('ectools')->_('支付单列表'),
            'allow_detail_popup'=>true,
            'use_view_tab'=>true,
            'use_buildin_filter'=>true,
        ));
    }

    public function _views()
    {
        return array(
            0=>array('label'=>app::get('systrade')->_('全部'),'optional'=>false,),
            2=>array('label'=>app::get('systrade')->_('在线支付'),'optional'=>false,'filter'=>array('pay_type'=>'online')),
            3=>array('label'=>app::get('systrade')->_('货到付款'),'optional'=>false,'filter'=>array('pay_type'=>'offline')),
        );
    }

}
