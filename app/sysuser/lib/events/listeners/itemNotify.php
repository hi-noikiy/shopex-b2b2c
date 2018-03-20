<?php
/**
 *发送到货通知的邮件
 */
class sysuser_events_listeners_itemNotify
{
	public function notify($params){
		$queue_params['sendstatus'] = 'ready';
		$queue_params['item_id'] = $params['item_id'];
		$queue_params['shop_id'] = $params['shop_id'];

		$notifyItemList = array_column(app::get('sysuser')->rpcCall('user.notifyItemList',$queue_params),'sku_id');
		if($notifyItemList){

			$skuParams['item_id'] = $params['item_id'];
	        if($params['status'] == "onsale")
	        {
	            foreach ($notifyItemList as $skuId) {
	            	$skuParams['sku_id'] = $skuId;
	            	$skuData = app::get('topshop')->rpcCall('item.sku.get',$skuParams);
	            	$skuData['realStore'] = $skuData['store'] - $skuData['freez'];
	            	//发送到货通知的邮件
	                if(intval($skuData['realStore']) >= 1){
	                    $queue_params['sku_id'] = $skuId;
	                    system_queue::instance()->publish('sysitem_tasks_userItemNotify', 'sysitem_tasks_userItemNotify', $queue_params);
	                }
	            }
	        }
		}
		
        return true;
	}
}
