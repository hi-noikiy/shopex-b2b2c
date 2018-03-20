<?php
/**
 * 恢复库存
 * item.store.recover
 */
class sysitem_api_item_recoverStore{

    public $apiDescription = "恢复库存";

    public function getParams()
    {
        $return['params'] = array(
            'item_id' => ['type'=>'int','valid'=>'required','description'=>'商品id','example'=>'2','default'=>''],
            'sku_id' => ['type'=>'int','valid'=>'required','description'=>'货品id','example'=>'2','default'=>''],
            'quantity' => ['type'=>'int','valid'=>'required','description'=>'恢复库存数量','example'=>'2','default'=>''],
            'sub_stock' => ['type'=>'bool','valid'=>'','description'=>'是否支持下单减库存(1 下单减库存, 0 付款减库存)','example'=>'1','default'=>''],
            'tradePay' => ['type'=>'bool','valid'=>'','description'=>'订单支付状态( 1已付款， 0 未付款)','example'=>'1','default'=>''],
        );
        return $return;
    }

    public function storeRecover($params)
    {
        $subStock = $params['sub_stock'];
        $tradePay = $params['tradePay'];
        unset($params['sub_stock']);
        unset($params['tradePay']);
        if($tradePay || $subStock)
        {
            $isRecover = kernel::single('sysitem_trade_store')->recoverItemStore($params);
            if(!$isRecover)return false;
        }
        else
        {
            $isRecover = kernel::single('sysitem_trade_store')->unfreezeItemStore($params);
            if(!$isRecover)return false;
        }

        event::fire('update.item', array($params['item_id']));
        return true;
    }

}
