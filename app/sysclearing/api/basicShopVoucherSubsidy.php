<?php
/**
 * clearing.subsidy.voucher.basic.shop
 */
class sysclearing_api_basicShopVoucherSubsidy {

    public $apiDescription = "获取指定商家参加购物券的补贴基本信息";

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'    => ['type'=>'string', 'valid'=>'required', 'example'=>'','desc'=>'店铺编号id'],
            'voucher_id' => ['type'=>'string', 'valid'=>'required', 'example'=>'','desc'=>'购物券ID'],
        );
        return $return;
    }

    /**
     * @return int subsidyFeeTotal 商家参加购物券平台应补贴的总额
     * @return int has_issued_subsidyFeeTotal 商家参加购物券平台已发放补贴补贴金额
     * @return int reserve_subsidyFeeTotal 商家参加购物券平台将发放补贴的今天
     * @return array list 平台补贴商家列表
     */
    public function handle($params)
    {
        //将要补贴给商家的总金额
        $subsidyFee = kernel::single('sysclearing_vouchersubsidy')->getShopSubsidyMoney($params['voucher_id'], $params['shop_id']);
        $result['subsidyFeeTotal'] = $subsidyFee;

        $list = app::get('sysclearing')->model('vouchersubsidy')->getList('*', ['voucher_id'=>$params['voucher_id'], 'shop_id'=>$params['shop_id']]);
        if( $list )
        {
            $result['has_issued_subsidyFeeTotal'] = 0;
            $result['reserve_subsidyFeeTotal'] = 0;
            foreach( $list as $row )
            {
                //补贴已发放
                if( $row['status'] == '2' )
                {
                    $result['has_issued_subsidyFeeTotal'] = ecmath::number_plus([$result['has_issued_subsidyFeeTotal'], $row['subsidy_fee']]);
                }
                else
                {
                    //准备发放的补贴
                    $result['reserve_subsidyFeeTotal'] = ecmath::number_plus([$result['reserve_subsidyFeeTotal'], $row['subsidy_fee']]);
                }
            }
        }

        $result['list'] = $list;
        return $result;
    }
}

