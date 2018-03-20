<?php
/**
 * @brief 商城账号
 */
class sysuser_ctl_admin_tmphongbao extends desktop_controller {


    function __construct($app){
        parent::__construct($app);
    }
    /**
     * @brief  商家账号列表
     *
     * @return
     */
    public function index()
    {
        return $this->finder('sysuser_mdl_user_hongbao_tmp',array(
            'title' => app::get('sysuser')->_('待领取红包'),
            'use_buildin_filter' => true,
            'use_buildin_delete' => false,
            'actions'=>array(
            )
        ));
    }

}
