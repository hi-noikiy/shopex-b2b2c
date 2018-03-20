<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysapp_ctl_admin_push_clients extends desktop_controller {

    public function index(){
        return $this->finder('sysapp_mdl_clients',array(
            'title' => app::get('sysapp')->_('设备列表'),
            'use_buildin_filter' => true,
            'use_buildin_delete' => true,
            'actions'=>array(
            )
        ));

    }

}



