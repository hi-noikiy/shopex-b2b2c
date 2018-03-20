<?php
/**
 * 初始化保证金数据
 */
class sysfinance_events_listeners_guaranteeMoney
{
    public function init($shop_id){

        $initData = app::get('sysfinance')->rpcCall('shop.type.getinfo',['shop_id'=>$shop_id]);
        $guarantee_money_balance = 0;

        if($initData['guarantee_money'] == 0 ){
            $account_status = 0;
        }else{
            $account_status = 1;
        }

        $mdl = app::get('sysfinance')->model('guaranteeMoney');

        $saveData = [
            'shop_id' => $shop_id,
            'guarantee_money' => $initData['guarantee_money'],
            'guarantee_money_balance' => 0,
            'account_status' => $account_status,
            'modified_time' => time(),
        ];

        return $mdl->insert($saveData);
    }
}