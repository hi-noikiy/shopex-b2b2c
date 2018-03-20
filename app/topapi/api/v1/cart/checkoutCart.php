<?php
/**
 * topapi
 *
 * -- cart.checkout
 * -- 订单确认页面信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_cart_checkoutCart implements topapi_interface_api{

    public $payType = array(
        'online' => '线上支付',
        'offline' => '货到付款',
    );

    public $shippingType = array(
        'express' => '快递配送',
        'ziti' => '自提',
    );

    /**
     * 接口作用说明
     */
    public $apiDescription = '购物车结算页';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'mode' => ['type'=>'string', 'valid'=>'in:cart,fastbuy', 'desc'=>'购物车类型,默认是cart', 'example'=>'cart', 'msg'=>''],
        ];
    }

    public function handle($params)
    {
        //默认支付方式为在线支付
        $return['payType'] = array(
            'pay_type'=>'online',
            'name'=>$this->payType['online']
        );

        // 获取默认地址
        $addressApiParams['user_id'] = $params['user_id'];
        $addressApiParams['def_addr'] = 1;
        $userDefAddr = app::get('topapi')->rpcCall('user.address.list',$addressApiParams)['list'][0];
        if( $userDefAddr )
        {
            $return['default_address'] = $this->__transAddr($userDefAddr);
        }
        else
        {
            $return['default_address'] = null;
        }

        // 商品信息
        $cartFilter['mode'] = $params['mode'] ? $params['mode'] :'cart';
        $cartFilter['needInvalid'] = false;
        $cartFilter['platform'] = 'app';
        $cartFilter['user_id'] = $params['user_id'];
        $cartInfo = app::get('topapi')->rpcCall('trade.cart.getCartInfo', $cartFilter);
        
        if(!$cartInfo)
        {
            return array();
        }

        // 刷新结算页则失效前面选则的优惠券
        $shop_ids = array_keys($cartInfo['resultCartData']);
        foreach($shop_ids as $sid)
        {
            $apiParams = array(
                'coupon_code' => '-1',
                'shop_id' => $sid,
                'user_id' => $params['user_id'],
            );

            app::get('topapi')->rpcCall('trade.cart.cartCouponCancel', $apiParams);
        }

        // 刷新结算页则失效前面的购物券
        $apiParams = array(
            'user_id' => $params['user_id'],
            'platform' => 'app',
        );
        app::get('topapi')->rpcCall('trade.cart.voucher.cancel', $apiParams);

       //获取运费
        if($return['default_address']['addr_id'])
        {
            $totalParams = [
                'addr_id' => $return['default_address']['addr_id'],
                'user_id'  => $params['user_id'],
            ];
            $totalInfoWithUserPoint = kernel::single('topapi_cart_checkout')->totalWithPoint($totalParams);
            $return['total']['allPayment'] = $totalInfoWithUserPoint['total']['allPayment'];
            $return['total']['allPostfee'] = $totalInfoWithUserPoint['total']['allPostfee'];
            $return['total']['disCountfee'] = $totalInfoWithUserPoint['total']['disCountfee'];

            $return['userPoint'] = $totalInfoWithUserPoint['userPoint'];
        }
        //获取运费结束

        $isSelfShop = true;
        $fmtCartInfo = [];
        $totalFee = 0;
        foreach($cartInfo['resultCartData'] as $key=>$val)
        {
            if($val['shop_type'] != "self")
            {
                $isSelfShop = false;
            }

            unset($val['basicPromotionListInfo']);
            unset($val['usedCartPromotion']);
            unset($val['usedCartPromotionWeight']);
            unset($val['cartByPromotion']);

            $fmtObject = [];
            $giftCount = 0;
            $packageCount = 0;
            foreach($val['object'] as $o)
            {
                if( empty($o['gift']) )
                {
                    $o['gift'] = null;
                }else{
                    $fmtGift=[];
                    foreach($o['gift']['gift_item'] as $gift)
                    {
                        $fmtGift[] = [
                            'item_id'=>$gift['item_id'],
                            'title'=>$gift['title'],
                            'gift_num'=>$gift['gift_num'],
                            'image_default_id' => base_storager::modifier($gift['image_default_id'], 't')
                        ];

                        $giftCount += $gift['gift_num'];
                    }
                    $o['gift'] = $fmtGift;
                }

                if( $o['skuList'] )
                {
                    $skuList = $o['skuList'];
                    unset($o['skuList']);
                    foreach( $skuList as $skuListRow )
                    {
                        $skuListRow['image_default_id'] = base_storager::modifier($skuListRow['image_default_id'], 't');
                        $o['skuList'][] = $skuListRow;
                    }
                    $packageCount += ($o['quantity'] * count($skuList) - $o['quantity']);
                }

                $fmtPromotions = [];
                foreach($o['promotions'] as $promotion);
                {
                    $fmtPromotions[] = $promotion;
                }
                $o['promotions'] = $fmtPromotions;

                $o['image_default_id'] = base_storager::modifier($o['image_default_id'], 't');

                $fmtObject[] = $o;
            }

            $val['totalItem'] = $giftCount+$val['cartCount']['itemnum']+$packageCount;
            $val['items'] = $fmtObject;
            unset($val['object']);

            //组织每个店铺的配送方式
            $shipping['shipping_type'] = 'express';//默认为在线支付
            $shipping['shipping_name'] = $this->shippingType[$shipping['shipping_type']];
            $val['shipping'] = $shipping;

            $ifOpenZiti = app::get('syslogistics')->getConf('syslogistics.ziti.open');

            if($ifOpenZiti == 'true' && $val['shop_type'] == "self")
            {
                $val['ziti'] = true;
            }
            else
            {
                $val['ziti'] = false;
            }

            $val['cartCount']['payment'] = $totalInfoWithUserPoint['total']['shop'][$val['shop_id']]['payment'];
            $val['cartCount']['obtain_point_fee'] = $totalInfoWithUserPoint['total']['shop'][$val['shop_id']]['obtain_point_fee'] ?: 0;
            $val['cartCount']['post_fee'] = $totalInfoWithUserPoint['total']['shop'][$val['shop_id']]['post_fee'] ?: 0;
            $val['cartCount']['total_discount'] = $totalInfoWithUserPoint['total']['shop'][$val['shop_id']]['discount_fee'] ?: 0;
            unset($val['cartCount']['total_coupon_discount']);

            $totalFee += $val['cartCount']['total_fee'];

            $val['couponlist'] = $this->__getCoupons($val['shop_id'], $params['user_id']);
            if(count($val['couponlist']) == 0)
                $val['couponlist'] = null;
            $fmtCartInfo[] = $val;
        }

        $return['cartInfo']['resultCartData'] = $fmtCartInfo;
        $return['cartInfo']['catItemPrice'] = $cartInfo['catItemPrice'];
        $return['total']['allCostFee'] = $return['total']['allPayment'] ? : $totalFee;

        if( $isSelfShop )
        {
            $ifOpenOffline = app::get('ectools')->getConf('ectools.payment.offline.open');
            $return['ifOpenOffline'] = $ifOpenOffline == 'true' ? true : false;
        }
        $return['isSelfShop'] = $isSelfShop;

        //用户验证购物车数据是否发生变化
        $md5CartFilter = array('user_id'=>$params['user_id'], 'platform'=>'app', 'mode'=>$cartFilter['mode'], 'checked'=>1);
        $md5CartInfo = md5(serialize(utils::array_ksort_recursive(app::get('topapi')->rpcCall('trade.cart.getBasicCartInfo', $md5CartFilter), SORT_STRING)));
        $return['md5_cart_info'] = $md5CartInfo;

        $return['invoice'] = json_decode(redis::scene('sysuser')->hget('invoice_info', $params['user_id']), 1);
        //默认发票信息
        if( !$return['invoice'] )
        {
            $return['invoice']['invoice_type'] = 'notuse';
            $return['invoice']['invoice_title'] = 'individual';
            $return['invoice']['invoice_content'] = null;
            $return['invoice']['invoice_vat']['company_name'] = null;
            $return['invoice']['invoice_vat']['registration_number'] = null;
            $return['invoice']['invoice_vat']['company_address'] = null;
            $return['invoice']['invoice_vat']['bankname'] = null;
            $return['invoice']['invoice_vat']['bankaccount'] = null;
            $return['invoice']['invoice_vat']['company_phone'] = null;
        }

        $curSymbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
        $return['cur_symbol'] = $curSymbol;
        return $return;
    }

    private function __getCoupons($shop_id, $user_id)
    {
        // 默认取100个优惠券，用作一页显示，一般达不到这个数量一个店铺
        $params = array(
            'page_no' => 0,
            'page_size' => 100,
            'fields' => '*',
            'user_id' => $user_id,
            'shop_id' => intval($shop_id),
            'is_valid' => 1,
            'platform' => 'app',
        );
        $couponListData = app::get('topapi')->rpcCall('user.coupon.list', $params);
        $couponList = $couponListData['coupons'];

        return $couponList;
    }

    private function __transAddr($addr)
    {
        $ret = $addr;
        $tmpAddr = explode(':',$ret['area']);
        $ret['area']    = str_replace('/', ' ',$tmpAddr[0]);
        $ret['area_id'] = str_replace('/', ',',$tmpAddr[1]);
        return $ret;
    }

    public function returnJson()
    {
    }
}

