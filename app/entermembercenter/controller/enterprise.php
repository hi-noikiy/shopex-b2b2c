<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class entermembercenter_ctl_enterprise extends desktop_controller{

    function index(){

        $this->entid = base_enterprise::ent_id();
        $this->ent_loginname = base_enterprise::ent_loginname();
        if(empty($this->entid) ||empty($this->ent_loginname)){
            $pagedata['enterprise'] = false;
        }else{
            $pagedata['enterprise'] = true;
        }
        $pagedata['entid'] = $this->entid;
		$pagedata['ent_loginname'] = $this->ent_loginname;
        $pagedata['debug'] = false;

        return $this->page('entermembercenter/enterprise.html', $pagedata);
    }
}

