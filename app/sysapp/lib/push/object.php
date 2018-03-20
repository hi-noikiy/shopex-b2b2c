<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sysapp_push_object{

    private $model ;

    private $__plugin;

    public function __construct()
    {
        $this->model = app::get('sysapp')->model('clients');
        $this->__plugin = kernel::single('sysapp_push_util')->getPushPlugin();
    }

    /**
     * @brief 向所有的设备推送消息
     * @params string title 消息的抬头
     * @params string text 消息的内容
     *
     * @return bool 返回结果
     *
     */
    public function pushAll($title, $text)
    {
        kernel::single('sysapp_push_logger')->add($title, $text, 'all', null);
        return $this->__plugin->pushAll($title, $text);
    }

    /**
     * @brief 向所有的设备推送消息
     * @params string title 消息的抬头
     * @params string text 消息的内容
     *
     * @return bool 返回结果
     *
     */
    public function pushAllWithParams($title, $text, $params)
    {
        kernel::single('sysapp_push_logger')->add($title, $text, 'allwithparams', null, $params);
        return $this->__plugin->pushAllWithParams($title, $text, $params);
    }

    /**
     * @brief 当有设备安装时，第一次启动会在这里注册clientid和token
     * @params string clientid 设备编号
     * @params string token 设备token，安卓可以没有
     * @params string type 标示这个设备是ios还是Android的
     *
     * @return int client_id 数据库内的逻辑id，自增
     *
     */
    public function register($clientid, $token = '', $type = 'ios', $plugin = 'igexin') {
        if(empty($clientid))
            throw new LogicException('Push插件：clientid不能为空!');

        $clientInfo = $this->model->getRow('client_id', ['clientid'=>$clientid]);
        $client_id = $clientInfo['client_id'];

        if(!$client_id)
        {
            $clientInfo = [
                'clientid' => $clientid,
                'token' => $token,
                'type' => $type == 'ios' ? 'ios' : 'android',
                'plugin' => $plugin == 'igexin' ? 'igexin' : 'mipush',
            ];
            return $this->model->insert( $clientInfo );
        }else{
            return $this->model->update( ['token'=>$token], ['client_id' => $client_id] );
        }

    }

    /**
     * @brief 当有设备安装时，第一次启动会在这里注册clientid和token
     * @params string clientid 设备编号
     * @params string user_id 登录用户的id
     * @params string token 设备token，安卓可以没有
     * @params string type 标示这个设备是ios还是Android的
     *
     * @return bool
     *
     */
    public function login($clientid, $user_id, $token = '', $type = 'ios', $plugin = 'igexin')
    {
        $clientInfo = $this->model->getRow('client_id', ['clientid'=>$clientid]);
        $client_id = $clientInfo['client_id'];
        if($client_id){
            return $this->model->update(['user_id'=>$user_id], ['client_id'=>$client_id]);
        }else{
            $clientInfo = [
                'clientid' => $clientid,
                'token' => $token,
                'type' => $type == 'ios' ? 'ios' : 'android',
                'user_id' => $user_id,
                'plugin' => $plugin == 'igexin' ? 'igexin' : 'mipush',
            ];
            return $this->model->insert( $clientInfo );
        }
        return true;
    }

}


