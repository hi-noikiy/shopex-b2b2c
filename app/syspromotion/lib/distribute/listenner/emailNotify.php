<?php
class syspromotion_distribute_listenner_emailNotify
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

            $to = $userInfo['email'];
            $subject = $distribute['distribute_name'];
            $sms = $distribute['email_tmpl'];
            $config = [];

            if(in_array($distribute['remind_way'], ['email', 'both']) && !is_null($userInfo['mobile']))
            {
                kernel::single('system_messenger_email')->send( $to, $subject, $sms, $config );
            }
        }catch(Exception $e){
            logger::error('distribute send has some error with send email : ' . $e->__toString());
        }

    }

}


