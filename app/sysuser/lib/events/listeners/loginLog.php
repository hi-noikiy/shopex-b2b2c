<?php
class sysuser_events_listeners_loginLog implements base_events_interface_queue{

    public function handle($userId,$loginName,$platform,$loginWay)
    {
        $params['user_name'] = $loginName;
        $params['user_id'] = $userId;
        $params['login_ip'] = request::getClientIp();
        $params['login_time'] = time();
        $params['login_platform'] = $platform ? $platform : 'pc';
        $params['login_way'] = $loginWay ? $loginWay."信任登录" : "账号登录";

        $objMdlLoginLog = app::get('sysuser')->model('user_login_log');
        return  $objMdlLoginLog->insert($params);
    }

}
