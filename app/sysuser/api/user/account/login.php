<?php
class sysuser_api_user_account_login{

    public $apiDescription = "用户登录";
    public function getParams()
    {
        $params['params'] = array(
            'user_name' => ['type'=>'string','valid'=>'required', 'description'=>'登录用户名'],
            'password' => ['type'=>'string','valid'=>'required', 'description'=>'用户登录密码'],
            'platform' => ['type'=>'string','valid'=>'', 'description'=>'登录平台(pc/wap/app)'],
            'trust_login' => ['type'=>'string','valid'=>'', 'description'=>'信任登录方式'],
        );
        return $params;
    }
    public function userLogin($params)
    {
        try{
            $name = $params['user_name'];
            $password = $params['password'];
            $platform = $params['platform'];
            $trustLogin = $params['trust_login'];
            $loginResult = kernel::single('sysuser_passport')->login($name, $password, $platform, $trustLogin);
        }
        catch(\LogicException $e)
        {
            throw new \LogicException($e->getMessage());
        }
        return $loginResult;
    }
}
