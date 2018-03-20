<?php
/**
 * updateMobile.php
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 class topapi_api_v1_member_security_saveMobile implements topapi_interface_api{
     /**
      * api接口的名称
      * @var string
      */
     public $apiDescription = '安全中心会员手机号保存';

     public function setParams()
     {
         return [
             'mobile' => ['type'=>'string', 'valid'=>'required|mobile', 'example'=>'13918087654', 'desc'=>'手机号'],
             'vcode' => ['type'=>'string', 'valid'=>'required|digits:6', 'example'=>'13918087654', 'desc'=>'手机号', 'msg'=>'短信验证码必填|短信验证码为6位数字'],
         ];
     }

     public function handle($params)
     {
         $key = 'topapi'.$params['user_id'].'security-update-password';
         $flag = false;
         $flag = cache::store('vcode')->get($key, false);
         if($flag)
         {
             if( !userVcode::verify($params['vcode'], $params['mobile'], 'reset') )
             {
                 throw new \LogicException(app::get('topapi')->_('验证码输入错误'));
             }

             // 开始修改
             $data['user_id'] = $params['user_id'];
             $data['user_name'] = $params['mobile'];
             app::get('topapi')->rpcCall('user.account.update',$data);
             cache::store('vcode')->put($key, false);
         }
         else
         {
             throw new \LogicException(app::get('topapi')->_('页面已过期'));
         }

         return null;
     }
 }

