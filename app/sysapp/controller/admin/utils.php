<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysapp_ctl_admin_utils extends desktop_controller {

    public function ajax_get_object()
    {
        $linkmapapp = kernel::single('sysapp_module_config')->linkmapapp;// 对应app端页面类型，用于app端判断怎么跳转页面

        $params = input::get();

        if( !$linkmapapp[$params['linktype']]['obj'] )
        {
            return '';
        }

        // $pagedata['name'] = $params['name'];
        // $pagedata['value'] = $params['value'];
        $pagedata['filter'] = http_build_query($params['filter']) ? : $linkmapapp[$params['linktype']]['obj']['filter'] ;
        $pagedata['callback'] = $params['callback'];
        $pagedata['object'] = $params['object'] ? : $linkmapapp[$params['linktype']]['obj']['object'];
        $pagedata['textcol'] = $params['textcol'] ? : $linkmapapp[$params['linktype']]['obj']['textcol'];
        $pagedata['emptytext'] = $params['emptytext'] ? : $linkmapapp[$params['linktype']]['obj']['emptytext'];

        return view::make('sysapp/ui/obj.html', $pagedata);
    }


    public function ajax_get_applink()
    {
        $params = input::get();

        // $pagedata['filter'] = http_build_query($params['filter']);
        $pagedata['name'] = $params['name'];
        $pagedata['value'] = $params['value'];
        $pagedata['linktypename'] = $params['linktypename'];
        $pagedata['linktypevalue'] = $params['linktypevalue'];

        return view::make('sysapp/ui/getapplink.html', $pagedata);
    }

}
