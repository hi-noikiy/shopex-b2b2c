<?php
class ectools_data_refunds{
    /**
     * 生成退款单
     * @param  array $params 退款单所需参数
     * @return integer 退款单号
     */
    public function create($params)
    {
        if($params['rufund_type'] == 'offline')
        {
            $data['status'] = 'succ';
            $data['rufund_type'] = 'offline';
            $data['pay_app_id'] = 'offline';
            $data['finish_time'] =time();

            $data['refund_people'] = $params['refund_people'];
            $data['refund_bank'] = $params['refund_bank'];
            $data['refund_account'] = $params['refund_account'];

            $data['beneficiary'] = $params['beneficiary'];
            $data['receive_bank'] = $params['receive_bank'];
            $data['receive_account'] = $params['receive_account'];
        }
        elseif($params['rufund_type'] == 'online')
        {
            $data['status'] = 'ready';
            $data['rufund_type'] = 'online';
            $objMdlPayments = app::get('ectools')->model('payments');
            $paymentInfo = $objMdlPayments->getRow('pay_app_id', ['payment_id'=>$params['payment_id']]);
            $data['pay_app_id'] = $paymentInfo['pay_app_id'];
        }

        if($params['money'] == 0)
        {
            $data['status'] = 'succ';
            $data['memo'] = '退款金额为0元，直接退款状态为succ';
        }
        $data['refund_id'] = $this->genId();
        $data['money'] = $params['money'];
        $data['cur_money'] = $params['money'];
        $data['op_id'] = $params['op_id'];
        $data['rufund_type'] = $params['rufund_type'];
        $data['refunds_type'] = $params['refunds_type'];
        $data['aftersales_bn'] = $params['aftersales_bn'] ? : '';
        $data['created_time'] = time();
        $data['oid'] = $params['oid'] ? : '';
        $data['tid'] = $params['tid'];

        $data['return_fee'] = $params['return_fee'];//由于现在代码逻辑暂时存储，方便第三方退款后更新其他api
        $data['refunds_id'] = $params['refunds_id'];//由于现在代码逻辑暂时存储，方便第三方退款后更新其他api

        $objMdlRefunds = app::get('ectools')->model('refunds');
        $result = $objMdlRefunds->insert($data);
        if(!$result)
        {
            throw new \LogicException("创建退款单失败");
            return false;
        }
        return $data['refund_id'];
    }

    // 生成refund_id
    private function genId()
    {
        $now = time();
        $startTime = 1325347200;//2012-01-01 做为初始年
        //当前时间相距初始年的天数，4为可使用20年
        $day =  floor( ($now - $startTime) / 86400);
        //当天从0秒开始到当前时间的秒数 总数为86400
        $second = $now - strtotime(date('Y-m-d'));

        $base   = $day . str_pad($second,5,'0',STR_PAD_LEFT);//9位
        $random = str_pad(rand(0, 999),3,'0',STR_PAD_LEFT);//3位
        return $base.$random;
    }

}
