<?php
/**
 * 结算确认事件
 *
 * 事件任务说明：更新订单结算状态
 */
class sysclearing_events_listeners_updateSettlementStatus {
    /**
     *
     * @param array $data 账单结算编号
     *
     */
     public function Update($settlementNo)
     {
       $shop_id = substr($settlementNo,-1);
        $objMdlSettlement = app::get('sysclearing')->model('settlement');
        $filter = array(
          'shop_id' => $shop_id,
          'settlement_no' => $settlementNo,
        );
        $fields = 'settlement_no,shop_id,settlement_status,account_start_time,account_end_time';
        $objMdlSettlement = app::get('sysclearing')->model('settlement');
        $settlementList = $objMdlSettlement->getRow($fields,$filter);
        $apiData['shop_id'] = $shop_id;
        $apiData['settlement_time_than'] = $settlementList['account_start_time'];
        $apiData['settlement_time_lthan'] = $settlementList['account_end_time'];
        $apiData['orderBy'] = 'settlement_time asc';
        $detailList = app::get('sysclearing')->rpcCall('clearing.detail.getlist',$apiData);
        foreach ($detailList['list'] as $key => $value) {
          $params['tid'] = $value['tid'];
          $params['settlement_status'] = $value['settlement_type'];
          app::get('sysclearing')->rpcCall('update.settleStatus',$params);
        }
        return true;
     }
}
