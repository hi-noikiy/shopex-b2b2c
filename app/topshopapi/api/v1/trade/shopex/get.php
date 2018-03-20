<?php

class topshopapi_api_v1_trade_shopex_get implements topshopapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取单笔交易信息';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function setParams()
    {
        //接口传入的参数
        return array(
            'tid'     => ['type'=>'string', 'valid'=>'required', 'example'=>'','desc'=>'订单编号'],
            'oid'     => ['type'=>'string', 'valid'=>'',         'example'=>'','desc'=>'子订单编号，返回指定子订单编号的orders数据结构'],
            'fields'  => ['type'=>'field_list','valid'=>'', 'example'=>'*,orders.*,buyerInfo.*', 'desc'=>'获取单个订单需要返回的字段'],
        );
    }

    /**
     * 获取单笔交易数据
     *
     * @param array $params 接口传入参数
     * @return array
     */
    public function handle($params, $type='oms')
    {
        return kernel::single('systrade_shopex_tradeCreate')->handle($params);
    }
}
