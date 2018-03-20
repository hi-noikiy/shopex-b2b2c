<?php
/**
 * 接口作用说明
 * item.search.filterItems
 */
class sysitem_api_search_filterItems {

    public $apiDescription = '根据搜索条件，列出渐进式的筛选项';

    public $use_strict_filter = true; // 是否严格过滤参数

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        $return['params'] = array(
            'item_id'     => ['type'=>'string','valid'=>'','description'=>'商品id，多个id用，隔开','example'=>'2,3,5,6'],
            'shop_id'     => ['type'=>'int','valid'=>'','description'=>'店铺id','example'=>''],
            'shop_cat_id' => ['type'=>'int','valid'=>'','description'=>'店铺自有类目id','example'=>''],
            'cat_id'      => ['type'=>'string','valid'=>'','description'=>'商城类目id,多个用逗号隔开','example'=>''],
            'brand_id'    => ['type'=>'int','valid'=>'','description'=>'商城筛选的品牌ID','example'=>''],
            'init_brand_id'   => ['type'=>'int','valid'=>'','description'=>'商城已确定的品牌ID','example'=>''],
            'approve_status'  => ['type'=>'string','valid'=>'','description'=>'商品上架状态','example'=>''],
            'search_keywords' => ['type'=>'string','valid'=>'','description'=>'搜索商品关键字','example'=>''],
            'use_platform'    => ['type'=>'string','valid'=>'','description'=>'商品使用平台(0=全部支持,1=仅支持pc端,2=仅支持移动端)如果查询不限制平台，则不需要传入该参数','example'=>'1'],
            'min_price' => ['type'=>'int','valid'=>'numeric','description'=>'搜索最小价格','example'=>'','default'=>''],
            'max_price' => ['type'=>'int','valid'=>'numeric','description'=>'搜索最大价格','example'=>'','default'=>''],
        );

        return $return;
    }

    private function __getFilter($params)
    {
        $filterCols = ['item_id','brand_id','shop_id','shop_cat_id','cat_id','init_brand_id','search_keywords','use_platform','approve_status'];
        foreach( $filterCols as $col )
        {
            if( $params[$col] )
            {
                $params[$col] = trim($params[$col]);

                if( in_array($col,['item_id','brand_id','init_brand_id','use_platform', 'cat_id','shop_id']) )
                {
                    $params[$col] = explode(',',$params[$col]);
                }
                $filter[$col] = $params[$col];
            }
        }

        if($params['max_price'] && $params['min_price'])
        {
            $filter['price|between'] = [$params['min_price'],$params['max_price']];
        }
        elseif($params['max_price'] && !$params['min_price'])
        {
            $filter['price|sthan'] = $params['max_price'];
        }
        elseif (!$params['max_price'] && $params['min_price'])
        {
            $filter['price|bthan'] = $params['min_price'];
        }

        return $filter;
    }

    public function get($params)
    {
        $objMdlItem = app::get('sysitem')->model('item');

        $params['approve_status'] = 'onsale';

        if( !$params['search_keywords'] && $params['cat_id'])
        {
            //发包临时修改
            if( count(explode(',',$params['cat_id'])) == 0 )
            {
                $catId = $params['cat_id'];
                $catInfo = app::get('sysitem')->rpcCall('category.cat.get.info',array('cat_id'=>$catId,'fields'=>'cat_name'));
                $filterItems['keyword'] = $catInfo[$catId]['cat_name'];
            }
        }

        $catSearchParams = $params;

        if( $catSearchParams['init_brand_id'] && !$catSearchParams['brand_id'])
        {
            $catSearchParams['brand_id'] = $catSearchParams['init_brand_id'];
            unset($catSearchParams['init_brand_id']);
        }

        $catList = kernel::single('search_object')->instance('item')
            ->groupBy('cat_id')
            ->countColumn('item_id','_count')
            ->orderBy('_count desc')
            ->search('cat_id',$this->__getFilter($catSearchParams));

        $catIds = implode(',',array_column($catList['list'],'cat_id'));

        if( !$catIds && $params['cat_id'] )
        {
            $catIds = $params['cat_id'];
        }

        if( $catIds )
        {
            $catData = app::get('sysitem')->rpcCall('category.cat.get.info',array('cat_id'=>$catIds,'fields'=>'cat_name'));
            $filterItems['cat'] = $catData;

            if( count($catList['list']) == 1 )
            {
                $catId = $catList['list'][0]['cat_id'];
                if( ! $params['cat_id'] )
                {
                    unset($filterItems['cat']);
                }
            }
        }

        if( $params['search_keywords'] )
        {
            $shopParams['shop_name'] = $params['search_keywords'];
            $shopinfo = app::get('sysitem')->rpcCall('shop.get.search',$shopParams);
            //去除被关闭的店铺
            $shopList = array();
            foreach ($shopinfo as $shop)
            {
                if($shop['status'] !='dead')
                {
                    $shopList[] = $shop;
                }
            }

            $filterItems['shopInfo'] = $shopList[0];
            $filterItems['keyword'] = $params['search_keywords'];
        }

        $brandSearcParams = $params;
        unset($brandSearcParams['brand_id']);
        if( $brandSearcParams['init_brand_id'] )
        {
            $brandSearcParams['brand_id'] = $brandSearcParams['init_brand_id'];
            unset($brandSearcParams['init_brand_id']);
        }

        $brandIdArr = kernel::single('search_object')->instance('item')
            ->page(0,100)
            ->groupBy('brand_id')
            ->search('brand_id',$this->__getFilter($brandSearcParams));

        if( $brandIdArr['list'] )
        {
            $brandFilter['brand_id'] = implode(',', array_column($brandIdArr['list'],'brand_id'));
            $brandFilter['fields'] = 'brand_id,brand_name';
            $brand = app::get('sysitem')->rpcCall('category.brand.get.list', $brandFilter);
            if( $brand )
            {
                $filterItems['brand'] = $brand;
            }
        }

        if( $catId )
        {
            $props = kernel::single('syscategory_data_props')->getNatureProps($catId);
        }

        if( $props )
        {
            foreach( $props as $key=>$row )
            {
                foreach( $row['prop_value'] as $k=>$value )
                {
                    $props[$key]['prop_value'][$k]['prop_index'] = $row['prop_id'].'_'.$value['prop_value_id'];
                }
            }
            $filterItems['props'] = $props;
        }

        $filterItems['cat_id'] = $catId;

        return $filterItems;
    }
}

