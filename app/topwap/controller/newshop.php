<?php
class topwap_ctl_newshop extends topwap_controller{

    public $limit = 10;
    public $maxpages = 100;

    public $orderSort = array(
        'addtime-l' => 'list_time desc',
        'addtime-s' => 'list_time asc',
        'price-l' => 'price desc',
        'price-s' => 'price asc',
        'sell-l' => 'sold_quantity desc',
        'sell-s' => 'sold_quantity asc',
    );

    public function __construct()
    {
        parent::__construct();
        $this->objLibSearch = kernel::single('topwap_item_search');
        $this->setLayoutFlag('shop');
    }

    public function index()
    {
        $shopId = input::get('shop_id');
        $pagedata = $this->__common($shopId);

        //店铺关闭后跳转至关闭页面
        if($pagedata['shopdata']['status'] == "dead")
        {
            return $this->page('topwap/shop/close_shop.html', $pagedata);
        }

        $pagedata['shopId'] = $shopId;

        // 店铺分类
        $pagedata['shopcat'] = app::get('topwap')->rpcCall('shop.cat.get',array('shop_id'=>$shopId));
        foreach($pagedata['shopcat'] as $shopCatId=>&$row)
        {
            if( $row['children'] )
            {
                $row['cat_id'] = $row['cat_id'].','.implode(',', array_column($row['children'], 'cat_id'));
            }
        }

        $pagedata['collect'] = $this->__CollectInfo($shopId);

        return $this->page('topwap/shop/index/index.html', $pagedata);
    }

    /**
     * 获取店铺详情
     *
     * @param int $shopId 店铺ID
     */
    public function shopInfo()
    {
        $shopId = input::get('shop_id');
        $pagedata['shopinfo'] = app::get('topwap')->rpcCall('shop.get',['shop_id'=>$shopId]);
        $pagedata['shopDsrData'] = $this->__getShopDsr($shopId);
        $pagedata['collect'] = $this->__CollectInfo($shopId);
        $url = url::action("topwap_ctl_shop@index",array('shop_id'=>$shopId));
        $pagedata['qrCodeData'] = getQrcodeUri($url,80,0);

        return $this->page('topwap/shop/shop_info.html',$pagedata);

    }

    public function shopItemList()
    {
        $filter = input::get();
        $itemsList = $this->__getShowItems($filter);

        $pagedata['items'] = $itemsList['list'];
        $activeFilter = $this->objLibSearch->getActiveFilter();
        $pagedata['activeFilter'] = $activeFilter;
        $pagedata['search_keywords'] = $activeFilter['search_keywords'];
        $pagedata['shopId'] = $activeFilter['shop_id'];

        //默认图片
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        //店铺分类
        $pagedata['shopcat'] = app::get('topwap')->rpcCall('shop.cat.get',array('shop_id'=>$activeFilter['shop_id']));
        foreach($pagedata['shopcat'] as $shopCatId=>&$row)
        {
            if( $row['children'] )
            {
                $row['cat_id'] = $row['cat_id'].','.implode(',', array_column($row['children'], 'cat_id'));
            }
        }
        $pagedata['pagers']['total'] = $this->objLibSearch->getMaxPages();

        return $this->page('topwap/shop/list/index.html', $pagedata);
    }

    public function ajaxGetShopItemList(){
        $filter = input::get();
        $itemsList = $this->__getShowItems($filter);
        $pagedata['items'] = $itemsList['list'];

        $activeFilter = $this->objLibSearch->getActiveFilter();
        $pagedata['activeFilter'] = $activeFilter;
        $pagedata['pagers']['total'] = $this->objLibSearch->getMaxPages();
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        return view::make('topwap/shop/list/item_list.html',$pagedata);

    }


    /**
     * 获取店铺模板页面头部共用部分的数据
     *
     * @param int $shopId 店铺ID
     * @return array
     */
    private function __common($shopId)
    {
        $shopId = intval($shopId);
        $apiParams = [
            'shop_id'    => $shopId,
            'page_name'  => 'index',
            'platform'   => 'wap',
        ];

        $data['widgets'] = app::get('topshop')->rpcCall('sysdecorate.widgets.get', $apiParams);

        foreach ($data['widgets'] as $k => $v) {
            if($v['widgets_type'] =='slider'){
                $data['widgets'][$k]['slider_first_image'] = reset($v['params']);
                $data['widgets'][$k]['slider_last_image'] = end($v['params']);
            }

            if($v['widgets_type'] == 'goods') {
                $filter = array(
                    'shop_id' => $v['shop_id'],
                    'item_id' => implode(',', $v['params']['item_id']),
                    'page_size' => 6,
                    'pages' => 1,
                );
                $data['widgets'][$k]['showitems'] = $this->__getShowItems($filter);
                $data['widgets'][$k]['itemIds'] = implode(',', $v['params']['item_id']);
            }
        }

        //店铺信息
        $shopdata = app::get('topwap')->rpcCall('shop.get',array('shop_id'=>$shopId));
        $data['shopdata'] = $shopdata;


        return $data;
    }

    //获取商品
    private function __getShowItems($filter)
    {
        $params['shop_id'] = $filter['shop_id'];
        $params['item_id'] = $filter['item_id'];
        $params['page_size'] = $filter['page_size'];
        $params['pages'] = $filter['pages'] ? $filter['pages'] : 1;
        $itemsList = $this->objLibSearch->search($params)
                          ->setItemsActivetyTag()
                          ->setItemsPromotionTag()
                          ->getData();
       
        return $itemsList;
    }

    /**
     * 获取店铺评分
     *
     * @param int $shopId 店铺ID
     */
    private function __getShopDsr($shopId)
    {
        $params['shop_id'] = $shopId;
        $params['catDsrDiff'] = true;
        $dsrData = app::get('topwap')->rpcCall('rate.dsr.get', $params);
        if( !$dsrData )
        {
            $countDsr['tally_dsr'] = sprintf('%.1f',5.0);
            $countDsr['attitude_dsr'] = sprintf('%.1f',5.0);
            $countDsr['delivery_speed_dsr'] = sprintf('%.1f',5.0);
        }
        else
        {
            $countDsr['tally_dsr'] = sprintf('%.1f',$dsrData['tally_dsr']);
            $countDsr['attitude_dsr'] = sprintf('%.1f',$dsrData['attitude_dsr']);
            $countDsr['delivery_speed_dsr'] = sprintf('%.1f',$dsrData['delivery_speed_dsr']);
        }
        $shopDsrData['countDsr'] = $countDsr;
        $shopDsrData['catDsrDiff'] = $dsrData['catDsrDiff'];
        return $shopDsrData;
    }

    //当前商品收藏和店铺收藏的状态
    private function __CollectInfo($shopId)
    {
        $userId = userAuth::id();
        $collect = unserialize($_COOKIE['collect']);

        if(in_array($shopId, $collect['shop']))
        {
            $pagedata['shopCollect'] = 1;
        }
        else
        {
            $pagedata['shopCollect'] = 0;
        }

        return $pagedata;
    }
}
