<?php
/**
 *  会员内容同步到矩阵
 */
class sysuser_events_listeners_pushAllUserToMatrix implements base_events_interface_queue {

    /**
     * 每个批次的会员多少个
     */
    public $pageSize = 100;

    /**
     * 打算把所有的会员push到matrix
     * 分批（100个/次）轮训出所有的会员数据，
     * 然后把这些数据遍历加到队列里面
     *
     */
    public function push($params)
    {
        $userNum = app::get('sysuser')->model('user')->count();

        $pageNum = intval($userNum/$this->pageSize) + 1;

        for( $i = 0; $i < $pageNum; $i++ )
        {
            $offset = $this->pageSize * $i;
            $userList = app::get('sysuser')->model('user')->getList('user_id', [], $offset, $this->pageSize, 'regtime ASC');
            foreach($userList as $user)
            {
                $this->pushToQueue($user);
            }
        }

    }

    private function pushToQueue($user)
    {
        $params = [];
        $params['userId'] = $user['user_id'];

        queue::push('sysuser_tasks_pushUserToMatrix','sysuser_tasks_pushUserToMatrix', $params);
    }

}

