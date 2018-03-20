<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sysapp_push_igexin_object{

    private $__appId;

    private $__appKey;

    private $__masterSecret;

    private $__tool;


    public function __construct()
    {
        $this->__loader();
        $this->__init();
    }

    private function __loader()
    {
        require_once(dirname(__FILE__) . '/' . 'IGt.Push.php');
        require_once(dirname(__FILE__) . '/' . 'igetui/IGt.AppMessage.php');
        require_once(dirname(__FILE__) . '/' . 'igetui/IGt.APNPayload.php');
        require_once(dirname(__FILE__) . '/' . 'igetui/template/IGt.BaseTemplate.php');
        require_once(dirname(__FILE__) . '/' . 'IGt.Batch.php');
        require_once(dirname(__FILE__) . '/' . 'igetui/utils/AppConditions.php');
    }

    private function __init()
    {
        $this->__tool = kernel::single('sysapp_push_igexin_util');
        $this->__appId = $this->__tool->getAppId();
        $this->__appKey = $this->__tool->getAppKey();
        $this->__masterSecret = $this->__tool->getMasterSecret();
    }

    public function pushAll($title, $message){
        logger::info('Push Messages Content :' . json_encode(['title'=>$title, 'text'=>$message]));
        logger::debug('Push Messages :'. json_encode($this));
        $igt = new IGeTui(null,$this->__appKey,$this->__masterSecret);
        logger::debug('Push Messages Driver :'. json_encode($igt));
        //定义透传模板，设置透传内容，和收到消息是否立即启动启用
        $template = $this->IGtNotificationTemplate($title, $message);

        // 定义"AppMessage"类型消息对象，设置消息内容模板、发送的目标App列表、是否支持离线发送、
        // 以及离线消息有效期(单位毫秒)
        $message = new IGtAppMessage();
        logger::debug('Push Messages tmpl :'. json_encode($message));
        $message->set_isOffline(true);
        $message->set_offlineExpireTime(10 * 60 * 1000);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_data($template);
        $appIdList=array($this->__appId);
        $message->set_appIdList($appIdList);
        $rep = $igt->pushMessageToApp($message,"任务组名");
        logger::info('Push Messages Result :' . json_encode($rep));
        return true;
    }

    public function pushAllWithParams($title, $message, $params)
    {
        logger::info('Push Messages Content With Params:' . json_encode(['title'=>$title, 'text'=>$message, 'params'=>$params]));
        logger::debug('Push Messages :'. json_encode($this));
        $igt = new IGeTui(null,$this->__appKey,$this->__masterSecret);
        logger::debug('Push Messages Driver :'. json_encode($igt));
        //定义透传模板，设置透传内容，和收到消息是否立即启动启用
        $template = $this->IGtNotificationTemplate($title, $message, $params);

        // 定义"AppMessage"类型消息对象，设置消息内容模板、发送的目标App列表、是否支持离线发送、
        // 以及离线消息有效期(单位毫秒)
        $message = new IGtAppMessage();
        logger::debug('Push Messages tmpl :'. json_encode($message));
        $message->set_isOffline(true);
        $message->set_offlineExpireTime(10 * 60 * 1000);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_data($template);
        $appIdList=array($this->__appId);
        $message->set_appIdList($appIdList);
        $rep = $igt->pushMessageToApp($message,"任务组名");

        logger::info('Push Messages Result With Params:' . json_encode($rep));
        return true;
    }



    /**
     * @brief 生成信息模板，到底是什么，我也不知道，官方sdk这么用的。以后如果重写sdk，这里也会重写
     * @title 推送标题
     * @text 推送内容
     * @return  object
     */
    public function IGtNotificationTemplate($title, $text, $params = null){
        $template = new IGtNotificationTemplate();
        $template->set_appId($this->__appId); //应用appid
        $template->set_appkey($this->__appKey); //应用appkey
        if(!is_array($params)){
            $template->set_transmissionType(1); //透传消息类型
            $template->set_transmissionContent(json_encode($params));//透传内容
        }
        $template->set_title($title); //通知栏标题
        $template->set_text($text); //通知栏内容
        //$template->set_logo(""); //通知栏logo
        //$template->set_logoURL(""); //通知栏logo链接
        $template->set_isRing(true); //是否响铃
        $template->set_isVibrate(true); //是否震动
        $template->set_isClearable(true); //通知栏是否可清除
        return $template;
    }

}

