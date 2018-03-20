<?php
/**
 * topapi
 *
 * -- promotion.coupon.code.get
 * -- 用户领取优惠券
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_getCouponCode implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '用户领取优惠券';

    public function setParams()
    {
        return array(
            'coupon_id' => ['type'=>'int','valid'=>'required', 'desc'=>'优惠券ID'],
        );
        return $return;
    }

    public function handle($params)
    {
        return app::get('topapi')->rpcCall('user.coupon.getCode', $params);
    }
}

