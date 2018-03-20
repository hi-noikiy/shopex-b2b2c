<?php
/**
 * user.voucher.back
 *
 * ShopEx licence
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 返还购物券
 */
final class sysuser_api_user_voucher_back {

    public $apiDescription = '返还购物券';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_code' => ['type'=>'string', 'valid'=>'required', 'desc'=>'购物券编码'],
            'user_id'      => ['type'=>'int',    'valid'=>'required', 'desc'=>'会员Id'],
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

        $objMdlUserVoucher->update(['is_valid'=>'1'], ['voucher_code'=>$params['voucher_code'],'user_id'=>$params['user_id']]);

        return true;
    }
}

