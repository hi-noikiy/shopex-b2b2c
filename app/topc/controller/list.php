<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_list extends topc_controller {

    /**
     * 每页搜索多少个商品
     */
    public $limit = 20;

    /**
     * 最多搜索前100页的商品
     */
    public $maxPages = 100;

    /**
     * 设置进入列表页的初始条件，用于查询渐进式筛选
     * 临时用于虚拟分类
     */
    private function __setInitFilter(&$params)
    {
        if( input::get('virtual_cat_id') )
        {
            $catinfo = app::get('topc')->rpcCall('category.virtualcat.info',array('virtual_cat_id'=>intval($params['virtual_cat_id']),'platform'=>'pc'));
            $initFilter = unserialize($catinfo['filter']);
            $initFilter['virtual_cat_id'] = $params['virtual_cat_id'];
            if( $initFilter['brand_id'] )
            {
                $initFilter['init_brand_id'] = implode(',',$initFilter['brand_id']);
                unset($initFilter['brand_id']);
            }

            if( $params['cat_id'] && !$initFilter['cat_id'] )
            {
                $initFilter['cat_id'] = $params['cat_id'];
            }

            if( $params['brand_id'] )
            {
                $initFilter['brand_id'] = is_array($params['brand_id']) ? implode(',',$params['brand_id']) : $params['brand_id'];
            }
            if($initFilter['search_keywords'] && !$params['search_keywords']){
                $params['search_keywords'] = $initFilter['search_keywords'];
            }

            $params = array_merge($initFilter, $params);
        }
        elseif( $params['voucher_id'] )
        {
            //获取到购物券信息
            $limitCat = app::get('topc')->rpcCall('promotion.voucher.get',['voucher_id'=>$params['voucher_id'],'fields'=>'limit_cat']);
            if( $limitCat['limit_cat'] )
            {
                $initFilter['limit_cat'] = $limitCat['limit_cat'];
                //如果没有指定一级类目，则默认选中第一个一级类目
                $catId = (!$params['lv1_cat_id']) ? $limitCat['limit_cat'][0] : $params['lv1_cat_id'];

                //如果没有指定店铺ID，则默选中参加类目的店铺
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

                //获取到第一个类目的所以三级类目
                $catList  = app::get('topc')->rpcCall('category.cat.get',['cat_id'=>$catId,'fields'=>'cat_id,cat_name']);
                $catIds = array();
                foreach( $catList as $lv2Row )
                {
                    foreach( $lv2Row['lv2'] as $value )
                    {
                        $catIds = array_merge($catIds, array_column($value['lv3'], 'cat_id'));
                    }
                }

                if( !$params['cat_id'] )
                {
                    $initFilter['cat_id'] = $params['cat_id'] = implode(',',$catIds);
                }
                else
                {
                    $initFilter['cat_id'] = $params['cat_id'];
                }

                $initFilter['brand_id'] = is_array($params['brand_id']) ? implode(',',$params['brand_id']) : $params['brand_id'];
            }
        }
        elseif( $params['search_keywords'] )
        {
            $initFilter['search_keywords'] = $params['search_keywords'];
            $initFilter['cat_id']          = $params['cat_id'];
            $initFilter['brand_id']        = is_array($params['brand_id']) ? implode(',',$params['brand_id']) : $params['brand_id'];
        }
        elseif( $params['cat_id'] )
        {
            $initFilter['cat_id'] = $params['cat_id'];
        }

        return $initFilter;
    }

    /**
     * 获取查询商品条件
     */
    private function __getDecodeFilter()
    {
        $objLibFilter              = kernel::single('topc_item_filter');
        $postdata                  = input::get();
        $params                    = $objLibFilter->decode($postdata);
        $params['use_platform']    = '0,1';
        $params['search_keywords'] = parseSearchKeyWord(trim($params['search_keywords']));
        return $params;
    }

    public function index()
    {
        $this->setLayoutFlag('gallery');
        $objLibFilter = kernel::single('topc_item_filter');

        $params     = $this->__getDecodeFilter();
        //判断自营  自营是1，非自营是0
        if($params['is_selfshop']=='1')
        {
            $pagedata['isself'] = '0';
        }
        else
        {
            $pagedata['isself'] = '1';
        }

        //已选择的搜索条件
        $pagedata['activeFilter'] = $params;

        $initFilter = $this->__setInitFilter($params);
        if( !$initFilter )
        {
            return redirect::back();
        }

        $catinfo = app::get('topc')->rpcCall('category.virtualcat.info',array('virtual_cat_id'=>intval($params['virtual_cat_id']),'platform'=>'pc'));

        //默认图片
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        //搜索或者筛选获取商品
        $searchParams = $this->__preFilter($params);

        //根据条件搜索出最多商品的分类，进行显示渐进式筛选项
        if( !$catinfo && $initFilter['virtual_cat_id'])
        {
            $filterItems = [];
        }
        else
        {
            $filterItems = app::get('topc')->rpcCall('item.search.filterItems',$initFilter);
        }
        //渐进式筛选的数据
        $pagedata['screen'] = $filterItems;

        //如果是购物券有一级类目限制
        if( $initFilter['limit_cat'] )
        {
            $catData = app::get('topc')->rpcCall('category.cat.get.info',['cat_id'=>$initFilter['limit_cat'],'level'=>1,'fields'=>'cat_id,cat_name,child_count']);
            $pagedata['screen']['lv1_cat'] = $catData;
            $pagedata['activeFilter']['lv1_cat_id'] = $pagedata['activeFilter']['lv1_cat_id'] ?: $initFilter['limit_cat'][0];
        }

        if( $filterItems && !$filterItems['props'])
        {
            unset($pagedata['activeFilter']['prop_index']);
            unset($searchParams['prop_index']);
        }

        //已有的搜索条件
        $tmpFilter = $pagedata['activeFilter'];
        unset($tmpFilter['pages']);
        $pagedata['filter'] = $objLibFilter->encode($tmpFilter);

        //面包屑数据
        $breadcrumb = array();
        if($searchParams['cat_id'] )
        {
            $cat = app::get('topc')->rpcCall('category.cat.get.data',array('cat_id'=>intval($searchParams['cat_id'])));
            $breadcrumb = array(
                ['url'=>url::action('topc_ctl_topics@index',array('cat_id'=>$cat['lv1']['cat_id'])),'title'=>$cat['lv1']['cat_name']],
                ['url'=>url::action('topc_ctl_topics@index',array('cat_id'=>$cat['lv2']['cat_id'])),'title'=>$cat['lv2']['cat_name']],
                ['url'=>url::action('topc_ctl_list@index',array('cat_id'=>$cat['lv3']['cat_id'])),'title'=>$cat['lv3']['cat_name']],
            );
            if($searchParams['brand_id'])
            {
                $brands = app::get('topc')->rpcCall('category.brand.get.list',array('brand_id'=>$searchParams['brand_id'],'fields'=>'brand_id,brand_name'));
                $title = (count($brands) >1) ? "品牌：" : '';
                foreach($brands as $brand)
                {
                    $title .= $brand['brand_name']."、";
                }
                $title = rtrim($title,"、");
                $breadcrumb[] = ['url'=>'','title'=>$title];
            }
        }
        elseif($searchParams['virtual_cat_id'])
        {
            $virtualcat = app::get('topc')->rpcCall('category.virtualcat.getData',array('virtual_cat_id'=>intval($searchParams['virtual_cat_id']),'platform'=>'pc'));
            $breadcrumb = array(
                ['url'=>url::action('topc_ctl_topics@index',array('virtual_cat_id'=>$virtualcat['lv1']['virtual_cat_id'])),'title'=>$virtualcat['lv1']['virtual_cat_name']],
                ['url'=>url::action('topc_ctl_topics@index',array('virtual_cat_id'=>$virtualcat['lv2']['virtual_cat_id'])),'title'=>$virtualcat['lv2']['virtual_cat_name']],
                ['url'=>url::action('topc_ctl_list@index',array('virtual_cat_id'=>$virtualcat['lv3']['virtual_cat_id'])),'title'=>$virtualcat['lv3']['virtual_cat_name']],
            );
        }

        if( $params['voucher_id'] )
        {
            $breadcrumb = array(
                ['url'=>'','title'=>'全部结果'],
                ['url'=>'','title'=>'购物券'],
            );
        }

        if($searchParams['search_keywords'])
        {
            $breadcrumb = array(
                ['url'=>'','title'=>'全部商品'],
                ['url'=>'','title'=>$searchParams['search_keywords']],
            );
        }
        $pagedata['breadcrumb'] = $breadcrumb;

        $searchParams['fields'] = 'item_id,title,image_default_id,price,promotion';
        try
        {
            if($searchParams['virtual_cat_id'] && !$catinfo)
            {
                $itemsList['list'] = [];
                $itemsList['total_found'] = 0;
            }else{
                $itemsList = app::get('topc')->rpcCall('item.search',$searchParams);
            }
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
            $activityItemList = app::get('topc')->rpcCall('promotion.activity.item.list',$activityParams);
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

        //分页
        $pagedata['pagers'] = $this->__pages($params['pages'], $pagedata['count'], $pagedata['filter']);

        return $this->page('topc/list/index.html', $pagedata);
    }

    /**
     * 将post过的数据转换为搜索需要的参数
     *
     * @param array $params
     */
    private function __preFilter($params)
    {
        $searchParams = $params;
        $searchParams['page_no'] = ($params['pages'] >=1 || $params['pages'] <= 100) ? $params['pages'] : 1;
        $searchParams['page_size'] = $this->limit;

        $searchParams['approve_status'] = 'onsale';
        $searchParams['buildExcerpts'] = true;

        if( $searchParams['brand_id'] && is_array($searchParams['brand_id']) )
        {
            $searchParams['brand_id'] = implode(',', $searchParams['brand_id']);
        }

        if( $searchParams['prop_index'] && is_array($searchParams['prop_index']) )
        {
            $searchParams['prop_index'] = implode(',', $searchParams['prop_index']);
        }

        //排序
        if( !$params['orderBy'] )
        {
            $params['orderBy'] = 'sold_quantity desc';
        }
        $searchparams['orderBy'] = $params['orderBy'];

        return $searchParams;
    }

    /**
     * 分页处理
     * @param int $current 当前页
     * @return int $total  总页数
     * @return array $filter 查询条件
     *
     * @return $pagers
     */
    private function __pages($current, $total, $filter)
    {
        //处理翻页数据
        $current = ($current || $current <= 100 ) ? $current : 1;
        $filter['pages'] = time();

        if( $total > 0 ) $totalPage = ceil($total/$this->limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_list@index',$filter),
            'current'=>$current,
            'total'=>$totalPage,
            'token'=>time(),
        );
        return $pagers;
    }

}

