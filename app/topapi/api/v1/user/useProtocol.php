<?php
/**
 * topapi
 *
 * -- user.protocol
 * -- 使用协议
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_user_useProtocol implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '使用协议';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [];
    }

    /**
     * @return string license 注册协议内容
     */
    public function handle($params)
    {
        $return['useProtocol'] = app::get('sysconf')->getConf('sysconf_setting.wap_usage_agreement');
        return $return;
    }
}