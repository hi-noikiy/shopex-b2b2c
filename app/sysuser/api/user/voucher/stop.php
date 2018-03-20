<?php
/**
 * user.voucher.stop
 *
 * ShopEx licence
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 将购物券过期，平台终止购物券
 */
final class sysuser_api_user_voucher_stop {

    public $apiDescription = '将购物券过期，平台终止购物券';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_id' => ['type'=>'string', 'valid'=>'required', 'desc'=>'购物券Id'],
        );

        return $return;
    }

    /**
     *  获取购物券详情购物券
     * @return
     */
    public function handle($params)
    {
        $objMdlUserVoucher = app::get('sysuser')->model('user_voucher');
        $objMdlUserVoucher->update(['is_valid'=>'2','end_time'=>time()], ['voucher_id'=>$params['voucher_id'],'is_valid'=>'1']);
        return true;
    }
}

