<?php
/**
 * @brief 短信发送日志
 */
class system_ctl_admin_smslog extends desktop_controller {

    /**
     * @brief  短信发送日志
     *
     * @return
     */
    public function index()
    {
        return $this->finder('system_mdl_smslog',array(
            'use_buildin_delete' => true,
            'title' => app::get('system')->_('短信日志'),
            'actions'=>array(),
        ));
    }

    public function _views()
    {
        $sub_menu = array(
            0=>array('label'=>app::get('systrade')->_('全部'),'optional'=>false),
            1=>array('label'=>app::get('systrade')->_('发送中'),'optional'=>false,'filter'=>array('status'=>'progress')),
            2=>array('label'=>app::get('systrade')->_('成功'),'optional'=>false,'filter'=>array('status'=>'succ')),
            4=>array('label'=>app::get('systrade')->_('失败'),'optional'=>false,'filter'=>array('status'=>'fail')),
        );

        return $sub_menu;
    }

}


