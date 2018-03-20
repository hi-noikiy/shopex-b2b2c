<?php
class topapi_payment{
    /*
     *检测要支付的订单数据有效性
     *创建支付单
     *返回支付单编号
     */
    public function getPaymentId($filter)
    {
        $tids = $filter['tid'];
        $uname = $filter['user_name'];
        if($filter['tid'] && is_array($filter['tid']))
        {
            $tids = implode(',',$filter['tid']);
        }
        $tradeParams = array(
            'user_id' => $filter['user_id'],
            'tid' => $tids,
            'fields' => 'tid,payment,points_fee,user_id,status,hongbao_fee',
        );
        //获取需要支付的订单并检测其有效性
        $tradeList = app::get('topapi')->rpcCall('trade.get.list',$tradeParams);
        $count = $tradeList['count'];
        $tradeList = $tradeList['list'];

        $countid = count($filter['tid']);
        if($countid != $count)
        {
            throw new \LogicException(app::get('topapi')->_("支付失败，提交的订单数据有误"));
            return false;
        }

        foreach($tradeList as $key=>$value)
        {
            if($value['status'] != "WAIT_BUYER_PAY")
            {
                throw new \LogicException(app::get('topapi')->_($value['tid']." 订单已被支付,请重新选择要支付订单"));
                return false;
            }

            $payment['money'] = ecmath::number_plus(array($payment['money'], ecmath::number_minus(array($value['payment'], $value['hongbao_fee']))));
            $payment['user_id'] = $value['user_id'];
            $payment['user_name'] = $uname;
        }
        $payment['tids'] = $tids;
        try
        {
            $paymentId = app::get('topapi')->rpcCall('payment.bill.create',$payment);
        }
        catch(Exception $e)
        {
            throw $e;
        }
        return $paymentId;
    }
}
