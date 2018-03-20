<?php
class syspromotion_distribute_listenner_distribute
{
    private $__userListLimit = 100;

    public function create($distribute)
    {
        $userFilter = $distribute['user_filter'];
        //$users的数据结构
        //array 'user_list' 详细数据
        //int   'count'     共多少条
        //int   'page_size' 每页多少条
        //int   'page_num'  共多少页
        $users = $this->__getUserList($userFilter, 1, $this->__userListLimit);
        $this->__createDetail($distribute, $users);
        if($users['page_num'] > 1)
        {
            for($i = 2; $i <= $users['page_num']; $i++ )
            {
                $users = $this->__getUserList($userFilter, $i, $this->__userListLimit);
                $this->__createDetail($distribute, $users);
            }
        }

        return true;
    }

    private function __getUserList($userFilter, $page, $limit)
    {
        logger::debug(
            'This is function "__getUserList" params : ' . "\n" .
            'user_fiter : ' . json_encode($userFilter) . "\n" .
            'page_num  : ' . json_encode($page) . "\n" .
            'page_size : ' . json_encode($limit) . "\n"
        );
        $apiParams = [
            'filter'    => json_encode( $userFilter ),
            'fields'    => 'user_id',
            'page_no'   => $page,
            'page_size' => $limit,
        ];

        return app::get('syspromotion')->rpcCall('user.list.byfilter', $apiParams);
    }

    /**
     * @param array distribute 详情查看distribute数据结构
     * @param array users 详情如下：
     *   //array 'user_list' 详细数据
     *   //int   'count'     共多少条
     *   //int   'page_size' 每页多少
     *   //int   'page_num'  共多少页
     */
    private function __createDetail($distribute, $users)
    {
        foreach($users['user_list'] as $user)
        {
            try{
                $itemData = ['distribute'=>$distribute, 'user' => $user];
              //logger::debug('=================================');
              //logger::debug(json_encode($itemData)); //这里调试过数据是否异常
              //logger::debug('=================================');
                system_queue::instance()->publish('syspromotion_distribute_tasks_item', 'syspromotion_distribute_tasks_item', $itemData);
                //这里放一个kernel::single();可以用来调试代码
                //kernel::single('syspromotion_distribute_tasks_item')->exec($itemData);
            }catch(Exception $e){
                logger::debug('There is something wrong : ' .
                    json_encode(['distribute'=>$distribute, 'user'=>$user, 'exception'=>$e->__toString()]));
            }
        }
        return true;
    }
}

