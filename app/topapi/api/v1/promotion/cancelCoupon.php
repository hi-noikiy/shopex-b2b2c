<?php
/**
 * topapi
 *
 * -- promotion.coupon.cancel
 * -- 订单结算取消使用优惠券
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_cancelCoupon implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '订单结算取消使用优惠券';

    public function setParams()
    {
        return array(
            'shop_id' => ['type'=>'string', 'valid'=>'required', 'desc'=>'取消使用优惠券的店铺ID'],
        );
        return $return;
    }

    public function handle($params)
    {
        $params['coupon_code'] = '-1';
        return app::get('topapi')->rpcCall('trade.cart.cartCouponCancel', $params);
    }
}

