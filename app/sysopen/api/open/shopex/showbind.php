<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * open.shop.show.bind
 */
class sysopen_api_open_shopex_showbind {

    public $apiDescription = "查看店铺绑定关系";

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'    => ['type'=>'int', 'valid'=>'required', 'example'=>'1','desc'=>'店铺ID'],
        );
        return $return;
    }

    public function handle($params)
    {
        return kernel::single('sysopen_shopex_bind')->showBind($params['shop_id']);
    }
}


