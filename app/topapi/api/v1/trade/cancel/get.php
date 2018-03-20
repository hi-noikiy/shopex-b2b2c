<?php
/**
 * topapi
 *
 * -- trade.cancel.get
 * -- 获取会员取消订单详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_trade_cancel_get implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '获取会员取消订单详情';

    /**
     * 定义API传入的应用级参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'cancel_id'    => ['type'=>'int', 'valid'=>'required|numeric',  'example'=>'', 'desc'=>'取消ID'],
        );

        return $return;
    }

    public function handle($params)
    {
        $cancelId = $params['cancel_id'];
        $data = app::get('topapi')->rpcCall('trade.cancel.get',['user_id'=>$params['user_id'],'cancel_id'=>$cancelId]);
        switch( $data['refunds_status'] )
        {
            case 'WAIT_CHECK':
                $data['status_desc'] = '审核中';
                $data['status_detail'] = '亲爱的客户，此订单已提交取消申请，正在审核';
                break;
            case 'WAIT_REFUND':
                $data['status_desc'] = '退款处理';
                $data['status_detail'] = '亲爱的客户，此订单正在进行退款处理';
                break;
            case 'SUCCESS':
                $data['status_desc'] = ($data['payed_fee'] > 0) ? '已完成退款' : '取消成功';
                $data['status_detail'] = ($data['payed_fee'] > 0) ? '亲爱的客户，此订单已取消成功，并已完成退款' : '亲爱的客户，此订单已取消成功';
                break;
            default:
                $data['status_desc'] = '取消失败';
                $data['status_detail'] = '亲爱的客户，订单取消失败';
        }

        $cur_symbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
        $data['cur_symbol'] = $cur_symbol;

        return $data;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"cancel_id":50,"user_id":4,"shop_id":3,"tid":1609111253210004,"pay_type":"online","payed_fee":"0.000","reason":"商品价格较贵","shop_reject_reason":null,"cancel_from":"buyer","process":"3","refunds_status":"SUCCESS","created_time":1473578622,"modified_time":1473578622,"log":[{"log_id":197,"rel_id":50,"op_id":4,"op_name":null,"op_role":"buyer","behavior":"cancel","log_text":"您的申请已提交","log_time":1473578622},{"log_id":198,"rel_id":50,"op_id":4,"op_name":null,"op_role":"system","behavior":"cancel","log_text":"您的订单取消成功","log_time":1473578622}],"status_desc":"取消成功","status_detail":"亲爱的客户，此订单已取消成功"}}';
    }
}
