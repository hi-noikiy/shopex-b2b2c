<?php
/**
 * updateMobile.php
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_security_setPayPasswordByMobile implements topapi_interface_api{
    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '忘记支付密码的短信验证';

    public function setParams()
    {
        return [
            'mobile' => ['type'=>'string', 'valid'=>'required|mobile', 'example'=>'13918087654', 'desc'=>'手机号'],
            'vcode' => ['type'=>'string', 'valid'=>'', 'example'=>'13918087654', 'desc'=>'手机验证码', 'msg'=>''],
            ];
    }

    public function handle($params)
    {
        $key = 'topapi'.$params['user_id'].'security-update-password';

        $userInfo = app::get('topapi')->rpcCall('user.get.info', ['user_id'=>$params['user_id']]);
        $userMobile = $userInfo['mobile'];
        if($userMobile != $params['mobile'])
            throw new \LogicException(app::get('topapi')->_('手机号错误'));

        if(!$params['vcode'])
        {
             if( !userVcode::send_sms('reset', $params['mobile']) )
             {
                 throw new \LogicException(app::get('topapi')->_('短信发送失败'));
             }
        }else{
            if( !userVcode::verify($params['vcode'], $params['mobile'], 'reset') )
            {
                throw new \LogicException(app::get('topapi')->_('验证码输入错误'));
            }

            cache::store('vcode')->put($key, true, 300);
        }
        return ['result'=>true];
    }
}

