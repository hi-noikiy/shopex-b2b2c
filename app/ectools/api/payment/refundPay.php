<?php
class ectools_api_payment_refundPay{

    public $apiDescription = "第三方支付方式退款(原路退回专用)";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'payment_id' => ['type'=>'number', 'valid'=>'required|numeric',  'description'=>'支付单号，实际支付成功的支付单号'],
            'money'      => ['type'=>'number', 'valid'=>'required|numeric|min:0.01', 'description'=>'退款金额，必须大于0'],
            'refund_id'  => ['type'=>'number', 'valid'=>'required|numeric',  'description'=>'退款单号'],
        );
        return $return;
    }

    public function refundPay($params)
    {
        $objMdlPayments = app::get('ectools')->model('payments');
        $paymentData = $objMdlPayments->getRow('payment_id,trade_no,status,money,pay_app_id',array('payment_id'=>$params['payment_id']));

        if(!$paymentData || $paymentData['status'] != 'succ')
        {
            throw new Exception('请检查订单对应的支付单号是否存在且已支付成功，否则不能退款');
        }
        if(!$paymentData['trade_no'])
        {
            throw new Exception('支付失败，没有第三方支付交易号，请选择线下方式退款');
        }

        $objRefundPay = kernel::single('ectools_pay');
        $sdf = [
            'trade_no' => $paymentData['trade_no'],
            'refund_fee' => $params['money'],
            'refund_id' => $params['refund_id'],
            'total_fee' => $paymentData['money'],
            'payment_id' => $paymentData['payment_id'],
            'pay_app_id' => $paymentData['pay_app_id'],
            'type' => 'refund', //此参数一定不能少，判断是否是退款操作
            'pay_type' => 'online',
        ];
        $result = $objRefundPay->generate($sdf);
        if(!$result)
        {
            throw new Exception('支付失败,请求支付网关出错');
        }
        $objMdlRefunds = app::get('ectools')->model('refunds');
        switch ($result['status'])
        {
            case 'succ':
            case 'progress':
                $isUpdatedPay = $objMdlRefunds->update(['status'=>$result['status']], ['refund_id'=>$result['refund_id']]);
                break;
            case 'failed':
                $isUpdatedPay = $objMdlRefunds->update(['status'=>'failed'], ['refund_id'=>$result['refund_id']]);
                break;
        }
        return $result;
    }
}


