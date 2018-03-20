<?php
class topapi_passport{

    public function addLoginLog($userId, $loginName,$loginWay=null)
    {
        $params['user_id'] = $userId;
        $params['user_name'] = $loginName;
        $params['login_ip'] = request::getClientIp();
        $params['login_time'] = time();
        $params['login_platform'] = 'app';
        $params['login_way'] = $loginWay ? $loginWay."信任登录" : "账号登录";

        return  app::get('topapi')->rpcCall('user.login.log.add',$params);

    }
}
