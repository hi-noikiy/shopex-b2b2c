<?php
/**
 * @brief 商家登录日志
 */
class sysshop_ctl_admin_sellerloginlog extends desktop_controller {

    /**
     * @brief 商家登录日志
     *
     * @return
     */
    public function index()
    {
        /**
        if($this->has_permission('export')){
            $use_buildin_export = true;
        }
         */
        return $this->finder('sysshop_mdl_sellerloginlog',array(
            'use_buildin_delete' => false,
            //'use_buildin_export' => $use_buildin_export,
            'title' => app::get('sysshop')->_('商家登录日志'),
            'actions'=>array(),
        ));
    }

}


