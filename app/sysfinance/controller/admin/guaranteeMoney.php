<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 class sysfinance_ctl_admin_guaranteeMoney extends desktop_controller
 {
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
        $this->objMdlGuaranteeMoney = app::get('sysfinance')->model('guaranteeMoney');
    }

    public function index(){
        return $this->finder('sysfinance_mdl_guaranteeMoney',array(
            'use_buildin_delete' => false,
            'use_buildin_filter' => true,
            'use_view_tab' => true,
        ));
    }

    /**
     *@brief 保证金充值
     *
     */
    public function recharge()
    {
        $postData = $this->__check(input::get());
        try{
            kernel::single('sysfinance_data_guaranteeMoney')->adjustBalance($postData);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg);
        }

        $msg = app::get('sysfinance')->_('修改成功');

        return $this->splash('success', null, $msg);
    }

    /**
     *@brief 保证金扣款
     *
     */
    public function expense()
    {
        $postData = $this->__check(input::get());
        try{
            kernel::single('sysfinance_data_guaranteeMoney')->adjustBalance($postData);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg);
        }

        $msg = app::get('sysfinance')->_('修改成功');

        return $this->splash('success', null, $msg);
    }

    /**
     *@brief 保证金余额调整
     *
     *@param $postData
     *
     *@return
     *
     */
    private function __check($postData)
    {
        unset($postData['app']);
        unset($postData['ctl']);
        unset($postData['act']);

        if(!$postData['shop_id'])
        {
            $msg = app::get('sysfinance')->_('店铺信息错误！');
            return $this->splash('error', null , $msg);
        }

        if(iconv_strlen($postData['money']) > 10)
        {
            $msg = app::get('sysfinance')->_('调整金额过大！');
            return $this->splash('error', null, $msg);
        }

        return $postData;
    }

    /**
     *@brief 设置单店保证金额度
     *
     *@param
     *
     *@return
     *
     */
    public function setGuaranteeMoney()
    {
        $postData = input::get();
        try{
            kernel::single('sysfinance_data_guaranteeMoney')->setGuaranteeMoney($postData);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg);
        }

        $msg = app::get('sysfinance')->_('修改成功');

        return $this->splash('success', null, $msg);


    }

    public function _views()
    {
        $sub_menu = array(
            1=>array(
                'label' => app::get('sysfinance')->_('全部'),
                'optional' => true,
                'filter' => array(
                ),
            ),
            2=>array(
                'label' => app::get('sysfinance')->_('正常'),
                'optional' => false,
                'filter' => array(
                    'account_status' => '0',
                ),
            ),
            3=>array(
                'label' => app::get('sysfinance')->_('预警'),
                'optional' => false,
                'filter' => array(
                    'account_status' => '1',
                ),
            ),
            4=>array(
                'label' => app::get('sysfinance')->_('欠缴'),
                'optional' => false,
                'filter' => array(
                    'account_status' => '2',
                ),
            ),
        );

        return $sub_menu;
    }
 }