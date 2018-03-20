<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysfinance_task{

    public function post_install($options)
    {
        $shoplist = app::get('sysfinance')->rpcCall('shop.list.get',['fields'=>'shop_id']);
        $db = app::get('sysfinance')->database();
        foreach ($shoplist['data'] as $shop) {
            $guaranteeInfo = app::get('sysfinance')->rpcCall('shop.type.getinfo',['shop_id'=>$shop['shop_id']]);
            if($guaranteeInfo['guarantee_money'] > 0){
                $account_status = 1;
            }else{
                $account_status = 0;
            }

            $db->executeUpdate('insert into sysfinance_guaranteeMoney (shop_id,guarantee_money,guarantee_money_balance,account_status,modified_time) value (?,?,?,?,?)',[$shop['shop_id'], $guaranteeInfo['guarantee_money'], 0, $account_status, time()]);
        }
        
    }
}