<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_wap_download_app(&$setting){

    $data = app::get('sysapp')->getConf('app.download.boot.setting');
    $setting = unserialize($data);
    return $setting;
}
?>
