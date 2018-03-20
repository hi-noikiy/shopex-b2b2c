<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class entermembercenter_ctl_register extends base_routing_controller
{

    public function index()
    {
        $pagedata['conf'] = base_setup_config::deploy_info();
        $callbackUrl = url::route('shopadmin', ['app' => 'entermembercenter', 'ctl' => 'register', 'act' => 'active']);
        $pagedata['loginHtml'] = prism::getLoginHtml($callbackUrl)."&view=onex_bbc_login";
        $output = view::make('entermembercenter/register.html', $pagedata)->render();
        return str_replace('%BASE_URL%',kernel::base_url(1),$output);
    }

    public function active()
    {
        if(!$_GET['code'])
        {
            $this->goRegister();
        }

        $result = prism::getToken($_GET['code']);

        if(!$result)
        {
            $this->goRegister();
        }

        $this->goPlatform();
    }

    public function goRegister()
    {
        header("Content-type: text/html; charset=utf-8");
        $active_url = url::route('shopadmin', ['app' => 'entermembercenter', 'ctl' => 'register']);
        echo '<script>top.location.href="'.$active_url.'"</script>';exit;
    }

    public function goPlatform()
    {
        $url = url::route('shopadmin');
        $url = base64_encode($url);
        $login_html = '?app=desktop&ctl=passport&act=index&url='.$url;
        echo '<script>top.location.href="'.$login_html.'"</script>';exit;
    }
}

