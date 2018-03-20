<?php
/**
 * topapi
 *
 * -- message.push.mipush.register
 * -- 手机新装app注册设备号
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_message_push_register implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '手机新装app注册设备号';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'clientid' => ['type'=>'string', 'valid'=>'required', 'title'=>'clientid', 'example'=>'', 'desc'=>'设备端获取的clientid'],
            'token' => ['type'=>'string', 'valid'=>'', 'title'=>'token', 'example'=>'', 'desc'=>'设备端获取的token'],
            'type' => ['type'=>'string', 'valid'=>'required|in:ios,android', 'title'=>'终端类型', 'example'=>'', 'desc'=>'设备端获取的类型'],
            'plugin' => ['type'=>'string', 'valid'=>'required|in:igexin,mipush', 'title'=>'终端类型', 'example'=>'', 'desc'=>'插件类型'],
        ];
        return $return;
    }

    /**
     * @return int ret 设备id序号
     * @return int flag 标示请求成功
     **/
    public function handle($params)
    {
        $clientid = $params['clientid'];
        $token = $params['token'];
        $type = $params['type'];
        $plugin = $params['plugin'];

        $ret = app::get('topapi')->rpcCall(
            'sysapp.push.register',
            [
                'clientid'=>$clientid,
                'token'=>$token,
                'type'=>$type,
                'plugin'=>$plugin,
            ]
        );
        $ret['flag'] = true;
        return $ret;
    }
}

