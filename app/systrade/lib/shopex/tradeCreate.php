<?php

class systrade_shopex_tradeCreate {

    public $apiMethod = 'store.trade.add';

    public $promotionDetail = [[
            'pmt_id' => "",
            'promotion_name' => "",
            'promotion_fee' => "",
            'promotion_desc' => "",
            'promotion_id' => "",
            'gift_item_id' => "",
            'gift_item_name' => "",
            'gift_item_num' => "",
            'pmt_type' => "",
    ]];

    //返回shopex体系创建订单结构
    public function handle($params)
    {
        $apiParams['tid']     = $params['tid'];
        $apiParams['shop_id'] = $params['shop_id'];
        $apiParams['fields'] = "*,orders.*,buyerInfo.*,payments.*";

        $tmpData = app::get('systrade')->rpcCall('trade.shop.get', $apiParams);
        return $this->__getTrade($tmpData);
    }

    private function __getOrder( $t )
    {
        if( !$t ) return null;

        $result = '';
        foreach( $t as $k=>$v )
        {
            $tmp = [
                'oid'       => $v['oid'],
                'type'      => 'goods',
                'bn'        => $v['bn'],
                'type_alias'=> "",
                'iid'       => $v['item_id'],
                'title'     => $v['title'],
                'items_num' => $v['num'],
                'total_order_fee' => $v['total_fee'],
                'weight' => $v['total_weight'],
                'discount_fee' => $v['discount_fee'],
                'status' => $v['status'],
                'ship_status' => "",
                'refund_status' => "",
                'consign_time' => $v['consign_time'],
                'sale_price' => "",
                'is_oversold' => "",
                'order_items'=> [
                    'item' => [[
                        'sku_id' => $v['sku_id'],
                        'item_type' => "product",
                        'iid' => $v['item_id'],
                        'bn' => $v['bn'],
                        'name'=> $v['title'],
                        'sku_properties' => $v['spec_nature_info'],
                        'weight' => $v['total_weight'],
                        'score' => "",
                        'discount_fee' => $v['discount_fee'],
                        'status' => $v['status'],
                        'price' => $v['price'],
                        'sale_price' => $v['total_fee'],
                        'total_item_fee' => $v['total_fee'],
                        'payment' => $v['payment'],
                        'num' => $v['num'],
                        'sendnum' => $v['sendnum'],
                        'pic_path' => $v['pic_path'],
                        'cid' => ""
                    ]]
                ],
            ];
            $result[] = $tmp;
        }
        return $result;
    }


    private function __getStatus($tradeStatus, $cancelStatus)
    {
        //原始订单没有status字段将报错
        if( !$tradeStatus )
        {
            return ['status'=> "error", 'pay_status'=>"error", 'ship_status'=>"error"];
        }

        // status check
        if ($tradeStatus == 'TRADE_FINISHED')
        {
            $status = 'TRADE_FINISHED';
        }
        elseif( in_array($tradeStatus, ['TRADE_CLOSED', 'TRADE_CLOSED_BY_SYSTEM'] ) )
        {
		    $status = 'TRADE_CLOSED';
        }
        else
        {
		    $status = 'TRADE_ACTIVE';
        }


        if (in_array($tradeStatus, ['WAIT_SELLER_SEND_GOODS','TRADE_FINISHED', 'WAIT_BUYER_CONFIRM_GOODS']))
        {
		    $payStatus = 'PAY_FINISH';
        }
        elseif( in_array($tradeStatus, ['TRADE_CLOSED', 'TRADE_CLOSED_BY_SYSTEM']))
        {
            $payStatus = 'REFUND_ALL';
        }
        else
        {
		    $payStatus = 'PAY_NO';
        }

        if ($cancelStatus  == 'WAIT_PROCESS' )
        {
            $payStatus = 'REFUNDING';
        }

        if ( in_array($tradeStatus, ['WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED','TRADE_FINISHED']) )
        {
            $shipStatus = 'SHIP_FINISH';
        }
        else
        {
            $shipStatus = 'SHIP_NO';
        }

	    return ['status'=>$status, 'pay_status'=>$payStatus, 'ship_status'=>$shipStatus];
    }

    private function __needInvoice($flag)
    {
        return intval($flag == 1) ? 'true' : 'false';
    }

    private function __invoiceTitle($needInvoice, $invoiceName, $invoiceMain)
    {
        if( !$needInvoice ) return '';

        if ($invoiceName == "individual")
        {
            return "个人";
        }
        return $invoiceMain;
    }

