<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysapp_ctl_admin_push_igexin extends desktop_controller {

    public function config(){

        $pagedata['igexin'] = kernel::single('sysapp_push_config')->getConfig('igexin');
        return view::make('sysapp/admin/push/igexin/config.html',$pagedata);
    }

    public function saveConfig(){
        $this->begin();

        $data = input::get();

        kernel::single('sysapp_push_config')->setConfig('igexin', $data['igexin']);
        return $this->end(true, app::get('sysapp')->_('保存成功'));
    }

}



