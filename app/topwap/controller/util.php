<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topwap_ctl_util extends topwap_controller
{
    public function configContent()
    {
        $app = input::get('app');
        $key = input::get('key');
        $title = parseSearchKeyWord(input::get('title'));
        $range = [
            'sysconf' => ['sysconf_setting.wap_license'],
            'sysconf' => ['sysconf_setting.wap_usage_agreement'],
        ];
        if( !in_array($key, $range[$app]) )
            return kernel::abort(404);

        $pagedata['value'] = app::get($app)->getConf($key);
        $pagedata['title'] = app::get('topwap')->_($title);
        return $this->page('topwap/util/configContent.html', $pagedata);
    }
}
