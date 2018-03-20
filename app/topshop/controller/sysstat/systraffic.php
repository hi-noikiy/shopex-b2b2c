<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topshop_ctl_sysstat_systraffic extends topshop_controller
{
    /**
     * 根据时间shopid获取商家运营情况
     * @param null
     * @return array
     */
    public function index()
    {
        $postSend = input::get();
        $type = $postSend['sendtype'];
        $objFilter = kernel::single('sysstat_data_filter');
        $params = $objFilter->filter($postSend);
        if(!$postSend || !in_array($postSend['sendtype'],array('yesterday','beforday','week','month','selecttime','select')))
        {
            $type='yesterday';
        }
        $postSend['sendtype'] = $type;
        //api参数
        $notAll = $this->__getParams('notall',$postSend,'yesterdayRank',$params);
        $data = app::get('topshop')->rpcCall('sysstat.traffic.data.get',$notAll);

        $pagedata['trafficData'] = $data['trafficData'];
        $pagedata['shop_id'] = shopAuth::getShopId();
        $pagedata['sendtype'] = $type;

        //分页
        $count = $data['count'];
        if($count>0) $total = ceil($count/$params['limit']);
        $current = $postSend['pages'] ? $postSend['pages'] : 1;
        $pagedata['limits'] = $params['limit'];
        $pagedata['pages'] = $current;
        $postSend['pages'] = time();
        $pagedata['pagers'] = array(
            'link'=>url::action('topshop_ctl_sysstat_systraffic@index',$postSend),
            'current'=>$current,
            'total'=>$total,
            'use_app' => 'topshop',
            'token'=>$postSend['pages']
        );

        $this->contentHeaderTitle = app::get('topshop')->_('运营报表-流量数据分析');
        return $this->page('topshop/sysstat/systraffic.html', $pagedata);
    }

    /**
     * 异步获取数据  图表用
     * @param null
     * @return array
     */

    public function ajaxTrade()
    {
        $postData = input::get();
        //api的参数
        $all = $this->__getParams('graphall',$postData,'weball');
        $datas =  app::get('topshop')->rpcCall('sysstat.traffic.data.get',$all,'seller');
        return response::json($datas);
    }

    //api参数组织
    private function __getParams($type,$postSend,$objType,$data=null)
    {
        if($type=='all')
        {
            $params = array(
                'inforType'=>$objType,
                'timeType'=>$postSend['sendtype'],
                'starttime'=>$postSend['starttime'],
                'endtime'=>$postSend['endtime'],
                'dataType'=>$type,
                'shop_id' => shopAuth::getShopId(),
            );
        }
        elseif($type=='notall')
        {
            $params = array(
                'inforType'=>$objType,
                'timeType'=>$postSend['sendtype'],
                'starttime'=>$postSend['starttime'],
                'endtime'=>$postSend['endtime'],
                'dataType'=>$type,
                'limit'=>intval($data['limit']),
                'start'=>intval($data['start']),
                'shop_id' => shopAuth::getShopId(),
            );
        }
        elseif($type=='graphall')
        {
            $params = array(
                'inforType'=>$objType,
                'tradeType'=>$postSend['trade'],
                'timeType'=>$postSend['sendtype'],
                'starttime'=>$postSend['starttime'],
                'endtime'=>$postSend['endtime'],
                'dataType'=>$type,
                'shop_id' => shopAuth::getShopId(),
            );
        }
        return $params;
    }
}
