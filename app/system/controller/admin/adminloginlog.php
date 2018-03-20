<?php
/**
 * @brief 平台操作日志
 */
class system_ctl_admin_adminloginlog extends desktop_controller {

    /**
     * @brief  平台操作日志
     *
     * @return
     */
    public function index()
    {
        return $this->finder('system_mdl_adminloginlog',array(
            'use_buildin_delete' => false,
            'title' => app::get('system')->_('平台登录日志'),
            'actions'=>array(),
        ));
    }

}


