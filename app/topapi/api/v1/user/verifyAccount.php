<?php
/**
 * topapi
 *
 * -- user.verifyAccount
 * -- 验证注册账号
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_user_verifyAccount implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '验证账号，用于注册，找回密码';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'account'    => ['type'=>'string', 'valid'=>'required|min:4', 'example'=>'demo',    'desc'=>'用户名／手机／邮箱', 'msg'=>'请输入账号|账号最少4个字'],
            'vcode_type' => ['type'=>'string', 'valid'=>'required|in:topapi_register,topapi_forgot', 'example'=>'signup', 'desc'=>'图片验证类型，图形验证码vcode_type参数一致'],
            'verifycode' => ['type'=>'string', 'valid'=>'required', 'example'=>'xuab',    'desc'=>'图片验证码', 'msg'=>'请输入验证']
        ];
    }

    /**
     * @return string account 注册账号
     * @return sring type login_account | email | mobile
     * @return sring mobile 找回密码返回该字段，如果返回的mobile为null，则表示账号未绑定手机，不可找回密码，需要平台客服重置密码
     * @return sring verifyAccount_token 验证账号后返回给短信或者设置密码时的令牌
     */
    public function handle($data)
    {
        $uname = $data['account'];

        if( $data['vcode_type'] == 'topapi_register' )
        {
            // 用户名、手机号、邮箱本来都可以注册，这里只从源头定为只支持手机号注册
            // 如果还需要其他注册方式，则在后台开启支持多方式注册
            // 增加客户手机信息的留存
            if( !app::get('sysconf')->getConf('user.account.register.multipletype') )
            {
                if(!preg_match("/^1[34578]{1}[0-9]{9}$/", $uname))
                {
                    $msg = app::get('topwap')->_("请输入正确的手机号码");
                    throw new \LogicException($msg);
                }
            }

            $accountType = app::get('topapi')->rpcCall('user.get.account.type',array('user_name'=>$uname));
            kernel::single('sysuser_passport')->checkSignupAccount($uname, $accountType);
        }
        else
        {
            $userData = userAuth::getAccountInfo($uname);
        }

        //验证图形验证码
        $verifycode = $data['verifycode'];
        if( !base_vcode::verify($data['vcode_type'], $verifycode))
        {
            throw new \LogicException(app::get('topapi')->_('验证码填写错误'));
        }

        $randomId = str_random(32);
        if( $data['vcode_type'] == 'topapi_forgot' )
        {
            //忘记密码
            if( !$userData )
            {
                //忘记密码，输入的账号错误
                throw new \LogicException(app::get('topapi')->_('请输入正确的账号'));
            }
            else
            {
                $res['mobile'] = $userData['mobile'];
                //只有绑定手机才可以找回密码
                if( $res['mobile'] )
                {
                    cache::store('vcode')->put('topapi'.$userData['mobile'].'forgot', $randomId, 3600);
                    $res['verifyAccount_token'] = $randomId;
                }
            }
        }
        else
        {
            //注册验证
            cache::store('vcode')->put('topapi'.$uname.'signup', $randomId, 3600);

            $res['account'] = $uname;
            $res['type'] = $accountType;
            $res['verifyAccount_token'] = $randomId;
        }

        return $res;
    }
}

