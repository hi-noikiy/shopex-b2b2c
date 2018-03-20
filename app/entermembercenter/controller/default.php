<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class entermembercenter_ctl_default extends base_routing_controller
{

    public function __construct($app){
        kernel::set_online(false);
        if(kernel::single('base_setup_lock')->lockfile_exists()){
            if(!kernel::single('base_setup_lock')->check_lock_code()){
                $this->lock();
            }
        }
        parent::__construct($app);

        config::set('log.default', 'file');
    }

	public function active(){

		$callbackUrl = url::route('shopadmin', ['app' => 'entermembercenter', 'ctl' => 'default', 'act' => 'success']);
        prism::oauth($callbackUrl);
	}

	public function success()
	{

		if(!$_GET['code'])
        {
            $this->goRegister();
        }

        $objectOauth = kernel::single('entermembercenter_oauth');
        $result = $objectOauth->getToken($_GET['code']);

        if(!$result)
        {
            $this->goRegister();
        }

        $this->goPlatform();
    }

    public function goRegister()
    {
        $active_url = url::route('shopadmin', ['app' => 'entermembercenter', 'ctl' => 'default','act'=>'active']);
        echo '<script>top.location.href="'.$active_url.'"</script>';exit;
    }

    public function goPlatform()
    {
        $success_url = kernel::base_url(1).'/index.php/setup/default/success';
        echo '<script>top.location.href="'.$success_url.'"</script>';exit;
    }

}
