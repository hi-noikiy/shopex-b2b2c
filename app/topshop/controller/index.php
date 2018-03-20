<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topshop_ctl_index extends topshop_controller {

	public function index()
	{
        $shopId = $this->shopId;
		//获取店铺数据
        $params = array(
            'shop_id' => $shopId,
            'fields' =>'shop_id,shop_name,close_reason,close_time,shop_type,open_time,shop_logo,shop_descript,status,brand.brand_name,cat.cat_id,cat.cat_name',
        );

		$shopInfo = app::get('topshop')->rpcCall('shop.get.detail',$params,'seller');
        //获取商家入驻信息以及入驻佣金  类目
        if($shopInfo['shop']['shop_type']!='self')
        {
        	$shopCatInfo = app::get('topshop')->rpcCall('shop.get.cat.fee',array('shop_id'=>$shopId),'seller');
			$pagedata['shopCatInfo'] = $shopCatInfo;
        }
        //店铺评分
        $pagedata['countDsr'] = app::get('topshop')->rpcCall('rate.dsr.get', ['shop_id'=>$shopId,'catDsrDiff'=>true]);

        //获取店铺通知列表top5
        $noticeParams = array(
            'shop_id'   => $shopId,
            'page_no'   => 1,
            'page_size' => 10,
            'fields'    =>'notice_title,notice_type,createtime,notice_id,shop_id',
            'orderBy' => 'createtime desc',
        );
        $pagedata['shopnotice']  = app::get('topshop')->rpcCall('shop.get.shopnoticelist',$noticeParams);

        //昨日销量排行top5
        $topParams = array('inforType'=>'item','timeType'=>'yesterday','limit'=>5);
        $topFiveItem =app::get('topshop')->rpcCall('sysstat.data.get',$topParams,'seller');
        $pagedata['topFiveItem'] = $topFiveItem['sysTrade'];

        //获取店铺昨日UV
        $date = date('Ymd', strtotime('-1 day'));
        $pagedata['uv'] = redis::scene('traffic')->hget('webuv:all_'.$shopId, $date);
        $pagedata['uvConfig'] = config::get('stat.disabled');

        //昨日新增订单数量
        $newtradeParam = [
            'shop_id'=>$shopId,
            'update_time_start' => strtotime(date('Y-m-d 00:00:00', strtotime('-1 day'))),
            'update_time_end' => strtotime(date('Y-m-d 23:59:59', strtotime('-1 day'))),
            'fields' => 'tid,payment',
        ];
        $newTrade = app::get('topshop')->rpcCall('trade.get.shop.list',$newtradeParam);
        $pagedata['countNewTrade'] = $newTrade['count'];
        $pagedata['countNewTradeFee'] = array_sum(array_column($newTrade['list'], 'payment'));
        $pagedata['avgPrice'] = round($pagedata['countNewTradeFee']/$newTrade['count'], 2);
        $pagedata['uvTransRate'] = round($newTrade['count']/$pagedata['uv']*100, 2);

		//待付款的订单数量
        $countUnTradeFee = app::get('topshop')->rpcCall('trade.count',array('shop_id'=>$shopId,'status'=>'WAIT_BUYER_PAY'));
		//待发货的订单数量
        $countReadysSend = app::get('topshop')->rpcCall('trade.count',array('shop_id'=>$shopId,'status'=>'WAIT_SELLER_SEND_GOODS'));
		//待收货的的订单数量
        $countReadyRec = app::get('topshop')->rpcCall('trade.count',array('shop_id'=>$shopId,'status'=>'WAIT_BUYER_CONFIRM_GOODS'));

        //待回复数据
        $countRateUnreply = app::get('topshop')->rpcCall('rate.list.get', ['is_reply'=>false,'fields'=>'rate_id','role'=>'seller'], 'seller');
        $pagedata['countRateUnreply'] = $countRateUnreply['total_results'];
        //退货数量
        $countRefund = app::get('topshop')->rpcCall('aftersales.list.get',['shop_id'=>$shopId,'aftersales_type'=>'REFUND_GOODS','progress'=>0,'fields'=>'tid']);
        $pagedata['countRefund'] = $countRefund['total_found'];
        //换货数量
        $countChanging = app::get('topshop')->rpcCall('aftersales.list.get',['shop_id'=>$shopId,'aftersales_type'=>'EXCHANGING_GOODS','progress'=>0,'fields'=>'tid']);
        $pagedata['countChanging'] = $countChanging['total_found'];

		//获取店铺上架商品数量
        $countShopOnsaleItem = app::get('topshop')->rpcCall('item.count',array('shop_id'=>$shopId,'status'=>'onsale'));
		//获取店铺下架商品数量
        $countShopInstockItem = app::get('topshop')->rpcCall('item.count',array('shop_id'=>$shopId,'status'=>'instock'));
        $pagedata['isGoodsExamine']  = app::get('sysconf')->getConf('shop.goods.examine');
        if($pagedata['isGoodsExamine']){
            //获取店审核失败商品数量
            $countShopRefuseItem = app::get('topshop')->rpcCall('item.count',array('shop_id'=>$shopId,'status'=>'refuse'));
            $pagedata['countShopRefuseItem'] = $countShopRefuseItem;
        }
        //获取商品咨询数量
        $countGaskItem = app::get('topshop')->rpcCall('rate.gask.count',['shop_id'=>$shopId]);
        $pagedata['countGaskItem'] = $countGaskItem['item'];

        //获取商品评价数量
        $countRate = app::get('topshop')->rpcCall('rate.count',['shop_id'=>$shopId]);
        $pagedata['countRate'] = $countRate['total']['total'];

		//昨日数据
		$yesterdayInfo = $this->getAverPrice('yesterday');
		//前日数据
		$beforInfo = $this->getAverPrice('beforday');
		//本周数据
		$weekInfo = $this->getAverPrice('week');
		//本月数据
		$monthInfo = $this->getAverPrice('month');

		//获取当前登录用户信息
		$sellData = shopAuth::getSellerData();
		//判断是否显示验证提醒
		$authNotice = false;
		if($sellData['seller_type'] == 0 && $sellData['auth_type'] == 'UNAUTH')
		{
		    $authNotice = true;
		    if(isset($_COOKIE['authNotice']) && $_COOKIE['authNotice'] === 'n')
		    {
		        $authNotice = false;
		    }
		}

        //库存报警
        $pagedata['storepolice'] = 0;
        $params['shop_id'] = $shopId;
        $params['fields'] = 'policevalue';
        $storePolice = app::get('topshop')->rpcCall('item.store.info',$params);
        if($storePolice['policevalue'])
        {
            $filter['store'] = $storePolice['policevalue'];
            $filter['shop_id'] = $shopId;
            $storepolice = app::get('topshop')->rpcCall('item.store.police.count',$filter);
            $pagedata['storepolice'] = $storepolice;
        }

		$pagedata['countShopOnsaleItem'] = $countShopOnsaleItem;
		$pagedata['countShopInstockItem'] = $countShopInstockItem;
		$pagedata['countUnTradeFee'] = $countUnTradeFee;
		$pagedata['countReadysSend'] = $countReadysSend;
		$pagedata['countReadyRec'] = $countReadyRec;
		$pagedata['shop'] = $shopInfo['shop'];
		$pagedata['shopBrandInfo'] = $shopInfo['brand'];
		$pagedata['yesterday'] = $yesterdayInfo;
		$pagedata['beforInfo'] = $beforInfo;
		$pagedata['weekInfo'] = $weekInfo;
		$pagedata['monthInfo'] = $monthInfo;
		$pagedata['authnotice'] = $authNotice;
		$url = url::action("topshop_ctl_index@index",array('shop_id'=>$shopId));
		$pagedata['qrCodeData'] = getQrcodeUri($url, 80, 0);
                $pagedata['examineSetting'] = app::get('sysconf')->getConf('shop.goods.examine');
		$this->contentHeaderTitle = app::get('topshop')->_('我的工作台');
		return $this->page('topshop/index.html', $pagedata);
	}

	/**
	 * 获取平均客单价
	 * @param data
	 * @return data
	 */
    public function getAverPrice($data)
    {
        switch($data)
        {
        case "yesterday":
            $stattime = strtotime(date("Y-m-d", time()-86400) . ' 00:00:00');
            $filterType = "nequal";
            break;
        case "beforday":
            $stattime = strtotime(date("Y-m-d", time()-86400*2) . ' 00:00:00');
            $filterType = "nequal";
            break;
        case "week":
            $stattime = strtotime(date("Y-m-d", time()-86400*7) . ' 00:00:00');
            $filterType = "bthan";
            break;
        case "month":
            $stattime = strtotime(date("Y-m-d", time()-86400*30) . ' 00:00:00');
            $filterType = "bthan";
            break;
        }
        $filter = array(
            'shop_id' => $this->shopId,
            'type' => $filterType,
            'createtime' => $stattime,
        );
        $getData = app::get('topshop')->rpcCall('stat.trade.data.count.get',$filter);

		$data = array();
        foreach ($getData as $key => $value)
        {
			$data['shop_id'] =$value['shop_id'];
			$data['new_trade'] +=$value['new_trade'];
			$data['new_fee'] +=$value['new_fee'];
			$data['ready_trade'] +=$value['ready_trade'];
			$data['ready_fee'] +=$value['ready_fee'];
			$data['ready_send_trade'] +=$value['ready_send_trade'];
			$data['ready_send_fee'] +=$value['ready_send_fee'];
			$data['already_send_trade'] +=$value['already_send_trade'];
			$data['already_send_fee'] +=$value['already_send_fee'];
			$data['cancle_trade'] +=$value['cancle_trade'];
			$data['complete_trade'] +=$value['complete_trade'];
			$data['complete_fee'] +=$value['complete_fee'];
			$data['createtime'] =$value['createtime'];
		}

		if($data['new_trade']==0)
		{
			$data['averPrice'] = 0;
		}
		else
		{
			$data['averPrice'] = number_format($data['new_fee'] / $data['new_trade'], 2, '.',' ');
		}
		return $data;
	}

	/**
	 * 判断浏览器
	 * @param null
	 * @return null
	 */
	public function browserTip()
	{
		return $this->page('topshop/common/browser_tip.html');
	}

    public function feedback()
    {
        $status = 'success';
        $msg = '提交成功';
        $validator = validator::make(
            [input::get('question'),input::get('tel'),input::get('email')],
            ['min:10|max:300','mobile','email'],
            ['提交问题最少10个字!|提交问题不能超过300个字','手机号码格式错误!', '邮箱格式错误']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',$url,$error[0],true);
            }
        }

        try
        {
            app::get('topshop')->rpcCall('feedback.add', input::get(), 'seller');
        }
        catch (LogicException $e)
        {
            $msg = $e->getMessage();
            $status = 'error';
        }
        $this->sellerlog('提交意见反馈');
        return $this->splash($status,$url,$msg,true);
    }

    public function nopermission()
    {
        $pagedata['url'] = specialutils::filterCrlf(input::get('next_page', request::server('HTTP_REFERER')));
        return view::make('topshop/permission.html',$pagedata);
    }

    public function onlySelfManagement()
    {
        $pagedata['url'] = specialutils::filterCrlf(input::get('next_page', request::server('HTTP_REFERER')));
        return view::make('topshop/onlySelfManagement.html',$pagedata);
    }

}
