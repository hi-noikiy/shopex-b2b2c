<?php
/**
 * ShopEx licence
 * - aftersales.get
 * - 获取单个售后详情
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取单条售后申请数据
 */
class topshopapi_api_v1_aftersales_get implements topshopapi_interface_api {

     /**
     * 接口作用说明
     */
    public $apiDescription = '获取单个售后详情';

    public function setParams()
    {
        return array(
            'aftersales_bn' => ['type'=>'int',       'valid'=>'required|numeric', 'title'=>'',  'desc'=>'申请售后编号'],
            'user_id'       => ['type'=>'int',       'valid'=>'int',              'title'=>'',  'desc'=>'售后单所属用户的用户id'],
            'fields'        => ['type'=>'field_list','valid'=>'required',         'title'=>'',  'desc'=>'获取单条售后需要返回的字段'],
        );
    }

    /**
     * 获取单条申请售后服务信息
     */
    public function handle($params, $type='oms')
    {
        return app::get('topshopapi')->rpcCall('aftersales.get', $params);
    }
}

