<?php
/**
 * user.voucher.code.get
 *
 * ShopEx licence
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 用户领取购物券
 */
final class sysuser_api_user_voucher_genCode {

    public $apiDescription = '用户领取购物券';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_id' => ['type'=>'int', 'valid'=>'required', 'desc'=>'购物券Id'],
            'voucher_code' => ['type'=>'string', 'valid'=>'', 'desc'=>'购物券编码'],
            'user_id'    => ['type'=>'int', 'valid'=>'required', 'desc'=>'会员Id'],
            'obtain_desc'=> ['type'=>'string', 'valid'=>'', 'desc'=>'领取方式'],
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

        $userInfo = kernel::single('sysuser_passport')->memInfo($params['user_id']);

        $apiData = $params;
        $apiData['grade_id'] = $userInfo['grade_id'];
        if($voucher_code) {
            $apiParams = [
                'voucher_id'=>$params['voucher_id'],
                'fields'=>'voucher_id,voucher_name,used_platform,limit_money,deduct_money,subsidy_proportion,start_time,limit_cat,end_time',
            ];
            $voucherInfo = app::get('promotion.voucher.get',$apiParams);
            $voucherInfo['voucher_code'] = $params['voucher_code'];
        } else {
            $voucherInfo = app::get('sysuser')->rpcCall('promotion.voucher.code.get', $apiData);
        }

        $saveData = [
            'user_id'       => $params['user_id'],
            'voucher_code'  => $voucherInfo['voucher_code'],
            'voucher_id'    => $voucherInfo['voucher_id'],
            'voucher_name'  => $voucherInfo['voucher_name'],
            'used_platform' => $voucherInfo['used_platform'],
            'obtain_desc'   => $params['obtain_desc'] ? $params['obtain_desc'] : '手动领取',
            'limit_money'   => $voucherInfo['limit_money'],
            'deduct_money'  => $voucherInfo['deduct_money'],
            'subsidy_proportion'  => $voucherInfo['subsidy_proportion'],
            'start_time'    => $voucherInfo['canuse_start_time'],
            'limit_cat'     => $voucherInfo['limit_cat'],
            'end_time'      => $voucherInfo['canuse_end_time'],
            'obtain_time'   => time(),
        ];

        $objMdlUserVoucher->insert($saveData);
        return $voucherInfo['voucher_code'];
    }
}

