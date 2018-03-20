<?php
/**
 * topapi
 *
 * -- member.security.userConf
 * -- 安全中心验证登录密码
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_security_getUserSecurityConf implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '获取会员安全中心相关所有配置';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
        );

        return $return;
    }



    public function handle($params)
    {
        $userId = $params['user_id'];

        $userConfig = [];

        //获取会员是否有支付密码
        $hasDepositPassword = app::get('topapi')->rpcCall('user.deposit.password.has', ['user_id'=>$userId]);
        $userConfig['hasDepositPassword'] = $hasDepositPassword['result'] ;

        //获取用户绑定的手机号
        $userInfo = app::get('topapi')->rpcCall('user.get.info', ['user_id'=>$userId]);
        $userConfig['userMobile'] = $userInfo['mobile'];

        return $userConfig;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":null}';
    }

}

