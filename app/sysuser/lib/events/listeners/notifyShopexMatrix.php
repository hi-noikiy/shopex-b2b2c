<?php
/**
 *  会员内容同步到矩阵
 */
class sysuser_events_listeners_notifyShopexMatrix implements base_events_interface_queue {

    public $createHandler = 'sysuser_shopex_createUser';

    public $updateHandler = 'sysuser_shopex_updateUser';

    public function createUser($userId)
    {
        return $this->__notify($userId);
    }

    public function updateBasicUser($userId)
    {
        return $this->__notify($userId);
    }

    public function setAccount($userId)
    {
        //这里因为队列执行太快了，会导致在读取的时候读取不到login_account的值
        //这样就会导致api传的用户名为空，所以临时加了一个sleep，让队列执行慢一点。
        //这里要改时间，让事件在部分情况下，被推入延时队列。
        //计划这个延时在config/events.php文件里面配置。
        sleep(3);
        return $this->__notify($userId);
    }

    public function verifyEmail($userId)
    {
        return $this->__notify($userId);
    }


    private function __notify($userId, $objShopexMatrix = null)
    {
        if( !$objShopexMatrix )
        {
            $objShopexMatrix = kernel::single('system_shopexMatrix');
        }

        $params['user_id']     = $userId;
        $params['crm_user_id'] = $this->__getCrmUserId($userId);

        if(!$params['crm_user_id'])
        {
            $result = $objShopexMatrix->notify($this->createHandler, 'platform', $params);
            if( !($result['rsp'] == 'succ') )
            {
                logger::error('send to matrix error with result : '.var_export($result, 1));
                return false;
                //throw new Exception('同步到矩阵失败');
            }else{

                $data = json_decode($result['data'], 1);
                $crm_user_id = $data['user']['user_id'];
                $user_crm_rel = ['user_id'=>$userId, 'crm_user_id'=>$crm_user_id];
                app::get('sysuser')->model('user_crm')->save($user_crm_rel);
            }
        }else{
            $result = $objShopexMatrix->notify($this->updateHandler, 'platform', $params);
            if( $result['rsp'] == 'fail' )
            {
                logger::error('send to matrix error with result : '.var_export($result, 1));
                return false;
                //throw new Exception('同步到矩阵失败');
            }
        }

        return true;
    }

    private function __getCrmUserId($userId)
    {

        $user_crm_rel = app::get('sysuser')->model('user_crm')->getRow('user_id,crm_user_id', ['user_id'=>$userId]);
        $crmUserId = $user_crm_rel['crm_user_id'];
        return $crmUserId;
    }

}

