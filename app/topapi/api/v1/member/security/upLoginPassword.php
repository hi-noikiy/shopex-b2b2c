<?php
/**
 * topapi
 *
 * -- member.security.updateLoginPassword
 * -- 安全中心修改登录密码
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_security_upLoginPassword implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '安全中心修改登录密码';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'password'              => ['type'=>'string', 'valid'=>'required|min:6|max:20|confirmed', 'example'=>'demo123', 'desc'=>'新密码', 'msg'=>'请输入密码|密码长度不能小于6位|密码长度不能大于20位|输入的密码不一致'],
            'password_confirmation' => ['type'=>'string', 'valid'=>'required',               'example'=>'demo123', 'desc'=>'确认新密码',   'msg'=>'请填写确认密码'],
        );

        return $return;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":null}';
    }

    public function handle($params)
    {
        $key = 'topapi'.$params['user_id'].'security-update-password';
        if( ! cache::store('vcode')->get($key) )
        {
            throw new \LogicException('页面已过期，请重新验证原密码');
        }

        cache::store('vcode')->put($key, false);

        $data = array(
            'new_pwd' => $params['password'],
            'confirm_pwd' => $params['password_confirmation'],
            'user_id' => $params['user_id'],
            'type' => "reset",
        );
        app::get('topapi')->rpcCall('user.pwd.update',$data);
        return kernel::single('topapi_token')->delete($params['accessToken']);
    }
}
