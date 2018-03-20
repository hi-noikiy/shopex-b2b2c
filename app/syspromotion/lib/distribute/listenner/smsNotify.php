<?php
class syspromotion_distribute_listenner_smsNotify
{

    /*
     * @param array $params['user'] 会员用户信息
     *
     */
    public function notify($params)
    {
        try{
            $user       = $params['user'];
            $distribute = $params['distribute'];
            $detail     = $params['detail'];
            $userInfo   = app::get('syspromotion')->rpcCall('user.get.info', ['user_id'=>$user['user_id'], 'fields'=>'mobile,email']);

            $to = $userInfo['mobile'];
            $title = $distribute['distribute_name'];
            $sms = $distribute['sms_tmpl'];
            $config = [];

            if(in_array($distribute['remind_way'], ['sms', 'both']) && !is_null($userInfo['mobile']))
            {
                kernel::single('system_messenger_sms')->send( $to, $title, $sms, $config );
            }
        }catch(Exception $e){
            logger::error('distribute send has some error with send sms : ' . $e->__toString());
        }
    }
}


