<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sysapp_view_input{

    function input_applink($params){

        $linkmapapp = kernel::single('sysapp_module_config')->linkmapapp;// 对应app端页面类型，用于app端判断怎么跳转页面

        $pagedata['domid'] = view::ui()->new_dom_id();
        $pagedata['value'] = $params['value'];
        $pagedata['name'] = $params['name'];
        $pagedata['linktypename'] = $params['linktypename'];
        $pagedata['linktypevalue'] = $params['linktypevalue'];
        $pagedata['linkmapapp'] = $linkmapapp;

        return view::make('sysapp/ui/applink.html', $pagedata)->render();
    }

}//End Class
