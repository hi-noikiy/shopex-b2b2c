<?php
/**
 * topapi
 *
 * -- promotion.coupon.use
 * -- 用户使用优惠券
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_useCoupon implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '用户使用优惠券';

    public function setParams()
    {
        return array(
            'coupon_code' => ['type'=>'string',  'valid'=>'required',        'desc'=>'优惠券编码'],
            'mode'        => ['type'=>'string',  'valid'=>'in:cart,fastbuy', 'desc'=>'购物车类型,默认是cart', 'example'=>'cart'],
            'platform'    => ['type'=>'string',  'valid'=>'required|in:pc,wap,app',  'desc'=>'使用平台 pc电脑端 wap手机端, app端'],
        );
        return $return;
    }

    public function handle($params)
    {
        $result = app::get('topapi')->rpcCall('promotion.coupon.use', $params);
        if($result)
        {
            return app::get('topc')->rpcCall('trade.cart.voucher.add', ['voucher_code' => -1, 'user_id' => $params['user_id'], 'platform' => 'app' ]);
        }
        return $result;

    }
}

