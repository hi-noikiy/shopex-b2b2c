<?php

class syspromotion_distribute_object
{

    private $__model ;

    public function __construct()
    {
        $this->__model = app::get('syspromotion')->model('distribute');
    }

    /**
     * @brief  保存定向发放优惠
     *
     * @param string distribute_name 定向发放名称
     * @param array user_filter 会员筛选项
     * @param enum discount_type 发放什么类型的优惠(hongbao,voucher)
     * @param array discount_param 优惠详细信息（比如红包id、购物券id）
     * @param enum remind_way 通过什么方式提醒(none, email, sms, both)
     * @param string sms_tmpl 短信模板
     * @param string email_tmpl 邮件模板
     *
     * @return int distribute_id
     **/
    public function createDistribute($distribute)
    {
        $this->checkoutSaveDistribute($distribute);

        $distribute['status'] = 'created';
        $distribute['created_time'] = time();

        $distribute_id = $this->__model->insert($distribute);
        $distribute['distribute_id'] = $distribute_id;

        //这两行是可以直接执行和进入队列的替换品
    //  $this->pushToQueue($distribute);
    //  kernel::single('syspromotion_distribute_tasks_distribute')->exec($distribute);

        event::fire('syspromotion.distribute.create', ['distribute' => $distribute]);

        return ['distribute_id'=>$distribute_id];
    }

    public function checkoutSaveDistribute($distribute)
    {

        if(! in_array($distribute['discount_type'], ['hongbao','voucher']))
            throw new LogicException('定向推送的类型错误');

        if(!in_array($distribute['remind_way'], ['none', 'email', 'sms', 'both']))
            throw new LogicException('通知方式的类型错误');

    }

    public function pushToQueue($distribute){
        return system_queue::instance()->publish('syspromotion_distribute_tasks_distribute', 'syspromotion_distribute_tasks_distribute', $distribute);
    }

}

