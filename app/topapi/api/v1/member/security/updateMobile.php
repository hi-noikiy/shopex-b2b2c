<?php
/**
 * updateMobile.php
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 class topapi_api_v1_member_security_updateMobile implements topapi_interface_api{
     /**
      * api接口的名称
      * @var string
      */
     public $apiDescription = '安全中心会员手机号绑定/修改,发送短信验证码';

     public function setParams()
     {
         return [
             'mobile' => ['type'=>'string', 'valid'=>'required|mobile', 'example'=>'13918087654', 'desc'=>'手机号', 'msg'=>'请输入手机号码|请输入正确的手机号码'],
         ];
     }

     public function handle($params)
     {

        kernel::single('sysuser_passport')->checkSignupAccount($params['mobile'], 'mobile');

         $key = 'topapi'.$params['user_id'].'security-update-password';
         $flag = false;
         $flag = cache::store('vcode')->get($key, false);
         if($flag)
         {
             if( !userVcode::send_sms('reset', $params['mobile']) )
             {
                 throw new \LogicException(app::get('topapi')->_('短信发送失败'));
             }
         }
         else
         {
             throw new \LogicException(app::get('topapi')->_('页面已过期'));
         }

         return ['mobile'=>$params['mobile']];
     }
 }

