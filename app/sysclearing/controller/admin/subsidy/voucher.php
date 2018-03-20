<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysclearing_ctl_admin_subsidy_voucher extends desktop_controller {

    protected $limit = 20;

    /**
     * 商家购物券补贴单明细列表
     */
    public function index()
    {
        $getData = input::get();
        $searchParams = array();

        // 表单搜索
        $actUrl = '?app=sysclearing&ctl=admin_subsidy_voucher&act=index';
        $this->pagedata['form_url'] = $actUrl;

        if($this->has_permission('export')){
            $top_extra_view = array('sysclearing'=>'sysclearing/admin/voucher/index_header.html');
        }

        $this->pagedata ['subsidy_type'] = [
            '-1' => '全部',
            '1' => '未发放',
            '2' => '已发放',
        ];
        if (isset ($getData ['issearch']) && $getData ['issearch'] == 1)
        {
            // 搜索变量
            if( $getData ['shop_name'] )
            {
                $this->pagedata['shop_name'] = $getData ['shop_name'];
                $shopResult = app::get('sysclearing')->rpcCall('shop.get.search',['shop_name'=>$getData ['shop_name'], 'fields'=>'shop_id']);
                if( $shopResult )
                {
                    $shopIds = array_column($shopResult,'shop_id');
                    $searchParams['shop_id|in'] = $shopIds;
                }
                else
                {
                    $searchParams['shop_id'] = '-1';
                }
            }

            if( $getData ['voucher_name'] )
            {
                $this->pagedata['voucher_name'] = $getData ['voucher_name'];
                $result = app::get('sysclearing')->rpcCall('promotion.voucher.get',['voucher_name'=>$getData ['voucher_name'], 'fields'=>'voucher_id']);
                if( $result['voucher_id'] )
                {
                    $searchParams['voucher_id'] = $result['voucher_id'];
                }
                else
                {
                    $searchParams['voucher_id'] = '-1';
                }
            }

            if( $getData ['status'] != '-1' )
            {
                $searchParams['status'] = $this->pagedata['status'] = $getData ['status'];
            }
            else
            {
                $this->pagedata['status'] = $getData ['status'];
            }
        }

        $this->pagedata['export_filter'] = json_encode($searchParams);
        return $this->finder('sysclearing_mdl_vouchersubsidy',array(
                'title'=>app::get('sysclearing')->_('购物券补贴汇总'),
                'use_buildin_export' => false,
                'use_buildin_filter' => false,
                'use_buildin_delete' => false,
                'use_buildin_refresh' => false,
                'use_buildin_setcol' => false,
                'use_buildin_selectrow' =>false,
                'base_filter' =>$searchParams,
                'top_extra_view'=>$top_extra_view,
        ));
    }

    /**
     * 商家购物券补贴单明细列表
     */
    public function detail()
    {

        // 准备数据
        $getData = input::get();
        $searchParams = array();
        $actUrl = '?app=sysclearing&ctl=admin_subsidy_voucher&act=detail';
        $this->pagedata ['form_url'] = $actUrl;
        $this->pagedata ['time_start'] = strtotime (date ('Y-m-d 00:00:00', strtotime ('-1 month')));
        $this->pagedata ['time_end'] = strtotime (date ('Y-m-d 23:59:59'));
        $this->pagedata ['subsidy_type'] = [
            '-1' => '全部',
            '1' => '普通补贴',
            '2' => '退还补贴',
        ];

        if (isset ($getData ['issearch']) && $getData ['issearch'] == 1)
        {
            $this->pagedata['time_start'] = isset($getData['time_start']) ? $getData['time_start'] : $this->pagedata['time_start'];
            $this->pagedata['time_end'] = isset($getData['time_end']) ? $getData['time_end'] : $this->pagedata['time_end'];
            if( $this->pagedata['time_start'] )
            {
                $searchParams['subsidy_time|than'] = strtotime($this->pagedata['time_start']);
            }

            if( $this->pagedata['time_end'] )
            {
                $searchParams['subsidy_time|lthan'] = strtotime($this->pagedata['time_end']);
            }

            // 搜索变量
            if( $getData ['shop_name'] )
            {
                $this->pagedata['shop_name'] = $getData ['shop_name'];
                $shopResult = app::get('sysclearing')->rpcCall('shop.get.search',['shop_name'=>$getData ['shop_name'], 'fields'=>'shop_id']);
                if( $shopResult )
                {
                    $shopIds = array_column($shopResult,'shop_id');
                    $searchParams['shop_id|in'] = $shopIds;
                }
                else
                {
                    $searchParams['shop_id'] = '-1';
                }
            }

            if( $getData ['voucher_name'] )
            {
                $this->pagedata['voucher_name'] = $getData ['voucher_name'];
                $result = app::get('sysclearing')->rpcCall('promotion.voucher.get',['voucher_name'=>$getData ['voucher_name'], 'fields'=>'voucher_id']);
                if( $result['voucher_id'] )
                {
                    $searchParams['voucher_id'] = $result['voucher_id'];
                }
                else
                {
                    $searchParams['voucher_id'] = '-1';
                }
            }

            if( $getData ['status'] != '-1' )
            {
                $searchParams['type'] = $this->pagedata['status'] = $getData ['status'];
            }
            else
            {
                $this->pagedata['status'] = $getData ['status'];
            }
        }

        if($this->has_permission('export')){
            $top_extra_view = array('sysclearing'=>'sysclearing/admin/voucher/detail_header.html');
        }

        $this->pagedata['export_filter'] = json_encode($searchParams);
        return $this->finder('sysclearing_mdl_vouchersubsidy_detail',array(
                'title'=>app::get('sysclearing')->_('购物券补贴明细'),
                'use_buildin_export' => false,
                'use_buildin_filter' => false,
                'use_buildin_delete' => false,
                'use_buildin_refresh' => false,
                'use_buildin_setcol' => false,
                'use_buildin_selectrow' =>false,
                'base_filter' =>$searchParams,
                'top_extra_view'=>$top_extra_view,
        ));
    }

    public function confirm()
    {
        $voucherId = input::get('voucher_id');
        $shopId = input::get('shop_id');
        $subsidyNo = input::get('subsidy_no');
        $data = app::get('sysclearing')->rpcCall('clearing.subsidy.voucher.basic.shop', ['voucher_id'=>$voucherId, 'shop_id'=>$shopId]);
        $list = array_bind_key($data['list'], 'subsidy_no');
        $pagedata['data'] = $data;
        $pagedata['subsidy_info'] = $list[$subsidyNo];

        $apiParams = [
            'shop_id'=> $shopId,
            'fields' => 'shop_id,account_status',
        ];
        $guaranteeMoney = app::get('sysclearing')->rpcCall('sysfinance.shop.guaranteeMoney.get',$apiParams);
        if($guaranteeMoney['account_status'] =='2')
        {
            $pagedata['is_valid'] = false;

        }else{
            $pagedata['is_valid'] = true;
        }

        return $this->page ('sysclearing/admin/voucher/confirm.html', $pagedata);
    }

    public function doConfirm()
    {
        $this->begin("?app=sysclearing&ctl=admin_subsidy_voucher&act=index");
        $subsidyNo = input::get('subsidy_no');
        try
        {
            kernel::single ('sysclearing_vouchersubsidy')->doConfirm($subsidyNo);
            $this->adminlog ("确认发放购物券补贴编号:{$subsidyNo}]", 1);
        } catch ( Exception $e )
        {
            $msg = $e->getMessage();
            $this->end (false, $msg);
        }
        $this->end (true);
    }
}