    private function __getTrade( $input )
    {
	    if ( !$input ) return [];

        $status = $this->__getStatus($input['status'], $input['cancel_status']);
        $return = [
		    'tid'     => $input['tid'],
		    'title'   => $input['title'],
		    'created' => $input['created_time'],
            'status'  => $status['status'],
            'pay_status' => $status['pay_status'],
            'ship_status' => $status['ship_status'],
            'has_invoice' => $this->__needInvoice($input['need_invoice']),  //'false' if order_msg.get('invoice_name','') =='' else 'true',
            'invoice_title' => $this->__invoiceTitle($input['need_invoice'], $input['invoice_name'], $input['invoice_main']),
            'invoice_desc' => "",
            'invoice_fee' => "", //--TODO
            'total_goods_fee' => $input['total_fee'],
            'total_trade_fee' => $input['payment'],
            'discount_fee' => "",
            'payed_fee' => ($status['pay_status'] == 'PAY_NO' || $status['status'] == 'TRADE_CLOSED') ? '' : $input['payed_fee'],
            'currency' => "CNY",
            //--
            'currency_rate' => '1.0',
            'total_currency_fee' => $input['payment'],
            'buyer_obtain_point_fee' => $input['obtain_point_fee'],
            'point_fee' => $input['consume_point_fee'],
            'total_weight' =>$input['total_weight'],
            'shipping_tid' => "",
            'shipping_type' => $input['dlytmpl_name'],// -- TODO
            'shipping_fee' => $input['post_fee'],
            'is_delivery' => true,
            'is_cod' => $input['is_cod'],
            'payment_type' => $input['pay_type'],
            'is_protect' => false,
            'protect_fee' => 0,
            'pay_time' => $input['pay_time'],
            'lastmodify' => $input['modified_time'],
            'modified' => $input['modified_time'],
            'end_time' => $input['end_time'],
            'confirm_time' => "",
            'timeout_action_time' => $input['timeout_action_time'],
            'goods_discount_fee' => 0,
		    //-- orders_discount_fee = $this->__ordersDiscountFee(input.orders),
            'orders_discount_fee' => $input['discount_fee'] + $input['points_fee'],
            //'promotion_details' => json_encode($this->promotionDetail),
            'consign_time' => $input['consign_time'],
            'receiver_name' => $input['receiver_name'],
            'receiver_mobile' => $input['receiver_mobile'],
            'receiver_email' => "",
            'receiver_state' => $input['receiver_state'],
            'receiver_city' => $input['receiver_city'],
            'receiver_district' => $input['receiver_district'],
            'receiver_address' => $input['receiver_address'],
            'receiver_zip' => $input['receiver_zip'],
            'receiver_phone' => $input['receiver_phone'],
            'receiver_time' => "",
            'commission_fee' => "",
            'pay_cost' => "",
            'seller_rate' => $input['seller_rate'],
            'seller_uname' => "",
            'seller_alipay_no' => "",
            'seller_mobile' => "",
            'seller_phone' => "",
            'seller_name' => "",
            'seller_email' => "",
            'seller_memo' => "",
            'buyer_name' => $input['buyerInfo']['username'],
            'buyer_alipay_no' => "",
            'buyer_id' => $input['user_id'],
            'buyer_uname' => $input['buyerInfo']['uname'],
            'buyer_email' => $input['buyerInfo']['email'],
            'buyer_memo' => $input['trade_memo'],
            'buyer_flag' => "",
            'buyer_message' => $input['buyer_message'],
            'buyer_rate' => $input['buyer_rate'],
            'buyer_mobile' => $input['buyerInfo']['mobile'],
            'buyer_phone' => "",
            'buyer_state' => "",
            'buyer_city' => "",
            'buyer_district' => "",
            'buyer_address' => "",
            'buyer_zip' => "",
            'orders_number' => $input['itemnum'],
            'trade_memo' => $input['shop_memo'],
            'orders' => json_encode(['order'=>$this->__getOrder($input['orders'])]),
            'payment_lists' =>  json_encode(['payment_list'=>$this->__getPaymentsList($input)]),
            'is_brand_sale' => "",
            'cod_status' => "",
            'trade_type' => "",
            'step_trade_status' => $input['step_trade_status'],
            'step_paid_fee' => $input['step_paid_fee'],
            'mark_desc' => "",
            'is_errortrade' => "",
            'passthrough' => ""
         ];

        return $return;
    }

    private function __getPaymentsList($tradeData)
    {
        if( !$tradeData['payments'] ) return null;

        $paymentList = "";
        foreach( $tradeData['payments'] as $key=>$val)
        {
            $paymentList[$key] = [
                'payment_id' => $val['payment_id']."-".$val['paybill_id'],
                'tid' => $tradeData['tid'],
                'seller_bank' => $val['bank'],
                'seller_account' => $val['account'],
                'buyer_id' => $tradeData['user_id'],
                'buy_name' => $val['user_name'],
                'buyer_account' => $val['pay_account'],
                'pay_fee' => $val['payment'],
                'paycost' => 0,
                'currency' => $val['currency'],
                'currency_fee' => $tradeData['payment'],
                'pay_type' => $tradeData['pay_type'],
                'payment_code' => $val['pay_app_id'],
                'payment_name' => $val['pay_name'],
                't_begin' => "",
                't_end' => $val['payed_time'],
                'pay_time' => $tradeData['pay_time'],
                'status' => "PAY_FINISH",
                'memo' => "",
                'outer_no' => ""
            ];
        }

        return $paymentList;
    }

    private function __ordersDiscountFee( $t )
    {
        if (!$t)  return '';
        $result = 0;
        foreach( $t as $k=>$v )
        {
            if ($k == "discount_fee")
            {
                $result = $result + $v;
            }
        }
        return $result;
    }
}

