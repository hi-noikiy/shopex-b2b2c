<?php
class syspromotion_ctl_admin_remind extends desktop_controller{

    public function index()
    {
        return $this->finder('syspromotion_mdl_remind',array(
            'use_buildin_filter'=>true,
            'use_view_tab'=>true,
            'title' => app::get('systrade')->_('订阅提醒列表'),
            'use_buildin_delete'=>false,
        ));
    }
}
