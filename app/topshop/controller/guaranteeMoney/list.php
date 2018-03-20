<?php

/**
 * @brief
 */
class topshop_ctl_guaranteeMoney_list extends topshop_controller
{
    public $limit = 20;
    public function index()
    {
        $op_type = array(
            '*' => '全部',
            'recharge' => '充值',
            'expense' => '扣款',
        );
        $this->contentHeaderTitle = app::get('topshop')->_('店铺保证金详情');
        $shopId = $this->shopId;

        $postFilter = input::get();
        $shopdata = app::get('topshop')->rpcCall('shop.get',array('shop_id'=>$shopId),'seller');

        $guaranteeMoneyInfo = app::get('topshop')->rpcCall('sysfinance.shop.guaranteeMoney.get',['shop_id'=>$shopId]);
        $pagedata['shop'] = $guaranteeMoneyInfo;
        $pagedata['shop']['shop_logo'] = $shopdata['shop_logo'];
        $pagedata['op_type'] = $op_type;

        return $this->page('topshop/guaranteeMoney/detail.html', $pagedata);
    }

    public function search(){
        $filter = input::get();

        if($filter['op_type'] =='-1')
        {
            unset($filter['op_type']);
        }else{
            $params['op_type'] = $filter['op_type'];
        }

        if($filter['created_time'])
        {
            $times = array_filter(explode('-', $filter['created_time']));
            if($times){
                $params['created_time_start'] = strtotime($times['0']);
                $params['created_time_end'] = strtotime($times['1']);
                unset($filter['created_time']);
            }
        }
        $params['shop_id'] = $this->shopId;
        $page = $filter['pages'] ? $filter['pages'] : 1;
        $limit = $this->limit;
        $params['page_no'] = $page;
        $params['page_size'] = $limit;

        $data = app::get('topshop')->rpcCall('sysfinance.guaranteeMoney.log.get', $params);

        
        $count = $data['count'];

        $pagedata['oplist'] = $data['list'];
        $pagedata['count'] = $count;
        $pagedata['pagers'] = $this->__pager($filter,$page,$count);

        $this->contentHeaderTitle = app::get('topshop')->_('保证金交易流水查询');
        return view::make('topshop/guaranteeMoney/list.html',$pagedata);

    }

    private function __pager($postFilter,$page,$count)
    {
        $postFilter['pages'] = time();
        $total = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('topshop_ctl_guaranteeMoney_list@search',$postFilter),
            'current'=>$page,
            'use_app' => 'topshop',
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;
    }

    public function ajaxLogDetail(){
        $params['op_id'] = input::get('op_id');
        $pagedata = app::get('topshop')->rpcCall('sysfinance.guaranteeMoney.logdetail.get', $params);

        return view::make('topshop/guaranteeMoney/logdetail.html', $pagedata);
    }
}