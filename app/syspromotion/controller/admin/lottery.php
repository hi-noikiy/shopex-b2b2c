<?php

/**
 * lottery.php
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syspromotion_ctl_admin_lottery extends desktop_controller {

    public function index()
    {
        return $this->finder('syspromotion_mdl_lottery',array(
            'actions'=>array(
                array(
                    'label'=>app::get('syspromotion')->_('添加转盘抽奖'),
                    'target'=>'dialog::{ title:\''.app::get('sysuser')->_('添加转盘抽奖').'\', width:850, height:600}',
                    'href'=>'?app=syspromotion&ctl=admin_lottery&act=edit',
                ),
            ),
            'title' => app::get('syspromotion')->_('转盘抽奖'),
            'use_buildin_delete' => false,
        ));
    }

    // 编辑转盘活动
    public function edit()
    {
        $pagedata = array();

        $postData = input::get();

        if($postData['lottery_id'])
        {
            $apiParams = array(
                'lottery_id'=>$postData['lottery_id'],
            );

            $pagedata = app::get('syspromotion')->rpcCall('promotion.lottery.get', $apiParams);
            if($pagedata['status'] == 'active'){

                foreach ($pagedata['lottery_rules'] as $key => $value) {
                    if($value['bonus_type'] == 'hongbao' && $value['hongbao_id']){
                        $hongbaoData = app::get('syspromotion')->rpcCall('promotion.hongbao.get',array('hongbao_id'=>$value['hongbao_id'],'fields'=>'hongbao_id,name,hongbao_type',));
                        $pagedata['hongbaoname'] = $hongbaoData['name'];
                    }
                }
                return $this->page('syspromotion/lottery/info.html', $pagedata);
            }
        }
        return $this->page('syspromotion/lottery/edit.html', $pagedata);
    }

    public function getHongbao(){
        $hongbao_id = input::get();

        $params = array(
            'hongbao_id'=>$hongbao_id,
            'fields'=>'*',
        );
        $hongbaoInfo = app::get('syspromotion')->rpcCall('promotion.hongbao.get',$params);

        return json_encode($hongbaoInfo);
    }

    public function setPrize()
    {
        $pagedata['bunus_type'] = array(
            'none' => '未中奖',
            'hongbao' => '红包',
            'point' => '积分',
            'custom' => '自定义',
        );
        return $this->page('syspromotion/lottery/prize_select.html', $pagedata);
    }

    public function getPrizeSettingHtml(){
        $data = input::get();
        $pagedata['type'] = $data['type'];
        $pagedata['maxkey'] = $data['maxKey'];

        return view::make('syspromotion/lottery/prizesetting.html',$pagedata);
    }

    public function chooseRedpacket()
    {
        $pagedata['bunus_type'] = array(
            'none' => '未中奖',
            'hongbao' => '红包',
            'point' => '积分',
            'custom' => '自定义',
        );

         $params = array(
            'page_no'=>1,
            'page_size'=>100,
            'fields'=>'hongbao_id,name,hongbao_list,get_start_time,get_end_time',
        );

        $hongbao = app::get('syspromotion')->rpcCall('promotion.hongbao.list.get',$params);
        $pagedata['hongbaoList'] = $hongbao['list'];
        return $this->page('syspromotion/lottery/hongbao_select.html', $pagedata);
    }

    // 保存数据
    public function save()
    {
        $data = input::get();

        $this->begin("?app=syspromotion&ctl=admin_lottery&act=index");
        try
        {
            if(!$data['lottery_id']){
                $objMdllottery = app::get('syspromotion')->model('lottery');
                $lotteryList = $objMdllottery->getList('*',array('status'=>'active'));
                if(count($lotteryList)>6){
                    throw new Exception(app::get('syspromotion')->_('最多同时开启5条活动！'));
                }
            }
            kernel::single('syspromotion_lottery')->save($data);
            $this->adminlog("编辑转盘活动{$apiParams['name']}", 1);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }
        $this->end(true);
    }


    //获奖详细记录
    public function logDetail(){
        $data = input::get();

        return $this->finder('syspromotion_mdl_lottery_result',array(
            'title'=>app::get('syspromotion')->_('抽奖结果列表'),
            'use_buildin_delete'=>false,
            'use_buildin_filter'=>true,
            'base_filter'=>array('lottery_id'=>$data['lottery_id']),
        ));
    }

    // //删除活动及获奖记录
    // public function doDelete()
    // {
    //     $ids = input::get();
    //     $this->begin('?app=syspromotion&ctl=admin_lottery&act=index');
    //     $model = app::get('syspromotion')->model('lottery');
    //     try
    //     {
    //         kernel::single('syspromotion_lottery')->delete($ids);
    //         $this->adminlog("删除转盘活动{$ids}",1);
    //         redis::scene('syspromotion')->del('lottery_result_'.$ids);
    //     }
    //     catch(Exception $e)
    //     {
    //         $this->adminlog("删除转盘活动{$ids}",0);
    //         $msg = $e->getMessage();
    //         $this->end(false,$msg);
    //     }
    //     $this->end(true,$msg);

    // }


    //更新活动状态
    public function updateStatus(){
        $data = input::get();
        $this->begin();
        if(!$data['status']){
            $this->end(true);
        }

        try{
            app::get('syspromotion')->rpcCall('promotion.lottery.updateStatus',array('lottery_id'=>$data['lottery_id'],'status'=>$data['status']));
        }
        catch( Exception $e)
        {
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }
        $this->end(true, app::get('syspromotion')->_('更改状态成功！'));
    }

    public function _views()
    {
        $subMenu = array(
                0 => array(
                        'label' => app::get('syspromotion')->_('全部'),
                        'optional' => false
                ),
        );

        return $subMenu;
    }

}

