<?php
/**
 * topapi
 *
 * -- promotion.coupon.list.get
 * -- 用户可领取商家优惠券列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_shopCouponList implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '用户可领取商家优惠券列表';

    public function setParams()
    {
        return array(
            'shop_id'   => ['type'=>'int', 'valid'=>'required', 'desc'=>'商家店铺ID'],
            'page_no'   => ['type'=>'int', 'valid'=>'numeric', 'example'=>'', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int', 'valid'=>'numeric', 'example'=>'', 'desc'=>'每页数据条数,默认10条'],
        );
        return $return;
    }

    /**
     *
     * @return int coupon_id 优惠券ID
     * @return string coupon_name 优惠券名称
     * @return string coupon_desc 优惠券说明
     * @return string deduct_money 优惠金额
     * @return string canuse_start_time 优惠券生效时间
     * @return string canuse_end_time  优惠券过期时间
     */
    public function handle($params)
    {
        $params['fields'] = 'coupon_id,coupon_name,coupon_desc,deduct_money,canuse_start_time,canuse_end_time';
        $params['page_no'] = $params['page_no'] ?: 1;
        $params['page_size'] = $params['page_size'] ?: 10;
        $params['is_cansend'] = 1;
        $params['platform'] = 'app';
        $couponListData = app::get('topapi')->rpcCall('promotion.coupon.list', $params);
        if($couponListData['coupons'])
        {
            $return['list'] = $couponListData['coupons'];
            $return['pagers']['total'] = $couponListData['count'];

            $return['shopname'] = app::get('topapi')->rpcCall('shop.get', ['shop_id'=> $params['shop_id'],'fields'=>'shop_id,shop_name'])['shopname'];

            $curSymbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
            $return['cur_symbol'] = $curSymbol;
        }

        return $return;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"coupon_id":13,"coupon_name":"连衣裙 满100减15","coupon_desc":"连衣裙 满100减15","canuse_start_time":1453824000,"canuse_end_time":1582905600}],"pagers":{"total":4},"shopname":"onexbbc自营店（自营店铺）自营店","cur_symbol":{"sign":"￥","decimals":2}}}';
    }
}

