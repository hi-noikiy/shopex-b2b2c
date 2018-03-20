<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysapp_ctl_admin_download extends desktop_controller {

    public $workground = 'site.wrokground.theme';
public function __construct(&$app)
    {
        parent::__construct($app);
    }

    public function index()
    {
        $data = app::get('sysapp')->getConf('app.download.boot.setting');
        $pagedata = unserialize($data);
        return $this->page('sysapp/download_setting.html', $pagedata);
    }

    public function save()
    {
        $this->begin("?app=sysapp&ctl=admin_download&act=index");

        $data['name']        = input::get('name');
        $data['ios_url']     = input::get('ios_url');
        $data['android_url'] = input::get('android_url');
        $data['logo']        = input::get('logo');
        $data['desc']        = input::get('desc');
        $data['ad_url']      = input::get('ad_url');
        $data['open']        = input::get('open', false);
        //var_dump($data['open']=='true');
        //exit;
        if($data['open']=='true'){
            if( !$data['name'] )
            {
                return $this->end(false,'请填写app名称');
            }

            if( !$data['ios_url'] && !$data['android_url'] )
            {
                return $this->end(false,'IOS下载地址或安卓下载地址必填一个');
            }

            if($data['ios_url'] && substr($data['ios_url'],0,4) != 'http' )
            {
                return $this->end(false,'请输入正确的IOS下载地址');
            }

            if($data['android_url'] && substr($data['android_url'],0,4) != 'http' )
            {
                return $this->end(false,'请输入正确的安卓下载地址');
            }

            if( !$data['logo'] )
            {
                return $this->end(false,'请上传app logo');
            }

            if( !$data['desc'] )
            {
                return $this->end(false,'请填写app宣传推广用语');
            }

            if( !$data['ad_url'] )
            {
                return $this->end(false,'请上传app广告图片');
            }

                app::get('sysapp')->setConf('app.download.boot.setting', serialize($data));

                return $this->end(true);
        }
        else{
                app::get('sysapp')->setConf('app.download.boot.setting', serialize($data));

                return $this->end(true);
            }
    }
}

