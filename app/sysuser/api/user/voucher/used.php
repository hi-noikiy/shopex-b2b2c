<?php
/**
 * user.voucher.used
 *
 * ShopEx licence
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 购物券使用后，更新用户购物券状态
 */
final class sysuser_api_user_voucher_used {

    public $apiDescription = '购物券使用后，更新用户购物券状态';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_code' => ['type'=>'string', 'valid'=>'required', 'desc'=>'购物券编码'],
            'user_id'      => ['type'=>'int',    'valid'=>'required', 'desc'=>'会员Id'],
            'tid'          => ['type'=>'string', 'valid'=>'required', 'desc'=>'使用该购物券的订单ID'],
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

        $voucherData = $objMdlUserVoucher->getRow('voucher_id', ['voucher_code'=>$params['voucher_code'],'user_id'=>$params['user_id']]);
        try
        {
            if( $voucherData )
            {
                $objMdlUserVoucher->update(['is_valid'=>'0','tid'=>$params['tid']], ['voucher_code'=>$params['voucher_code'],'user_id'=>$params['user_id']]);
                app::get('sysuser')->rpcCall('promotion.voucher.code.usedQuantity',['voucher_id'=>$voucherData['voucher_id'],'quantity'=>1]);
            }
        }
        catch( Exception $e )
        {
            throw $e;
        }

        return true;
    }
}

