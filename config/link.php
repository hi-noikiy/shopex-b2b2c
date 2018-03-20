<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


return array(
    /**
    |--------------------------------------------------------------------------
    | ShopEx企业账号站点
    |--------------------------------------------------------------------------
    |
    | ShopEx企业账号站点
    |
    */
    'shopex_openapi' =>'http://openapi.shopex.cn/api',

    /**
    |--------------------------------------------------------------------------
    | ShopEx企业账号相关API接口
    |--------------------------------------------------------------------------
    |
    | ShopEx企业账号相关API接口
    |
    */
    'shop_user_enterprise_api'=>'https://passport.shopex.cn/api.php',
    //'shop_user_enterprise_api'=>'http://passport.ex-sandbox.com/api.php',

    /**
    |--------------------------------------------------------------------------
    | ShopEx License标准接口
    |--------------------------------------------------------------------------
    |
    | 包含注册, 申请node_id, info查看等
    |
    */
    'license_center'=>'https://service.shopex.cn/', //证书的正式外网地址.
    //'license_center'=>'http://service.ex-sandbox.com/', //证书的正式外网地址.

    /**
    |--------------------------------------------------------------------------
    | ShopEx Matrix 节点关系绑定接口API
    |--------------------------------------------------------------------------
    |
    | ShopEx Matrix 节点关系绑定接口
    |
    */
    'matrix_relation_url' => 'https://iframe.shopex.cn',
    //'matrix_relation_url' => 'http://sws.ex-sandbox.com/',

    'matrix_relation_api' => 'http://www.matrix.ecos.shopex.cn/api.php',
    //'matrix_relation_api' => 'http://sws.ex-sandbox.com/api.php',

    /**
    |--------------------------------------------------------------------------
    | ShopEx Matrix 异步通信接口
    |--------------------------------------------------------------------------
    |
    | ShopEx Matrix 异步通信接口
    |
    */
    'matrix_async_url'=>'http://matrix.ecos.shopex.cn/async',
    //'matrix_async_url'=>'http://rpc.ex-sandbox.com/async',


    /**
    |--------------------------------------------------------------------------
    | ShopEx Matrix 同步通信接口
    |--------------------------------------------------------------------------
    |
    | ShopEx Matrix 同步通信接口
    |
    */
    'matrix_realtime_url'=>'http://matrix.ecos.shopex.cn/sync',
    //'matrix_realtime_url'=>'http://rpc.ex-sandbox.com/sync',

    /**
    |--------------------------------------------------------------------------
    | ShopEx Matrix Service接口
    |--------------------------------------------------------------------------
    |
    | ShopEx Matrix 同步通信接口
    |
    */
    'matrix_service_url'=>'http://matrix.ecos.shopex.cn/service',
    //'matrix_service_url'=>'http://rpc.ex-sandbox.com/service',

    /**
    |--------------------------------------------------------------------------
    | ShopEx sms open接口(外网正式)
    |--------------------------------------------------------------------------
    |
    | ShopEx 短信签名
    |
     */
    'sms_api' => 'https://openapi.shopex.cn/api',

    /**
    |--------------------------------------------------------------------------
    | ShopEx sms open接口(内网测试)
    |--------------------------------------------------------------------------
    |
    | ShopEx 短信签名
    |
    */
    'sms_debug' => "",  //开启测试地址的设置
    'sms_sandbox_api' => 'https://openapi.shopex.cn/api-sandbox',

    //测试数据图片地址
    'shopexdemoimage' => 'http://images.bbc.shopex123.com',
);
