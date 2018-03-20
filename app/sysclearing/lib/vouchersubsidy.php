<?php

class sysclearing_vouchersubsidy {

    /*
     * 创建购物券补贴明细
    *  @param array $tradeInfo 订单数据
     * @param int $type 补贴类型 1 为普通补贴，2 退款补贴
     */
    public function createSubsidyDetail($tradeInfo, $type)
    {
        $objMdlDetail = app::get('sysclearing')->model('vouchersubsidy_detail');

        foreach($tradeInfo['orders'] as $key => $val)
        {
            //如果没有补贴金额或者补贴比例为0者跳过生成补贴明细
            if( $val['voucher_discount'] <= 0 || $val['voucher_subsidy_proportion'] <= 0 )
            {
                continue;
            }

            $subsidyFee = ecmath::number_multiple([$val['voucher_discount'],ecmath::number_div([$val['voucher_subsidy_proportion'], 100])]);
            $subsidyFee = ($type == '1') ? $subsidyFee : -$subsidyFee;
            $data = [
                'voucher_id'         => $val['voucher_id'],
                'voucher_name'       => $voucherInfo['voucher_name'],
                'oid'                => $val['oid'],
                'tid'                => $val['tid'],
                'shop_id'            => $val['shop_id'],
                'pay_time'           => $val['pay_time'],
                'order_fee'          => $val['payment'],
                'voucher_discount'   => $val['voucher_discount'],
                'subsidy_proportion' => $val['voucher_subsidy_proportion'],
                'subsidy_fee'        => $subsidyFee,
                'type'               => $type,
                'subsidy_time'       => time(),
            ];

            if( $objMdlDetail->insert($data) )
            {
                $this->setShopSubsidyMoney($val['voucher_id'], $val['shop_id'], $subsidyFee);
            }
        }

        return true;
    }

    public function doConfirm($subsidyNo)
    {
        $updateData['subsidy_time'] = time();
        $updateData['status'] = '2';
        return app::get('sysclearing')->model('vouchersubsidy')->update($updateData,array('subsidy_no'=>$subsidyNo));
    }

    //累增商家补贴金额
    public function setShopSubsidyMoney($voucherId, $shopId, $subsidyFee)
    {
        $subsidyFee = floatval(ecmath::number_multiple([$subsidyFee, 1000]));
        redis::scene('voucher')->hincrby('subsidy_'.$voucherId, $shopId, $subsidyFee);
        //购物券待补贴总额
        redis::scene('voucher')->incrby('subsidy_total_'.$voucherId, $subsidyFee);
        return true;
    }

    //获取累增商家补贴金额
    public function getShopSubsidyMoney($voucherId, $shopId)
    {
        $subsidyFee = redis::scene('voucher')->hget('subsidy_'.$voucherId, $shopId);
        return ecmath::number_div([$subsidyFee, 1000]);
    }

    //购物券补贴总额金额
    public function getVoucherSubsidy($voucherId)
    {
        $subsidyFee = redis::scene('voucher')->get('subsidy_total_'.$voucherId);
        return ecmath::number_div([$subsidyFee, 1000]);
    }
}

