<?php
/**
 * 订单事件触发将消息通知到prism
 *
 */
class sysaftersales_events_listeners__notifyShopexMatrix implements base_events_interface_queue {


    /**
     * 创建订单成功后，将订单数据同步到矩阵
     *
     * @param array $data 保存的订单结构
     * @param array $special 指定的参数
     */
    public function getAftersalesInfo($data, $shopId)
    {
        $objShopexMatrix = kernel::single('system_shopexMatrix');
        $params['shop_id'] = $shopId;
        $params['aftersales_bn'] = $data['aftersales_bn'];
        $objShopexMatrix->notify('sysaftersales_shopex_aftersalesInfo',$shopId, $params);
        if( $result['rsp'] == 'fail' )
        {
            throw new Exception('同步到矩阵失败');
        }

        return true;
    }

    public function getRefundInfo($data)
    {
        $objShopexMatrix = kernel::single('system_shopexMatrix');
        $params['shop_id']    = $data['shop_id'];
        $params['refunds_id'] = $data['refunds_id'];
        $objShopexMatrix->notify('sysaftersales_shopex_refundInfo',$data['shop_id'], $params);
        if( $result['rsp'] == 'fail' )
        {
            throw new Exception('同步到矩阵失败');
        }

        return true;
    }

    /**
     * 买家退货给卖家消息
     *
     * @param $data 买家退货消息
     * @param $shopId 接收退货的卖家店铺ID
     */
    public function buyerReturnGoods($data, $shopId)
    {
        $objShopexMatrix = kernel::single('system_shopexMatrix');

        $params['aftersales_bn']    = $data['aftersales_bn'];
        $params['corp_code']        = $data['corp_code'];
        $params['logi_name']        = $data['logi_name'];
        $params['logi_no']          = $data['logi_no'];
        $params['receiver_address'] = $data['receiver_address'];
        $params['mobile']           = $data['mobile'];
        $params['tid']              = $data['tid'];
        $params['oid']              = $data['oid'];
        $params['shop_id']          = $shopId;

        $objShopexMatrix->notify('sysaftersales_shopex_buyerReturnGoods',$shopId, $params);
        if( $result['rsp'] == 'fail' )
        {
            throw new Exception('同步到矩阵失败');
        }

        return true;
    }
}

