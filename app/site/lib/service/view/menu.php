<?php


class site_service_view_menu {
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

    function function_menu(){
        $html = array();
        $shopUrl = url::action('topc_ctl_default@index');
        $shopWapUrl = url::action('topwap_ctl_default@index');
        $shopManagementUrl = url::action('topshop_ctl_index@index');
        if (config::get('app.debug') && kernel::single('desktop_user')->is_super() )
        {
            $apitestUrl = url::route('topdev.index');
            $html[] = "<a href='$apitestUrl' target='_blank'>API测试工具</a>";
        }
        $html[] = "<a href='$shopUrl' target='_blank'>浏览商城</a>";
        $html[] = "<a href='$shopWapUrl' target='_blank'>手机商城</a>";
        $html[] = "<a href='$shopManagementUrl' target='_blank'>商家后台</a>";
        return $html;

    }
}
