<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 更新优惠券信息
 */
final class syspromotion_api_coupon_couponUpdate {

    public $apiDescription = '更新优惠券信息';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'       => ['type'=>'int',    'valid'=>'required', 'desc'=>'店铺ID', 'example'=>'4'],
            'coupon_id'     => ['type'=>'int',    'valid'=>'required', 'example'=>'', 'desc'=>'优惠券id'],
            'coupon_name'   => ['type'=>'string', 'valid'=>'required', 'desc'=>'优惠券名称', 'msg'=>'请填写优惠券名称'],
            'coupon_desc'   => ['type'=>'string', 'valid'=>'max:50', 'desc'=>'优惠券描述', 'msg'=>'优惠券描述不能超过50个字'],
            'limit_money'   => ['type'=>'string', 'valid'=>'required|numeric|min:1', 'desc'=>'满足条件金额', 'example'=>'100', 'msg'=>'请填写优惠券满足金额条件'],
            'deduct_money'  => ['type'=>'string', 'valid'=>'required|numeric|min:1', 'desc'=>'优惠金额', 'example'=>'10', 'msg'=>'请填写优惠券优惠金额'],
            'max_gen_quantity'  => ['type'=>'int', 'valid'=>'required|integer|min:1', 'desc'=>'生成优惠券总数量', 'example'=>'10', 'msg'=>'请填写优惠券生成总数量'],
            'userlimit_quantity'=> ['type'=>'int', 'valid'=>'required|integer|min:1', 'desc'=>'用户总计可领取数量', 'example'=>'10', 'msg'=>'请填写优惠券用户可领取总数量'],
            'valid_grade'       => ['type'=>'string', 'valid'=>'required', 'example'=>'1,2,3,4,5',  'desc'=>'适用会员,会员等级ID', 'msg'=>'请选择使用的会员等级'],
            'cansend_start_time'=> ['type'=>'string', 'valid'=>'required', 'example'=>'1483150679', 'desc'=>'优惠券可领取开始时间', 'msg'=>'请选择优惠券可领取开始时间'],
            'cansend_end_time'  => ['type'=>'string', 'valid'=>'required', 'example'=>'1483150679', 'desc'=>'优惠券可领取结束时间', 'msg'=>'请选择优惠券可领取结束时间'],
            'canuse_start_time' => ['type'=>'string', 'valid'=>'required', 'example'=>'1483150679', 'desc'=>'优惠券生效开始时间', 'msg'=>'请选择优惠券生效开始时间'],
            'canuse_end_time'   => ['type'=>'string', 'valid'=>'required', 'example'=>'1483150679', 'desc'=>'优惠券生效结束时间', 'msg'=>'请选择优惠券生效结束时间'],
            'coupon_rel_item'   => ['type'=>'jsonArray', 'valid'=>'required', 'example'=>'', 'desc'=>'优惠券关联的商品', 'params'=>array(
                'item_id' => ['type'=>'int',    'valid'=>'required|integer|min:1', 'example'=>'', 'desc'=>'商品ID'],
                'sku_id'  => ['type'=>'string', 'valid'=>'', 'example'=>'', 'desc'=>'SKU ID以逗号隔开, 数据为空则表示所有规格都参加'],
            ), 'msg'=>'请选择商品'],
        );

        return $return;
    }

    /**
     *  更新优惠券信息
     * @param  array $apiData api数据
     * @return bool
     */
    public function couponUpdate($apiData)
    {
        $apiData['coupon_name'] = strip_tags($apiData['coupon_name']);
        $apiData['coupon_desc'] = strip_tags($apiData['coupon_desc']);

        $forPlatform = intval($apiData['used_platform']);
        $apiData['used_platform'] = $forPlatform ? $forPlatform : '0';;

        return kernel::single('syspromotion_data_object')
            ->setPromotion('coupon', $apiData['shop_id'])
            ->savePromotion($apiData);
    }

}

