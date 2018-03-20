<?php
class syspromotion_ctl_admin_voucher_register extends desktop_controller{

    public function index()
    {
        return $this->finder('syspromotion_mdl_voucher_register',
            array(
                'title' => app::get('syspromotion')->_('报名列表'),
                'use_buildin_delete' => false,
                'use_view_tab'=>true,
                'allow_detail_popup'=>true,
            ));
    }

    public function _views()
    {
        $objMdlVoucherRegister = app::get('syspromotion')->model('voucher_register');
        $sub_menu = array(
            0=>array('label'=>app::get('syspromotion')->_('全部'),'optional'=>false,'filter'=>array()),
            1=>array('label'=>app::get('syspromotion')->_('等待审核'),'optional'=>false,'filter'=>array('verify_status'=>array('pending'),)),
            2=>array('label'=>app::get('syspromotion')->_('审核通过'),'optional'=>false,'filter'=>array('verify_status'=>array('agree'),)),
            3=>array('label'=>app::get('syspromotion')->_('审核拒绝'),'optional'=>false,'filter'=>array('verify_status'=>array('refuse'),)),
        );

        if(isset($_GET['optional_view'])) $sub_menu[$_GET['optional_view']]['optional'] = false;

        foreach($sub_menu as $k=>$v)
        {
            if($v['optional']==false)
            {
                $show_menu[$k] = $v;
                if(is_array($v['filter']))
                {
                    $v['filter'] = array_merge(array(),$v['filter']);
                }
                else
                {
                    $v['filter'] = array();
                }
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
                if($k==$_GET['view'])
                {
                    $show_menu[$k]['newcount'] = true;
                    $show_menu[$k]['addon'] = $objMdlVoucherRegister->count($v['filter']);
                }
                $show_menu[$k]['href'] = '?app=syspromotion&ctl=admin_voucher_register&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }
            elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view'])
            {
                $show_menu[$k] = $v;
            }
        }
        return $show_menu;
    }

    public function edit()
    {
        $pagedata = $this->__publicData();

        $registerInfo = app::get('syspromotion')->rpcCall('promotion.voucher.register.get', [
            'voucher_id'=> input::get('voucher_id'),
            'shop_id'   => input::get('shop_id'),
            'fields'    => '*',
        ]);
        $registerInfo['cat_id'] = explode(',',$registerInfo['cat_id']);
        $pagedata['register'] = $registerInfo;

        $apiParams['voucher_id'] = input::get('voucher_id');
        $apiParams['fields'] = '*';
        $ruleInfo = app::get('syspromotion')->rpcCall('promotion.voucher.get', $apiParams);
        $ruleInfo['used_platform'] = $this->__platform($ruleInfo['used_platform']);
        $pagedata['ruleInfo'] = $ruleInfo;

        $shopParams = array(
            'shop_id' => input::get('shop_id'),
        );
        $shopdata = app::get('topshop')->rpcCall('shop.get.detail',$shopParams);
        $pagedata['shopdata'] = $shopdata;

        $shopType = explode(',',$ruleInfo['shoptype']);
        foreach($shopType as $v)
        {
            if($pagedata['shoptype'][$v])
            {
                $pagedata['shoptype'][$v]['checked'] = true;
            }
        }

        $catData = explode(',',$ruleInfo['limit_cat']);
        foreach($catData as $id)
        {
            if($pagedata['cat'][$id])
            {
                $pagedata['cat'][$id]['checked'] = true;
            }
        }

        if( $registerInfo['verify_status'] == 'pending' && $registerInfo['valid_status'] )
        {
            return $this->page('syspromotion/voucher/register_check.html', $pagedata);
        }
        elseif( $registerInfo['verify_status'] == 'agree' && $registerInfo['valid_status'] )
        {
            return $this->page('syspromotion/voucher/register_stop.html', $pagedata);
        }
        else
        {
            return $this->page('syspromotion/voucher/register_info.html', $pagedata);
        }
    }

    public function approve()
    {
        $this->begin();
        $postdata = input::get();
        if( !trim($postdata['reason']) && $postdata['status'] == 'refuse' )
        {
            $this->end(false,'请填写驳回原因');
        }
        $apiData = array(
            'voucher_id' => (int) $postdata['voucher_id'],
            'shop_id' => (int) $postdata['shop_id'],
            'status' => $postdata['status'],
            'reason' => trim($postdata['reason']),
        );
        try{
             app::get('syspromotion')->rpcCall('promotion.voucher.register.approve', $apiData);

             $this->adminlog("购物券报名审核[{$postdata['status']}]，购物券ID：{$postdata['voucher_id']}", 1);
        } catch (\LogicException $e) {
            $this->end(false,$e->getMessage());
        }

        $this->end(true);
    }

    private function __publicData()
    {
        // 获取店铺类型
        $shopType = app::get('syspromotion')->rpcCall('shop.type.get');
        $shopType['self'] = array(
            'shop_type' => 'self',
            'name' => '运营商自营',
        );
        $pagedata['shoptype'] = $shopType;

        //获取类目
        $cat = app::get('syspromotion')->rpcCall('category.cat.get.info',array('level' =>1));
        $pagedata['cat'] = $cat;

        return $pagedata;
    }

    //停止商家购物券
    public function stop()
    {
        $this->begin();
        try
        {
            app::get('syspromotion')->rpcCall('promotion.voucher.register.stop', ['voucher_register_id'=>input::get('voucher_register_id')]);
        }
        catch(Exception $e)
        {
            $this->end(false,$e->getMessage());
        }
        $this->end(true);
    }

    private function __platform($platform)
    {
        $plat = array(
            'pc'  => ['name' => '电脑端'],
            'wap' => ['name' => 'H5端'],
            'app' => ['name' => 'APP端'],
        );
        $platForm = explode(',',$platform);
        //print_r($platForm);exit;
        foreach($platForm as $key)
        {
            $form[]=$plat[$key]['name'];
        }
        return implode(' ',$form);
    }
}
