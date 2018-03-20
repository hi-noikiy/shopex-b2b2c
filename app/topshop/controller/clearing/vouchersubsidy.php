<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topshop_ctl_clearing_vouchersubsidy extends topshop_controller
{
    public $limit = 10;

    /**
     * 结算明细
     * @return
     */
    public function detail()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('购物券补贴明细');

        $filter['shop_id'] = $this->shopId;

        $postSend = utils::_filter_input(input::get());
        if($postSend['timearea'])
        {
            $pagedata['timearea'] = $postSend['timearea'];
            $timeArray = explode('-', $postSend['timearea']);
            $filter['subsidy_time_than']  = strtotime($timeArray[0]);
            $filter['subsidy_time_lthan'] = strtotime($timeArray[1]);
        }
        $page = $postSend['page'] ? $postSend['page'] : 1;

        if($postSend['type'] && $postSend['type'] != '-1')
        {
            $filter['type'] = $postSend['type'];
            $pagedata['type'] = $postSend['type'];
        }
        if( $postSend['voucher_id'])
        {
            $filter['voucher_id'] = $postSend['voucher_id'];
        }
        $filter['page_no'] = $page;
        $filter['page_size'] = $this->limit;

        $result = app::get('topshop')->rpcCall('clearing.subsidy.voucher.detail.list',$filter);
        $pagedata = $result;

        $voucherids = array_column($result['list'], 'voucher_id');
        $voucherData = app::get('topshop')->rpcCall('promotion.voucher.shop.list.get', ['shop_id'=>$this->shopId, 'voucher_id'=>$voucherids, 'fields'=>'voucher_id,voucher_name']);
        foreach( $voucherData['list'] as $row )
        {
            $pagedata['voucher'][$row['voucher_id']] = $row['voucher_name'];
        }

        $total = $result['pagers']['total'];

        //处理翻页数据
        $limit = $this->limit;
        $postSend['page'] = time();
        $link = url::action('topshop_ctl_clearing_vouchersubsidy@detail',$postSend);
        $pagedata['pagers'] = $this->__pagers($total,$page,$limit,$link);
        return $this->page('topshop/clearing/voucher/subsidydetail.html', $pagedata);
    }

    private function __pagers($count,$page,$limit,$link)
    {
        if($count>0)
        {
            $total = ceil($count/$limit);
        }
        $pagers = array(
            'link'=>$link,
            'current'=>$page,
            'use_app' => 'topshop',
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;
    }

}
