<?php
/**
 * 触发店铺通知
 */
class sysfinance_events_listeners_updateStatus
{
    public function notify($params){

        if($params['account_status'] == 1)
        {
            $type = "保证金预警";
            $this->setnotifyType($type);
            $noticeInfo = [
                'shop_id' => $params['shop_id'],
                'notice_title' => '保证金账户预警通知',
                'notice_type' => '保证金预警',
                'notice_content' => '您的保证金账户处于预警状态，请收到通知后进行补交。',
            ];
        }
        elseif($params['account_status'] == 2)
        {
            $type = "保证金欠缴";
            $this->setnotifyType($type);
            $noticeInfo = [
                'shop_id' => $params['shop_id'],
                'notice_title' => '保证金账户欠缴通知',
                'notice_type' => '保证金欠缴',
                'notice_content' => '您的保证金账户处于欠缴状态，请收到通知后进行补交，逾期未补交成功，您的货款将被暂停结算，直至补交完成。',
            ];
        }

        try
        {
            $result = app::get('sysshop')->rpcCall('shop.savenotice', $noticeInfo);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            throw new \LogicException($msg);
        }

        return $result;
    }

    public function setnotifyType($type)
    {
        $noticeType = app::get('sysshop')->getConf('shopnoticetype');
        if(!$noticeType)
        {
            app::get('sysshop')->setConf('shopnoticetype', [$type]);
        }
        elseif (!in_array($type, $noticeType))
        {
            array_push($noticeType, $type);
            app::get('sysshop')->setConf('shopnoticetype', $noticeType);
        }

        return ture;
    }
}