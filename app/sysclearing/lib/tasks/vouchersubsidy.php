<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysclearing_tasks_vouchersubsidy extends base_task_abstract implements base_interface_task
{
    // 每个队列执行100条订单信息
    public function exec($params=null)
    {
        $filter = array(
            'subsidy_time|than'=>strtotime(date('Y-m-01 00:00:00', strtotime('-1 month'))),
            'subsidy_time|sthan'=>strtotime(date('Y-m-t  23:59:59', strtotime('-1 month'))),
        );

        $objMdlVouchersubsidy = app::get('sysclearing')->model('vouchersubsidy');
        $objMdlVouchersubsidyDetail = app::get('sysclearing')->model('vouchersubsidy_detail');

        $detailList =  $objMdlVouchersubsidyDetail->getList('*',$filter);
        if( !$detailList ) return true;

        foreach( $detailList as $row )
        {
            $voucherId = $row['voucher_id'];
            $shopId    = $row['shop_id'];
            $saveData[$voucherId][$shopId]['subsidy_no'] = date('ym').str_pad($shopId,6,'0',STR_PAD_LEFT).str_pad($voucherId,6,'0',STR_PAD_LEFT);
            $saveData[$voucherId][$shopId]['shop_id'] = $shopId;
            $saveData[$voucherId][$shopId]['voucher_id'] = $row['voucher_id'];
            $saveData[$voucherId][$shopId]['tradecount'] += 1;
            if( $saveData[$voucherId][$shopId]['subsidy_fee'] )
            {
                $saveData[$voucherId][$shopId]['subsidy_fee'] = ecmath::number_plus([$saveData[$voucherId][$shopId]['subsidy_fee'], $row['subsidy_fee']]);
            }
            else
            {
                $saveData[$voucherId][$shopId]['subsidy_fee'] = $row['subsidy_fee'];
            }

            $saveData[$voucherId][$shopId]['account_start_time'] = strtotime(date('Y-m-01 00:00:00', strtotime('-1 month')));
            $saveData[$voucherId][$shopId]['account_end_time'] = strtotime(date('Y-m-t  23:59:59', strtotime('-1 month')));
        }

        foreach($saveData as $voucherRow)
        {
            foreach($voucherRow as $shopRow)
            {
                $objMdlVouchersubsidy->save($shopRow);
            }
        }

        return true;
    }
 }
