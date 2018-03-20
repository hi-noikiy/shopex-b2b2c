<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysitem_ctl_admin_search_setting extends desktop_controller{

    public function index()
    {
        $actions = array(
            array(
                'label'=>app::get('sysitem')->_('添加权重规则'),
                'href'=>'?app=sysitem&ctl=admin_search_setting&act=editRule','target'=>'dialog::{title:\''.app::get('sysitem')->_('权重规则设置').'\',width:500,height:520}'
            ),
            array(
                'label'=>app::get('sysitem')->_('系统权重分'),
                'href'=>'?app=sysitem&ctl=admin_search_setting&act=config','target'=>'dialog::{title:\''.app::get('sysitem')->_('权重规则设置').'\',width:500,height:500}'
            ),
        );

        return $this->finder('sysitem_mdl_item_search_rule',array(
            'use_buildin_set_tag' => false,
            'use_buildin_tagedit' => true,
            'use_buildin_filter'=> true,
            'use_buildin_refresh' => true,
            'use_buildin_delete' => true,
            //'allow_detail_popup' => true,
            'title' => app::get('sysitem')->_('权重配置'),
            'actions' => $actions,
        ));

    }

    public function editRule($ruleId)
    {
        if( $ruleId )
        {
            $ruleInfo = app::get('sysitem')->model('item_search_rule')->getRow('*', ['rule_id'=>$ruleId]);
            $ruleInfo['rule'] = json_decode($ruleInfo['rule'],1);
            $pagedata['ruleInfo'] = $ruleInfo;
        }

        return view::make('sysitem/search/rule.html', $pagedata);
    }

    public function saveRule()
    {
        $this->begin();
        $data = $_POST;
        try {
            $flag = kernel::single('sysitem_search_weight')->saveSearchWeightRule($data);
            if( !empty($data['rule_id']) )
            {
                $msg = $flag ? app::get('sysitem')->_('添加商品排序权重成功') :app::get('sysitem')->_('添加商品排序权重失败');
                $this->adminlog("添加排序权重[{$data['name']}]", $flag ? 1 : 0);
            }
            else
            {
                $msg = $flag ? app::get('sysitem')->_('保存商品排序权重成功') :app::get('sysitem')->_('保存商品排序权重失败');
                $this->adminlog("编辑排序权重[{$data['name']}]", $flag ? 1 : 0);
            }
        } catch (Exception $e) {
            $this->end(false, $e->getMessage());
        }

        $this->end(true, $msg);
    }

    public function setRuleDefault($ruleId)
    {
        $this->begin('?app=sysitem&ctl=admin_search_setting&act=index');
        if($ruleId)
        {
            app::get('sysitem')->setConf('search_weight_rule', $ruleId);
        }
        $this->adminlog("修改商品排序权重默认规则", 1);
        $this->end(true, $this->app->_('设置成功'));
    }


    public function config()
    {
        $pagedata = config::get('searchweight');
        return view::make('sysitem/search/searchweight.html', $pagedata);
    }

    public function shop()
    {
        $actions = array(
            array(
                'label'=>app::get('sysitem')->_('系统权重分'),
                'href'=>'?app=sysitem&ctl=admin_search_setting&act=config','target'=>'dialog::{title:\''.app::get('sysitem')->_('权重规则设置').'\',width:500,height:500}'
            ),
        );
        return $this->finder('sysitem_mdl_search_shopweight',array(
            'use_buildin_filter'=> true,
            'use_buildin_refresh' => true,
            'use_buildin_delete' => false,
            //'allow_detail_popup' => true,
            'title' => app::get('sysitem')->_('店铺自定义权重'),
            'actions' => $actions,
        ));
    }

    public function editShopWeight($shopId)
    {
        if( $shopId )
        {
            $data = app::get('sysitem')->model('search_shopweight')->getRow('*', ['shop_id'=>$shopId]);
            $pagedata['data'] = $data;
        }

        return view::make('sysitem/search/shop_weight.html', $pagedata);
    }

    public function saveShopWeight()
    {
        $this->begin();
        $data = $_POST;
        try {
            $flag = kernel::single('sysitem_search_weight')->saveShopWeight($data);

            $msg = $flag ? app::get('sysitem')->_('保存店铺自定义权重成功') : app::get('sysitem')->_('保存店铺自定义权重失败');
            $this->adminlog("编辑店铺自定义权重[{$data['shop_name']}]", $flag ? 1 : 0);

        } catch (Exception $e) {
            $this->end(false, $e->getMessage());
        }

        $this->end(true, $msg);
    }

}
