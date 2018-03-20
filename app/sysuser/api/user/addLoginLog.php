<?php
class sysuser_api_user_addLoginLog{

    public $apiDescription = "记录会员登录日志";

    public $use_strict_filter = true; // 是否严格过滤参数
    public function getParams()
    {
        $return['params'] = array(
            'user_id' =>['type'=>'integer','valid'=>'required|integer','description'=>'会员id'],
            'user_name' =>['type'=>'string','valid'=>'string','description'=>'登录名'],
            'login_way' =>['type'=>'string','valid'=>'string','description'=>'登录方式(微信、QQ、微博等)'],
            'login_platform' =>['type'=>'string','valid'=>'required|string','description'=>'登录平台(pc、wap、app)'],
            'login_time' => ['type'=>'string','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'会员登录时间'],
            'login_ip' => ['type'=>'string','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'会员登录时的IP'],
        );
        return $return;
    }

    public function addLog($params)
    {
        $objMdlUserloginlog = app::get('sysuser')->model('user_login_log');
        return $objMdlUserloginlog->insert($params);
    }
}
