<?php
/**
 * topapi
 *
 * -- user.sendSms
 * -- 发送注册短信
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_user_sendSms implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '注册账号和忘记密码发送短信';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'mobile'         => ['type'=>'string', 'valid'=>'required|mobile', 'example'=>'13918087654', 'desc'=>'手机号'],
            'type'           => ['type'=>'string', 'valid'=>'required|in:signup,forgot',  'desc'=>'发送短信类型 signup注册 forgot找回密码'],
            'send_sms_token' => ['type'=>'string', 'valid'=>'required', 'desc'=>'验证账号后返回的verifyAccount_token'],
        ];
    }

    public function handle($data)
    {
        $signupToken = cache::store('vcode')->get('topapi'.$data['mobile'].$data['type']);
        if( !$signupToken || $data['send_sms_token'] != $signupToken )
        {
            $msg = app::get("topapi")->_('页面已过期');
            throw new \LogicException($msg);
        }

        if( !userVcode::send_sms($data['type'], $data['mobile']) )
        {
            throw new \LogicException(app::get('topapi')->_('短信发送失败'));
        }

        return true;
    }
}

