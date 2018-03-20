
<?php

class sysaftersales_shopex_refundInfo {

    public $apiMethod = 'store.trade.refund.add';

    //返回shopex体系创建结构
    public function handle($params)
    {
        $apiParams['refunds_id'] = $params['refunds_id'];
        $apiParams['shop_id']    = $params['shop_id'];
        $tmpData = app::get('systrade')->rpcCall('aftersales.refundapply.get', $apiParams);
        return $this->__getRefund($tmpData);
    }

    private function __getStatus( $v )
    {
        if ($v == '0')  return 'APPLY';

        if ($v == '2') return 'FAILED';

        if ($v == '4') return 'FAIL';

        if (in_array($v, ['1', '3', '5', '6']) ) return 'SUCC';
    }

    private function __getType( $v )
    {
        if (in_array($v, ['1', '3', '5', '6']))
        {
            return 'refund';
        }
        else
        {
            return "apply";
        }
    }

    private function __getRefund( $v )
    {
        if (!$v )  return null;

        $return =  [
            'refund_id'        =>  $v['refund_bn'],
            'tid'              =>  $v['tid'],
            'buyer_id'         =>  $v['user_id'],
            'buyer_account'    =>  '',
            'buyer_bank'       =>  '',
            'buyer_name'       =>  '',
            'currency'         =>  'CNY',
            'refund_fee'       =>  $v['refund_fee'],
            'paycost'          =>  '',
            'currency_fee'     =>  $v['total_price'],
            'pay_type'         =>  '',
            'refund_type'      =>  $this->__getType($v['status']),
            'payment_type'     =>  '',
            't_begin'          =>  $v['created_time'],
            't_sent'           =>  '',
            't_received'       =>  '',
            'memo'             =>  '',
            'outer_no'         =>  '',
            'modified'         =>  $v['modified_time'],
            'oid'              =>  $v['oid'],
            'shipping_type'    =>  '',
            'cs_status'        =>  '',
            'advance_status'   =>  '',
            'split_fee'        =>  '',
            'split_seller_fee' =>  '',
            'payment_id'       =>  '',
            'total_fee'        =>  $v['total_price'],
            'buyer_nick'       =>  '',
            'seller_nick'      =>  '',
            'created'          =>  $v['created_time'],
            'status'           =>  $this->__getStatus($v['status']),
            'good_status'      =>  '',
            'has_good_return'  =>  '',
            'reason'           =>  $v['refunds_reason'],
            'desc'             =>  '',
            'good_return_time' =>  '',
            'logistics_company'=>  '',
            'logistics_no'     =>  '',
            'company'          =>  '',
            'receiver_address' =>  '',
            'refund_item_list' =>  '',
        ];

        return $return;
    }

}
