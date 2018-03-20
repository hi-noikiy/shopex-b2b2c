<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class desktop_code{


  //public function getCodeInfo(){
  //    $code = $this->getAuthCode();

  //    $codeinfo = kernel::single('desktop_certicheck')->check_code($code);
  //    return $codeinfo;
  //}

    public function getAuthCode(){
        return app::get('desktop')->getConf('activation_code');
    }

    public function requestCertInfo()
    {
        $remoteCertInfo = kernel::single('desktop_certicheck')->check_certid();
        if($remoteCertInfo['res'] != 'succ')
            throw new RuntimeException($remoteCertInfo['msg']);
        return $remoteCertInfo;
    }

    public function getCodeExpire(){

        $expireTime = app::get('desktop')->getConf('activation_code_expire');
        if($expireTime > time()) return $expireTime;

        $certInfo = $this->requestCertInfo();
        $expireTime = $certInfo['info']['valid'];

        app::get('desktop')->setConf('activation_code_expire', $expireTime);

        return $expireTime;
    }

    //true 表示已经过期
    //false 表示还没有过期
    public function checkCodeExpire()
    {
        $expireTime = $this->getCodeExpire();
        if($expireTime == 0) return false;
        if($expireTime < time() + 30 * 24 * 60 * 60)
            return true;

        return false;
    }

    public function getCodeExpireAlertFlag()
    {
        if($this->hasAlerted()) return false;
        if(kernel::single('desktop_certicheck')->is_internal_ip() || kernel::single('desktop_certicheck')->is_demosite())
        {
            return false;
        }

        try{
            if($this->checkCodeExpire()) {
                $this->alerted();
                return true;
            }
        }catch(RuntimeException $e) {
            return false;
        }
        return false;

    }

    public function hasAlerted()
    {
        $key = $this->__genUserKey();
        $flag = cache::store('misc')->get($key);
        if($flag) return true;
        return false;
    }

    public function alerted()
    {
        $key = $this->__genUserKey();
        cache::store('misc')->put($key, true, 24*60);
        return ;
    }

    private function __genUserKey()
    {

        $prefix = 'hasAlerted';
        $userId = $_SESSION['account']['user_data']['user_id'];
        $dayNum = intval(time()/(24*3600));
        return $prefix . '-' . $userId . '-' . $dayNum;
    }

}
