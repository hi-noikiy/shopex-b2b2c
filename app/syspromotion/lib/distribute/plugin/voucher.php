<?php

class syspromotion_distribute_plugin_voucher implements syspromotion_distribute_plugin_interface
{
    public function receive($distributeDetail)
    {
        $apiParams = [];
        $apiParams['voucher_id'] = $distributeDetail['discount_param']['voucher_id'];
        $apiParams['user_id'] = $distributeDetail['user_id'];
        $apiParams['obtain_desc'] = app::get('syspromotion')->_('系统定向发放');

        $voucherResult = app::get('syspromotion')->rpcCall('user.voucher.code.get', $apiParams);

        return $voucherResult;
    }
}

