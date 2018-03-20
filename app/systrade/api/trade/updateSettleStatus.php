<?php
/**
 * ShopEx licence
 * - update.settleStatus
 * - 更新订单使用红包支付金额
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
class systrade_api_trade_updateSettleStatus {

    /**
     * 接口作用说明
     */
    public $apiDescription = '更新订单结算状态';

    /**
     * 接口参数
     */
     public function getParams()
     {
         $return['params'] = array(
             'tid'     => ['type'=>'string', 'valid'=>'required',  'title'=>'订单号',   'desc'=>'订单号'],
             'settlement_status' => ['type'=>'string', 'valid'=>'required',  'title'=>'订单结算状态',   'desc'=>'订单结算状态'],
         );
         return $return;
     }

     public function update($params){
       $objMdlTrade = app::get('systrade')->model('trade');
       try{
         $objMdlTrade->update(['settlement_status' =>$params['settlement_status']],['tid'=>$params['tid']]);
       }
       catch(Exception $e)
       {
         throw $e;
       }
       return true;
     }
}
