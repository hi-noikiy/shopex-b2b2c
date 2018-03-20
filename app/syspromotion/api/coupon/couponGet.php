<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取单条优惠券数据
 */
final class syspromotion_api_coupon_couponGet {

    public $apiDescription = '获取单条优惠券数据';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'         => ['type' => 'int', 'valid'    => '', 'example'  => '', 'desc' => '店铺ID,user_id和shop_id必填一个'],
            'coupon_id'       => ['type' => 'int', 'valid'    => 'required', 'example' => '', 'desc' => '优惠券id'],
            'coupon_itemList' => ['type' => 'string', 'valid' => '', 'example' => '', 'desc' => '优惠券的商品'],
        );

        return $return;
    }

    /**
     *  获取单条优惠券信息
     * @param  array $params 筛选条件数组
     * @return array         返回一条优惠券信息
     */
    public function couponGet($params)
    {
        $couponInfo = kernel::single('syspromotion_data_object')->setPromotion('coupon', $params['shop_id'])->getPromoitonInfo($params['coupon_id']);
        $couponInfo['valid'] = $this->__checkValid($couponInfo);
        if($params['coupon_itemList'])
        {
            $couponItems = kernel::single('syspromotion_data_object')->setPromotion('coupon', $params['shop_id'])->getPromtionItems($params['coupon_id']);
            $couponInfo['itemsList'] = $couponItems;
        }
        return $couponInfo;
    }

    // 检查当前优惠券是否可用
    private function __checkValid(&$couponInfo)
    {
        $now = time();
        if( ($couponInfo['coupon_status']=='agree') && ($couponInfo['canuse_start_time']<$now) && ($couponInfo['canuse_end_time']>$now) )
        {
            return true;
        }
        return false;
    }
}

