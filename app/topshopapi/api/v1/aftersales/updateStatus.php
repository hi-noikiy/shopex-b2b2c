<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 更新售后状态外部联通使用
 */
class topshopapi_api_v1_aftersales_updateStatus implements topshopapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '更新售后状态';

    /**
     * 消费者提交退货物流信息参数
     */
    public function setParams()
    {
        return array(
            'aftersales_bn' => ['type'=>'string','valid'=>'required', 'description'=>'申请售后的订单编号'],
            'status'        => ['type'=>'string', 'valid'=>'required', 'description'=>'售后状态'],
            'memo'          => ['type'=>'string', 'valid'=>'', 'description'=>'备注，如果售后为换货类型卖家重新发货信息必填在备注中'],
        );
    }

    /**
     * 消费者提交退货物流信息
     */
    public function handle($params, $type='oms')
    {
        if( $type == 'oms' )
        {
            $params['status'] = $this->__getStatus($params['status']);
        }

        return app::get('topshopapi')->rpcCall('aftersales.status.update', $params);
    }

    private function __getStatus($s)
    {
        $statusArr = [
            "1" => "0",
            "2" => "0",
            "3" => "1",
            "4" => "4",
            "5" => "3",
            "6" => "5",
            "7" => "5",
            //"8" => "补差价",
            "9" => "3",
        ];

        return $statusArr[$s];
    }
}
