<?php
/**
 * ShopEx licence
 * - sysapp.modules.get
 * - 用于获取app端页面模块配置信息
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-23
 */
final class sysapp_api_push_login{

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = 'APP登录用户时注册的client终端设备';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'clientid' => ['type'=>'string', 'valid'=>'required', 'title'=>'clientid', 'example'=>'index', 'desc'=>'设备端获取的clientid'],
            'user_id' => ['type'=>'string', 'valid'=>'', 'title'=>'token', 'example'=>'index', 'desc'=>'登录时会员的id'],
            'token' => ['type'=>'string', 'valid'=>'', 'title'=>'token', 'example'=>'index', 'desc'=>'设备端获取的token'],
            'type' => ['type'=>'string', 'valid'=>'required|in:ios,android', 'title'=>'终端类型', 'example'=>'index', 'desc'=>'设备端获取的类型'],
            'plugin' => ['type'=>'string', 'valid'=>'required|in:igexin,mipush', 'title'=>'插件类型', 'example'=>'index', 'desc'=>'使用个推还是小米推送'],
        );

        return $return;
    }

    /**
     * @desc  用于注册新装app的用户
     * @return mix ret
     */
    public function login($params)
    {
        $clientid = $params['clientid'];
        $user_id = $params['user_id'];
        $token = $params['token'];
        $type = $params['type'];
        $plugin = $params['plugin'];
        $ret =  kernel::single('sysapp_push_object')->login($clientid, $user_id, $token, $type, $plugin);

        return ['ret' => $ret];
    }

}

