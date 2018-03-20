<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topshop_controller extends base_routing_controller
{

    /**
     * 页面不需要menu
     */
    public $nomenu = false;

    public function __construct($app)
    {
        pamAccount::setAuthType('sysshop');
        $this->app = $app;
        $this->sellerId = pamAccount::getAccountId();
        $this->sellerName = pamAccount::getLoginName();
        $this->shopId = app::get('topshop')->rpcCall('shop.get.loginId',array('seller_id'=>$this->sellerId),'seller');
        if($this->shopId)
        {
            $this->shopInfo = app::get('topshop')->rpcCall('shop.get',array('shop_id'=>$this->shopId));
        }
        $action = route::currentActionName();
        $actionArr = explode('@',$action);
        if( $actionArr['0'] != 'topshop_ctl_passport' )
        {
            if( !$this->shopId &&  !in_array($actionArr[0], ['topshop_ctl_register', 'topshop_ctl_enterapply', 'topshop_ctl_find', 'topshop_ctl_register', 'topshop_ctl_shopexnode']) )
            {
                redirect::action('topshop_ctl_register@enterAgreementPage')->send();exit;
            }
        }
        // debug模式开启情况下使用系统错误处理
        if (! config::get('app.debug', true))
        {
            kernel::single('base_foundation_bootstrap_handleExceptions')->setExceptionHandler(new topshop_exception_handler());
        }
        $this->topshopNewSetup = app::get('sysconf')->getConf('topshop.firstSetup');
        $this->openNewWapdecorate = redis::scene('shopDecorate')->hget('wapdecorate_status','shop_'.$this->shopId);
        $this->openNewAppdecorate = redis::scene('shopDecorate')->hget('appdecorate_status','shop_'.$this->shopId);
    }

    /**
     * @brief 检查是否登录
     *
     * @return bool
     */
    public function checklogin()
    {
        if($this->sellerId) return true;

        return false;
    }

    /**
     * @brief 错误或者成功输出
     *
     * @param string $status
     * @param stirng $url
     * @param string $msg
     * @param string $method
     * @param array $params
     *
     * @return string
     */
    public function splash($status='success',$url=null,$msg=null,$ajax=true){
        $status = ($status == 'failed') ? 'error' : $status;
        //如果需要返回则ajax
        if($ajax==true||request::ajax()){
            return response::json(array(
                $status => true,
                'message'=>$msg,
                'redirect' => $url,
            ));
        }

        if($url && !$msg){//如果有url地址但是没有信息输出则直接跳转
            return redirect::to($url);
        }
    }

    public function isValidMsg($status)
    {
        $status = ($status == 'true') ? 'true' : 'false';
        $res['valid'] = $status;
        return response::json($res);
    }

    /**
     * @brief 商家中心页面加载，默认包含商家中心头、尾、导航、和左边栏
     *
     * @param string $view  html路径
     * @param stirng $app   html路径所在app
     *
     * @return html
     */
    public function page($view, $pagedata = array())
    {
        $sellerData = shopAuth::getSellerData();
        $sellerData['shop_logo'] = $this->shopInfo['shop_logo'];
        $sellerData['shoptype'] = $this->shopInfo['shoptype'];
        if($this->shopId)
        {
            $roleInfo = app::get('topshop')->rpcCall('account.shop.roles.get',['role_id'=>$sellerData['role_id'],'shop_id'=>$this->shopId]);
            if($roleInfo)
            {
                $sellerData['role_name'] = $roleInfo['role_name'];
            }
        }

        $topshopPageParams['seller'] = $sellerData;
        $pagedata['shopId'] = $this->shopId;
        $topshopPageParams['path'] = $this->runtimePath;//设置面包屑

        if( $this->contentHeaderTitle )
        {
            $topshopPageParams['contentTitle'] = $this->contentHeaderTitle;
        }

        //当前页面调用的action
        $topshopPageParams['currentActionName']= route::currentActionName();

        $allMenu = $this->__getMenu();
        if( $allMenu && !$this->nomenu )
        {
            $topshopPageParams['allMenu'] = $allMenu;
        }

        //获取logo
        $logo = app::get('site')->getConf('site.logo');
        $pagedata['logo'] = $logo;
        $topshopPageParams['view'] = $view;

        $pagedata['topshop'] = $topshopPageParams;
        $pagedata['system_site_name'] = app::get('site')->getConf('site.name');

        $pagedata['icon'] =  app::get('topshop')->res_url.'/favicon.ico';
        $pagedata['topshopNewSetup'] = $this->topshopNewSetup;
        $pagedata['openNewWapdecorate'] = $this->openNewWapdecorate;
        $pagedata['openNewAppdecorate'] = $this->openNewAppdecorate;

        if( !$this->tmplName )
        {
            $this->tmplName = 'topshop/tmpl/page.html';
        }

        return view::make($this->tmplName, $pagedata);
    }

    public function set_tmpl($tmpl)
    {
        $tmplName = 'topshop/tmpl/'.$tmpl.'.html';
        $this->tmplName = $tmplName;
    }

    /**
     * @brief 获取到商家中心的导航菜单和左边栏菜单
     *
     * @return array $res
     */
    private function __getMenu()
    {
        $currentPermission = shopAuth::getSellerPermission();
        $defaultActionName = route::currentActionName();

        $shopMenu = config::get('shop');
        $subdomainSetting = config::get('app.subdomain_enabled');
        $trafficsetting = config::get('stat.disabled');

        foreach( (array)$shopMenu as $menu => $row )
        {
            //不需要显示菜单
            if( $row['display'] === false ) continue;

            if( !$currentPermission || !$navbar[$menu] )
            {
                $row['display'] = false;
                $navbar[$menu] = $row;
                $navbar[$menu]['default'] = ($row['action'] == $defaultActionName && $navbar[$menu]);
                unset($navbar[$menu]['menu']);
            }

            foreach( (array)$row['menu'] as $k=>$params )
            {
                //判断是否安装财务
                if(!app::get('sysfinance')->is_installed() && strstr($params['action'], 'guaranteeMoney_list'))
                {
                    $params['display'] = false;
                }

                //判断是否开启二级域名
                //平台未开启二级菜单功能，二级菜单不显示
                if(!$subdomainSetting && $params['url'] =="subdomain.html")
                {
                    $params['display'] = false;
                }

                //标记一下，临时解决im的菜单问题
                if(!app::get('sysim')->is_installed() && strstr($params['action'], 'im_webcall'))
                {
                    $params['display'] = false;
                }
                //标记一下，临时解决im的菜单问题--这里结束

                //是否显示wap端店铺装修菜单
                if(!$this->topshopNewSetup && $this->openNewWapdecorate !="open" && $params['url'] == 'new_decorate/edit.html')
                {
                    $params['display'] = false;
                }
                if(!$this->topshopNewSetup && $this->openNewAppdecorate !="open" && $params['url'] == 'app_decorate/edit.html')
                {
                    $params['display'] = false;
                }
                if($this->topshopNewSetup && $params['url'] == 'wapdecorate.html' || ($this->openNewAppdecorate == "open" && $this->openNewWapdecorate == 'open') && $params['url'] == 'wapdecorate.html')
                {
                    $params['display'] = false;
                }

                //! $currentPermission 店主 或者子账号有该菜单权限
                if( !$currentPermission || (in_array($params['as'],$currentPermission) ))
                {
                    //如果为当前的路由则高亮
                    if( $params['action'] == $defaultActionName && $navbar[$menu] )
                    {
                        $navbar[$menu]['default'] = true;
                        $params['default'] = true;
                    }
                    $navbar[$menu]['display'] = true;

                    //二级菜单 不需要显示菜单
                    if( !$params['display'] ) continue;

                    $navbar[$menu]['menu'][$k] = $params;
                }
            }
        }
        return $navbar;
    }

    public function setShortcutMenu($data)
    {
        return app::get('topshop')->setConf('shortcutMenuAction.'.$this->sellerId, $data);
    }

    public function getShortcutMenu()
    {
        return app::get('topshop')->getConf('shortcutMenuAction.'.$this->sellerId);
    }

    /**
     * 用于指示商家操作者的标志
     * @return array 商家登录用户信息
     */
    public function operator()
    {
        return array(
            'user_type' => 'seller',
            'op_id' => pamAccount::getAccountId(),
            'op_account' => pamAccount::getLoginName(),
        );
    }

    /**
     * 记录商家操作日志
     *
     * @param $lang 日志内容
     * @param $status 成功失败状态
     */
    protected final function sellerlog($memo)
    {
        // 开启了才记录操作日志
        if ( SELLER_OPERATOR_LOG !== true ) return;

        if(!$this->shopId)
        {
            $shopId = app::get('topshop')->rpcCall('shop.get.loginId',array('seller_id'=>pamAccount::getAccountId()),'seller');
        }
        else
        {
            $shopId = $this->shopId;
        }
        $queue_params = array(
            'seller_userid'   => pamAccount::getAccountId(),
            'seller_username' => pamAccount::getLoginName(),
            'shop_id'         => $shopId,
            'created_time'    => time(),
            'memo'            => $memo,
            'router'          => request::fullurl(),
            'ip'              => request::getClientIp(),
        );
        return system_queue::instance()->publish('system_tasks_sellerlog', 'system_tasks_sellerlog', $queue_params);
    }

}
