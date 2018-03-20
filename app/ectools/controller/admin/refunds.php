<?php
class ectools_ctl_admin_refunds extends desktop_controller{

    public function index()
    {
        return $this->finder('ectools_mdl_refunds',array(
            'use_buildin_filter'=>true,
            'use_view_tab'=>true,
            'title' => app::get('systrade')->_('退款单列表'),
            'use_buildin_delete'=>false,
        ));
    }
}
