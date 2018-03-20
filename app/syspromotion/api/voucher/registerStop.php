<?php
/**
 * promotion.voucher.register.stop
 *
 * ShopEx licence *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 平台终止指定商家购物券活动
 */
final class syspromotion_api_voucher_registerStop {

    public $apiDescription = '平台终止指定商家购物券活动';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_register_id'  => ['type'=>'int',  'valid'=>'required', 'desc'=>'购物券店铺购物券报名ID'],
        );

        return $return;
    }

    /**
     * @return
     */
    public function handle($apiData)
    {
        //商家报名表终止
        app::get('syspromotion')->model('voucher_register')->update(['valid_status'=>0], ['id'=>$apiData['voucher_register_id']]);

        return true;
    }
}

