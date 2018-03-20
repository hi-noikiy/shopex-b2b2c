<?php
/**
 * topapi
 *
 * -- trade.cancel.reason.get
 * -- 获取平台配置的取消订单原因
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_trade_cancel_getCancelReason implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '获取平台配置的取消订单原因';

    /**
     * 定义API传入的应用级参数
     */
    public function setParams()
    {
        return [];
    }

    public function handle($params)
    {
        $data['list'] = config::get('tradeCancelReason.user');
        unset($data['list']['other']);
        rsort($data['list']);
        return $data;
    }

   public function returnJson()
   {
       return '{"errorcode":0,"msg":"","data":{"list":["重复下单","订单商品选择有误","现在不想购买","无法支付订单","收货信息填写有误","支付方式选择有误","商品缺货","商品价格较贵","发票信息填写有误","价格波动"]}}';
   }
}

