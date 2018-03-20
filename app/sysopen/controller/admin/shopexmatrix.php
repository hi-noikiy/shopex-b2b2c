<?php

class sysopen_ctl_admin_shopexmatrix extends desktop_controller {

    public function index()
    {
        $pagedata['shopexmatrix_enable'] = app::get('sysopen')->getConf('shopexmatrix.enable');
        $shopexNod = sysopen_shopexnode::getNodeInfo();
        $pagedata['node_id'] = $shopexNod['node_id'];

        //如果开启矩阵并且没有shopexNodeId
        //开启node_id
        if( input::get('shopexmatrix_enable') && !$shopexNodeId )
        {
            $flag = sysopen_shopexnode::register();
        }

        if( $flag && input::has('shopexmatrix_enable') )
        {
            app::get('sysopen')->setConf('shopexmatrix.enable', input::get('shopexmatrix_enable'));
            $pagedata['shopexmatrix_enable'] = input::get('shopexmatrix_enable');
        }

        return $this->page('sysopen/shopexmatrix/config.html', $pagedata);
    }

    //商派产品绑定表，OMS CRM
    public function shopexProduct()
    {
        return $this->finder('sysopen_mdl_shopexProduct',array(
            'use_buildin_filter'=>false,
            'use_view_tab'=>true,
            'title' => app::get('systrade')->_('Shopex产品绑定列表'),
            'use_buildin_delete'=>false,
        ));
    }


}
