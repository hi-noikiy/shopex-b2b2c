<?php

/**
 * scratchcard.php
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syspromotion_ctl_admin_scratchcard extends desktop_controller {

    public function index()
    {
        return $this->finder('syspromotion_mdl_scratchcard', array(
                'actions'=>array(
                    array(
                        'label'=>app::get('syspromotion')->_('添加刮刮卡抽奖'),
                        'target'=>'dialog::{ title:\''.app::get('syspromotion')->_('添加刮刮卡抽奖').'\', width:1200, height:600}',
                        'href'=>'?app=syspromotion&ctl=admin_scratchcard&act=edit',
                    ),
                ),
                'title' => app::get('syspromotion')->_('刮刮卡抽奖'),
                'use_buildin_delete' => false,
            ));
    }

    public function edit(){

        $scratchcard_id = input::get('scratchcard_id');
        if( $scratchcard_id )
        {
            $scratchcard = kernel::single('syspromotion_scratchcard_object')->getScratchcard($scratchcard_id);
        }
        $scratchcard['scratchcard']['scratchcardFilter'] = time();
        if($scratchcard['scratchcard']['scratchcard_rules']){
            foreach ($scratchcard['scratchcard']['scratchcard_rules'] as $key => $rule) {
                if($rule['bonus_type'] == 'voucher' && $rule['voucher_id']){
                    $apiParams = [
                        'voucher_id' => $rule['voucher_id'],
                        'fields' => 'voucher_id, voucher_name',
                    ];
                    $voucherInfo = app::get('syspromotion')->rpcCall('promotion.voucher.get',$apiParams);
                    $scratchcard['scratchcard']['scratchcard_rules'][$key]['voucher_name'] = $voucherInfo['voucher_name'];
                }
            }
        }
        //购物券列表
        $voucher = app::get('syspromotion')->rpcCall('promotion.voucher.list.get', ['fields' => 'voucher_id, voucher_name,cansend_end_time']);
        $scratchcard['scratchcard']['voucherList'] = $voucher['list'];

        if($scratchcard['scratchcard']['status'] == 'active')
            return $this->page('syspromotion/scratchcard/info.html', $scratchcard['scratchcard']);

        return $this->page('syspromotion/scratchcard/edit.html', $scratchcard['scratchcard']);
    }

    //这里只有暂停功能
    public function updatestatus(){
        $scratchcard_id = input::get('scratchcard_id');
        $this->begin("?app=syspromotion&ctl=admin_scratchcard&act=index");
        try{
            kernel::single('syspromotion_scratchcard_object')->stopActive($scratchcard_id);
        }catch(Exception $e){
            logger::error('stop scratchcard error:'.$e->__toString());

            return $this->end(false, $e->getMessage());
        }
        return $this->end(true, app::get('syspromotion')->_('暂停成功'));
    }

    public function save(){

        $data = input::get();

        $this->begin("?app=syspromotion&ctl=admin_scratchcard&act=index");
        try{
            $scratchcard = $this->__postDataGenScratchcard($data);
            kernel::single('syspromotion_scratchcard_object')->saveScratchcard($scratchcard);
            return $this->end(true, app::get('syspromotion')->_('保存成功'));
        }catch(Exception $e){

            return $this->end(false, $e->getMessage());
        }
    }

    public function setPrize()
    {
        $pagedata['bunus_type'] = array(
            'none' => '未中奖',
            'hongbao' => '红包',
            'point' => '积分',
            'voucher' => '购物券',
        );
        return $this->page('syspromotion/scratchcard/prize_select.html', $pagedata);
    }

    public function getPrizeSettingHtml()
    {
        $data = input::get();
        $pagedata['type'] = $data['type'];
        $pagedata['maxkey'] = $data['maxKey'];
        $pagedata['scratchcardFilter'] = time();
        try
        {
            $objMdlVoucher = app::get('syspromotion')->model('voucher');
            $pagedata['voucherList'] = $objMdlVoucher->getList('voucher_id, voucher_name',['cansend_end_time|than'=>time()],0, -1,'created_time DESC');
        }catch(Exception $e)
        {
            throw $e;
        }

        return view::make('syspromotion/scratchcard/prizesetting.html',$pagedata);
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
        return $this->page('syspromotion/scratchcard/hongbao_select.html', $pagedata);
    }

    private function __postDataGenScratchcard($postData)
    {
        unset($postData['scratchcardrules']['key']);
        foreach ($postData['scratchcardrules'] as $key => $rule) {
            if($rule['bonus_type'] =='voucher'){
                $voucher = app::get('syspromotion')->rpcCall('promotion.voucher.list.get',['fields'=>'voucher_id']);
                if($voucher['pagers']['total'] <= 0) {
                    throw new Exception(app::get('syspromotion')->_('当前无可用购物券，请添加购物券！'));
                }
                if(!$rule['voucher_id']){
                    throw new Exception(app::get('syspromotion')->_('请选择购物券！'));
                }
            }
        }
        $scratchcard = [];
        $scratchcard['scratchcard_id']              = $postData['scratchcard_id'];
        $scratchcard['scratchcard_name']            = $postData['scratchcard_name'];
        $scratchcard['scratchcard_desc']            = $postData['scratchcard_desc'];
        $scratchcard['scratchcard_word']            = $postData['scratchcard_word'];
        $scratchcard['scratchcard_btn_word']        = $postData['scratchcard_btn_word'];
        $scratchcard['background_url']              = $postData['scratchard_background_url'];
        $scratchcard['status']                      = $postData['status'] == 'active' ? 'active' : 'stop';
        $scratchcard['scratchcard_type']            = $postData['scratchcard_type'];
        $scratchcard['used_platform']               = $postData['used_platform'];
        $scratchcard['scratchcard_joint_limit']     = $postData['scratchcard_joint_limit'];
        $scratchcard['scratchcard_point_num']       = $postData['scratchcard_point_num'];
        $scratchcard['scratchcard_rules']           = $postData['scratchcardrules'];
        return $scratchcard;
    }

}

