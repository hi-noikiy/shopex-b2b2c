<?php
/**
 * user.voucher.get
 *
 * ShopEx licence
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取用户购物券信息
 */
final class sysuser_api_user_voucher_get {

    public $apiDescription = '获取用户购物券信息';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_code' => ['type'=>'int', 'valid'=>'required', 'desc'=>'购物券编码'],
            'user_id'    => ['type'=>'int', 'valid'=>'required', 'desc'=>'会员Id'],
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
        $voucherInfo = $objMdlUserVoucher->getRow('*', array('voucher_code'=>$params['voucher_code'], 'user_id'=>$params['user_id']));
        if( !$voucherInfo )
        {
            throw new \LogicException('购物券不存在');
        }

        return $voucherInfo;
    }
}

