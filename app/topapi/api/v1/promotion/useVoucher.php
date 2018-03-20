<?php

class topapi_api_v1_promotion_useVoucher implements topapi_interface_api{

	/**
     * 接口作用说明
     */
    public $apiDescription = '用户使用购物券';

    public function setParams()
    {
        return array(
            'voucher_code' => ['type'=>'string',  'valid'=>'required',        'desc'=>'购物券编码'],
            'platform'    => ['type'=>'string',  'valid'=>'required|in:pc,wap,app',  'desc'=>'使用平台 pc电脑端 wap手机端, app端'],
        );
        return $return;
    }

    public function handle($params)
    {
        return app::get('topapi')->rpcCall('trade.cart.voucher.add', $params);
    }	
}