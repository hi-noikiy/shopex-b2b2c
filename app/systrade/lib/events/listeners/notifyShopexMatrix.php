<?php
/**
 * 订单事件触发将消息通知到prism
 *
 */
class systrade_events_listeners_notifyShopexMatrix implements base_events_interface_queue {


    /**
     * 创建订单成功后，将订单数据同步到矩阵
     *
     * @param array $data 保存的订单结构
     * @param array $special 指定的参数
     */
    public function tradeCreate($data, $special)
    {
        $objShopexMatrix = kernel::single('system_shopexMatrix');
        foreach( $data['trade'] as $shopData )
        {
            $this->__notify($shopData['tid'], $shopData['shop_id'], $objShopexMatrix);
        }

        return true;
    }

    /**
     * 修改运费
     */
    public function tradeEditPrice($tid, $shopId, $data)
    {
        return $this->__notify($tid, $shopId);
    }

    /**
     * 发货完成
     *
     * @param array $tradeData 发货的订单信息
     * @param array $shipData  发货信息
     */
    public function tradeDelivery($tradeData, $shipData)
    {
        return $this->__notify($tradeData['tid'], $tradeData['shop_id']);
    }

    /**
     * 确认收货,将消息通知到prism
     *
     * @param array $data 订单数据
     * @param array $operator 操作者数据
     */
    public function tradeConfirm($tradeData, $operator)
    {
        return $this->__notify($tradeData['tid'], $tradeData['shop_id']);
    }

    /**
     * 取消订单成功
     *
     * @param string $tid
     * @param string $cancelReason
     */
    public function tradeClose($tid, $shopId, $cancelReason)
    {
        return $this->__notify($tid, $shopId);
    }

    /**
     * 订单付款完成
     *
     * @param $tid string 订单ID
     * @param $payment string 订单付款金额
     * @param $shopId int 店铺ID
     */
    public function tradePay($tid, $payment, $shopId)
    {
        return $this->__notify($tid, $shopId);
    }

    /**
     * 订单退款消息
     *
     * @param string $tid
     */
    public function tradeRefund($tid, $shopId)
    {
        return $this->__notify($tid, $shopId);
    }

    private function __notify($tid, $shopId, $objShopexMatrix)
    {
        if( !$objShopexMatrix )
        {
            $objShopexMatrix = kernel::single('system_shopexMatrix');
        }

        $params['tid']     = $tid;
        $params['shop_id'] = $shopId;

        $result = $objShopexMatrix->notify('systrade_shopex_tradeCreate',$shopId, $params);
        if( $result['rsp'] == 'fail' )
        {
            throw new Exception('同步到矩阵失败');
        }

        return true;
    }
}

