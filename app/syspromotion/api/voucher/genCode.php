<?php
/**
 * promotion.voucher.code.get
 *
 * ShopEx licence
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取购物券的编号
 */
final class syspromotion_api_voucher_genCode {

    public $apiDescription = '获取购物券的编号';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_id' => ['type'=>'int', 'valid'=>'required', 'desc'=>'购物券Id'],
            'user_id'    => ['type'=>'int', 'valid'=>'required', 'desc'=>'会员Id'],
            'grade_id'   => ['type'=>'int', 'valid'=>'required', 'desc'=>'会员等级ID'],
        );

        return $return;
    }

    /**
     *  获取购物券详情购物券
     * @return
     */
    public function handle($params)
    {
        $voucherInfo = kernel::single('syspromotion_data_promotion_voucher')->getVoucherCode($params['user_id'], $params['voucher_id'], $params['grade_id']);

        $result = [
            'voucher_id'        => $voucherInfo['voucher_id'],
            'voucher_code'      => $voucherInfo['code'],
            'voucher_name'      => $voucherInfo['voucher_name'],
            'used_platform'     => $voucherInfo['used_platform'],
            'deduct_money'      => $voucherInfo['deduct_money'],
            'limit_money'       => $voucherInfo['limit_money'],
            'limit_cat'         => $voucherInfo['limit_cat'],
            'subsidy_proportion' => $voucherInfo['subsidy_proportion'],
            'canuse_start_time' => $voucherInfo['canuse_start_time'],
            'canuse_end_time'   => $voucherInfo['canuse_end_time']
        ];

        return $result;
    }
}

