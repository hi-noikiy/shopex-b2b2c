<?php
// refund.create
class ectools_api_refund_create{

    public $apiDescription = '创建退款单';

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'string','valid'=>'required', 'description'=>'申请售后的主订单编号',],
            'oid' => ['type'=>'string','valid'=>'required_if:refunds_type,0', 'description'=>'申请售后的子订单编号','msg'=>'当退款方式是售后申请退款，子订单号则必填'],
            'money' => ['type'=>'numeric','valid'=>'required|numeric|min:0', 'description'=>'退款金额',],

            'refund_bank' => ['type'=>'string','valid'=>'required_if:rufund_type,offline', 'description'=>'退款银行',],
            'refund_account' => ['type'=>'string','valid'=>'required_if:rufund_type,offline', 'description'=>'退款账号',],
            'refund_people' => ['type'=>'string','valid'=>'required_if:rufund_type,offline', 'description'=>'退款操作人',],
            'receive_bank' => ['type'=>'string','valid'=>'required_if:rufund_type,offline', 'description'=>'收款银行',],
            'receive_account' => ['type'=>'string','valid'=>'required_if:rufund_type,offline', 'description'=>'收款账号',],
            'beneficiary' => ['type'=>'string','valid'=>'required_if:rufund_type,offline', 'description'=>'收款人',],

            'aftersales_bn' => ['type'=>'string','valid'=>'required_if:refunds_type,0', 'description'=>'售后单编号','msg'=>'当退款方式是售后申请退款，售后编号则必填'],
            'refunds_type' => ['type'=>'string','valid'=>'required|in:0,1,2', 'description'=>'退款单类型',],
            'rufund_type' => ['type'=>'string','valid'=>'in:offline,online', 'description'=>'退款方式,offline(线下退款),online(在线原路退款)',],
            'op_id' => ['type'=>'integer','valid'=>'required|min:1', 'description'=>'操作员'],

            'return_fee'  => ['type'=>'number', 'valid'=>'required|numeric',  'description'=>'商家退款金额(含红包、积分抵扣金额)，第三方退款后使用'],
            'refunds_id'  => ['type'=>'number', 'valid'=>'required|min:1',  'description'=>'退款主键(sysaftersales/refunds表主键)，第三方退款后使用'],
            'payment_id'  => ['type'=>'number', 'valid'=>'required|min:1',  'description'=>'退款对应支付单号'],
        );
        return $return;
    }

    // '0' => '售后申请退款','1' => '取消订单退款','2' => '拒收订单退款',
    public function create($params)
    {
        if( $params['refunds_type'] == '0' && (empty($params['aftersales_bn']) || empty($params['oid']) ) )//退款类型，售后退款
        {
            throw new \LogicException('请填写售后单编号或字订单编号');
        }

        $db = app::get('ectools')->database();
        $db->beginTransaction();
        try
        {
            $objRefund = kernel::single('ectools_data_refunds');
            $result = $objRefund->create($params);
        }
        catch(\Exception $e)
        {
            $db->rollback();
            throw new \LogicException(app::get('ectools')->_($e->getMessage()));
            return false;
        }
        $db->commit();
        return $result;
    }
}
