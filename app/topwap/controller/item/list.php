<?php
/**
 * 商品列表页控制器
 */
class topwap_ctl_item_list extends topwap_controller {


    public function __construct()
    {
        $this->objLibSearch = kernel::single('topwap_item_search');
    }

    public function index()
    {
        $filter = input::get();
        if($filter['virtual_cat_id']){
            $catInfo = app::get('topwap')->rpcCall('category.virtualcat.info',array('virtual_cat_id'=>intval($filter['virtual_cat_id']),'platform'=>'h5'));
            if(!$catInfo){
                $pagedata['items'] = [];
                $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
                $pagedata['pagers']['total'] = 0;
                return $this->page('topwap/item/list/index.html', $pagedata);
            }

            $initFilter = unserialize($catInfo['filter']);
            if($initFilter['brand_id']){
                $initFilter['init_brand_id'] = implode(',', $initFilter['brand_id']);
                unset($initFilter['brand_id']);
            }
        }
        if($initFilter && is_array($initFilter)){
            $filter = array_merge($initFilter,$filter);
        }
        if($filter['cat_id']){
            $pagedata['catFlag'] = $filter['cat_id'];
        }
        
        $itemsList = $this->objLibSearch->search($filter)
                          ->setItemsActivetyTag()
                          ->setItemsPromotionTag()
                          ->getData();

        $pagedata['items'] = $itemsList['list'];

        $activeFilter = $this->objLibSearch->getActiveFilter();

        //根据条件搜索出最多商品的分类，进行显示渐进式筛选项
        $filters = app::get('topc')->rpcCall('item.search.filterItems',$filter);
        if($filters['props'])
        {
            foreach ($filters['props'] as $k => $v)
            {
                $filters['props'][$k]['prop_count'] = count($v['prop_value']);
            }
        }

        $pagedata['activeFilter'] = $activeFilter;
        $pagedata['filter'] = $filters;
        $pagedata['search_keywords'] = $activeFilter['search_keywords'];

        //默认图片
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        $pagedata['pagers']['total'] = $this->objLibSearch->getMaxPages();
        $pagedata['screen'] = $this->__itemListFilter($filter);

        return $this->page('topwap/item/list/index.html', $pagedata);
    }

    public function ajaxGetItemList()
    {
        $filter = input::get();
        $itemsList = $this->objLibSearch->search($filter)
                          ->setItemsActivetyTag()
                          ->setItemsPromotionTag()
                          ->getData();

        $pagedata['items'] = $itemsList['list'];

        $activeFilter = $this->objLibSearch->getActiveFilter();
        $pagedata['activeFilter'] = $activeFilter;
        $pagedata['pagers']['total'] = $this->objLibSearch->getMaxPages();
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        if( !$pagedata['pagers']['total'] )
        {
            return view::make('topwap/empty/item.html',$pagedata);
        }

        if($pagedata['items'])
        {
            return view::make('topwap/item/list/item_list.html',$pagedata);
        }
    }
    
    // 商品搜索
    private function __itemListFilter($postdata)
    {
        $objLibFilter = kernel::single('topwap_item_filter');
        $params = $objLibFilter->decode($postdata);
        $params['use_platform'] = '0,2';
        $filterItems = app::get('topwap')->rpcCall('item.search.filterItems',$params);
        if($filterItems['shopInfo'])
        {
            $wapslider = shopWidgets::getWapInfo('waplogo',$filterItems['shopInfo']['shop_id']);
            $filterItems['logo_image'] = $wapslider[0]['params'];
        }
        
        //渐进式筛选的数据
        return $filterItems;
    }
    
}

