<?php
class syspromotion_distribute_listenner_detail
{

    /*
     * @param array $params['user'] 会员用户信息
     *
     */
    public function consume($params)
    {
        $user       = $params['user'];
        $distribute = $params['distribute'];
        kernel::single('syspromotion_distribute_detail')->create($distribute, $user);
        return true;
    }

}

