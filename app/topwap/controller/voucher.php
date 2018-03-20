<?php
class topwap_ctl_voucher extends topwap_controller{

	public $limit = 20;
    public $_maxPages = 100;
	public function voucherList()
	{
		$pagedata = $this->__commonItemList();
		return $this->page("topwap/voucher/list.html",$pagedata);
	}

    public function ajaxGetVoucherItem()
    {
        $pagedata = $this->__commonItemList();
        if( !$pagedata['pagers']['total'] )
        {
            return view::make('topwap/empty/item.html',$pagedata);
        }
        return view::make('topwap/voucher/itemlist.html', $pagedata);
    }


    private function __getDecodeFilter()
    {
        $objLibFilter              = kernel::single('topwap_item_filter');
        $postdata                  = input::get();
        $params                    = $objLibFilter->decode($postdata);
        $params['use_platform']    = '0,2';
        return $params;
    }

    private function __commonItemList()
    {
        $pagedata['items'] = [];
        $params     = $this->__getDecodeFilter();
        $pagedata['activeFilter'] = $params;

        $initFilter = $this->__setInitFilter($params);
        //默认图片
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        //搜索或者筛选获取商品
        $searchParams = $this->__preFilter($params);

        $filterItems = app::get('topwap')->rpcCall('item.search.filterItems',$initFilter);
        $pagedata['screen'] = $filterItems;
        //如果是购物券有一级类目限制
        if( $initFilter['limit_cat'] )
        {
            $catData = app::get('topwap')->rpcCall('category.cat.get.info',['cat_id'=>$initFilter['limit_cat'],'level'=>1,'fields'=>'cat_id,cat_name,child_count']);
            $pagedata['screen']['lv1_cat'] = $catData;
            $pagedata['activeFilter']['lv1_cat_id'] = $pagedata['activeFilter']['lv1_cat_id'] ?: $initFilter['limit_cat'][0];
        }
        $pagedata['voucher'] = $initFilter['voucher'];

        //已有的搜索条件
        $tmpFilter = $pagedata['activeFilter'];
        unset($tmpFilter['pages']);
        $objLibFilter = kernel::single('topwap_item_filter');
        $pagedata['filter'] = $objLibFilter->encode($tmpFilter);

        if($params['catFlag']){
            $pagedata['catFlag'] = $params['catFlag'];
        }

        try
        {
            $searchParams['fields'] = 'item_id,title,image_default_id,price,promotion';
            $itemsList = app::get('topwap')->rpcCall('item.search',$searchParams);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        //检测是否有参加团购活动
        if($itemsList['list'])
        {
            $itemsList['list'] = array_bind_key($itemsList['list'],'item_id');
            $itemIds = array_keys($itemsList['list']);
            $activityParams['item_id'] = implode(',',$itemIds);
            $activityParams['status'] = 'agree';
            $activityParams['end_time'] = 'bthan';
            $activityParams['start_time'] = 'sthan';
            $activityParams['fields'] = 'activity_id,item_id,activity_tag,price,activity_price';
            $activityItemList = app::get('topwap')->rpcCall('promotion.activity.item.list',$activityParams);
            if($activityItemList['list'])
            {
                foreach($activityItemList['list'] as $key=>$value)
                {
                    $itemsList['list'][$value['item_id']]['activity'] = $value;
                    $itemsList['list'][$value['item_id']]['activity_price'] = $value['activity_price'];
                }
            }
        }

        $pagedata['items'] = $itemsList['list'];//根据条件搜索出的商品
        $pagedata['count'] = $itemsList['total_found']; //根据条件搜索到的总数
        $totalPage = ceil($pagedata['count']/$this->limit);
        $pagedata['pagers']['total'] = ($totalPage <= $this->_maxPages) ? $totalPage : $this->_maxPages;

        return $pagedata;
    }

	private function __setInitFilter(&$params)
	{
		//获取到购物券信息
            $limitCat = app::get('topwap')->rpcCall('promotion.voucher.get',['voucher_id'=>$params['voucher_id'],'fields'=>'*']);
            $this->__platform($limitCat);
            
             $initFilter['voucher'] = $limitCat;
            if( $limitCat['limit_cat'] )
            {
                $initFilter['limit_cat'] = $limitCat['limit_cat'];

                //第一个一级类目
                 if( !$params['lv1_cat_id'] )
                {
                    //第一个一级类目
                    $catId = $limitCat['limit_cat'][0];
                }
                else
                {
                    $catId = $params['lv1_cat_id'];
                }

                $params['catFlag'] = $catId ;

                if( !$params['shop_id'] )
                {
                    foreach( $limitCat['registerShop'] as $row )
                    {
                        if( in_array($catId, explode(',',$row['cat_id'])) )
                        {
                            $params['shop_id'][] = $row['shop_id'];
                        }
                    }

                    if( $params['shop_id'] )
                    {
                        $initFilter['shop_id'] = $params['shop_id'] = implode(',', $params['shop_id']);
                    }
                    else
                    {
                        $params['shop_id'] = '-1';
                    }
                }

                //如果没有指定三级类目则获取三级类目
                if( !$params['cat_id'] )
                {
                    //获取到第一个类目的所以三级类目
                    $catList  = app::get('topwap')->rpcCall('category.cat.get',['cat_id'=>$catId,'fields'=>'cat_id,cat_name']);
                    $catIds = array();
                    foreach( $catList as $lv2Row )
                    {
                        foreach( $lv2Row['lv2'] as $value )
                        {
                            $catIds = array_merge($catIds, array_column($value['lv3'], 'cat_id'));
                        }
                    }

                    $initFilter['cat_id'] = $params['cat_id'] = implode(',',$catIds);
                }
                else
                {
                    $initFilter['cat_id'] = $params['cat_id'];
                }
            }
        return $initFilter;
	}

	private function __preFilter($params)
    {
        $searchParams = $params;
        $searchParams['page_no'] = ($params['pages'] >=1 || $params['pages'] <= 100) ? $params['pages'] : 1;
        $searchParams['page_size'] = $this->limit;

        $searchParams['approve_status'] = 'onsale';
        $searchParams['buildExcerpts'] = true;

        //排序
        if( !$params['orderBy'] )
        {
            $params['orderBy'] = 'sold_quantity desc';
        }
        $searchparams['orderBy'] = $params['orderBy'];

        return $searchParams;
    }

    private function __platform(&$data)
    {
        $platform = $data['used_platform'];
        $platArr = array(
            'pc' =>'pc端',
            'wap' =>'H5端',
            'app' =>'APP端',
        );
        $data['available'] = 0;
        foreach(explode(',',$platform) as $value)
        {
            $result[] = $platArr[$value];
            if($value == "wap")
            {
                $data['available'] = 1;
            }
        }
        $data['used_platform'] = implode(' ',$result);
    }

}
