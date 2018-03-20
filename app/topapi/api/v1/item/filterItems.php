<?php
/**
 * topapi
 *
 * -- item.search.filterItems
 * -- 根据搜索条件，列出渐进式的筛选项
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_item_filterItems implements topapi_interface_api{

    public $apiDescription = '根据搜索条件，列出渐进式的筛选项';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        $return = array(
            'cat_id' => ['type'=>'int','valid'=>'','example'=>'1','desc'=>'平台的商品类目id','msg'=>'平台类目id必须是正整数'],
            'virtual_cat_id' => ['type'=>'int','valid'=>'','example'=>'1','desc'=>'平台的虚拟类目id','msg'=>'平台虚拟类目id必须是正整数'],
            'search_keywords' => ['type'=>'string','valid'=>'','example'=>'iphone','desc'=>'商品相关关键字','msg'=>''],
        );
        return $return;
    }

    private function __getFilter($virtual_cat_id)
    {
        $virtualCatInfo = app::get('topapi')->rpcCall('category.virtualcat.info',['virtual_cat_id'=>$virtual_cat_id, 'platform'=>'app']);

        $initFilter = unserialize($virtualCatInfo['filter']);
        $filter = $initFilter;
        if($initFilter['brand_id'] )
        {
            $filter['init_brand_id'] = implode(',',$initFilter['brand_id']);
            unset($filter['brand_id']);
        }

        return $filter;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        if($params['virtual_cat_id']){
            $filter = $this->__getFilter($params['virtual_cat_id']);
        }else{
            $filter = $params;
        }

        $filterItems = app::get('topapi')->rpcCall('item.search.filterItems',$filter);

        if($filterItems['props']){
            foreach ($filterItems['props'] as $key => $value) {
                $filterItems['props'][$key]['prop_count'] = count($value['prop_value']);
            }
        }

        if($filterItems['brand']){
            foreach ($filterItems['brand'] as $key => $value) {
                $brand['brand_id'] = $value['brand_id'];
                $brand['brand_name'] = $value['brand_name'];
                $brands[] = $brand;
            }
        }

        if($filterItems['cat']){
            foreach ($filterItems['cat'] as $value) {
                $cat['cat_id'] = $value['cat_id'];
                $cat['cat_name'] = $value['cat_name'];
                $cats[] = $cat;
            }
        }

        $pagedata['brand'] = $brands;
        $pagedata['cat'] = $cats; 
        $pagedata['props'] = $filterItems['props'];
        $pagedata['brand_count'] = count($filterItems['brand']);
        $pagedata['cat_count'] = count($filterItems['cat']);
        $pagedata['activeFilter'] = $filter;

        return $pagedata;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"brand":{"0":{"brand_id":73,"brand_name":"艾格"},"1":{"brand_id":67,"brand_name":"DAZZLE"},"2":{"brand_id":66,"brand_name":"miamia"},"3":{"brand_id":44,"brand_name":"forever21"},"4":{"brand_id":32,"brand_name":"ONLY"},"5":{"brand_id":3,"brand_name":"GAP"},"brand_count":6,"cat_count":1},"cat":[{"cat_id":33,"cat_name":"连衣裙"}],"props":[{"prop_id":3,"prop_name":"面料","type":null,"search":"select","show":"","is_def":0,"show_type":"text","prop_type":"nature","prop_memo":"","order_sort":1,"modified_time":1487068217,"disabled":0,"prop_value":[{"prop_value_id":25,"prop_id":3,"prop_value":"棉","prop_image":"","order_sort":0,"prop_index":"3_25"},{"prop_value_id":26,"prop_id":3,"prop_value":"腈纶","prop_image":"","order_sort":1,"prop_index":"3_26"},{"prop_value_id":27,"prop_id":3,"prop_value":"麻","prop_image":"","order_sort":2,"prop_index":"3_27"},{"prop_value_id":28,"prop_id":3,"prop_value":"丝绸","prop_image":"","order_sort":3,"prop_index":"3_28"},{"prop_value_id":29,"prop_id":3,"prop_value":"羊毛","prop_image":"","order_sort":4,"prop_index":"3_29"}],"prop_count":5},{"prop_id":6,"prop_name":"领型","type":null,"search":"select","show":"","is_def":0,"show_type":"text","prop_type":"nature","prop_memo":"","order_sort":1,"modified_time":1487068217,"disabled":0,"prop_value":[{"prop_value_id":38,"prop_id":6,"prop_value":"圆领","prop_image":"","order_sort":0,"prop_index":"6_38"},{"prop_value_id":39,"prop_id":6,"prop_value":"方领","prop_image":"","order_sort":1,"prop_index":"6_39"},{"prop_value_id":40,"prop_id":6,"prop_value":"高领","prop_image":"","order_sort":2,"prop_index":"6_40"},{"prop_value_id":41,"prop_id":6,"prop_value":"低领","prop_image":"","order_sort":3,"prop_index":"6_41"},{"prop_value_id":42,"prop_id":6,"prop_value":"翻领","prop_image":"","order_sort":4,"prop_index":"6_42"},{"prop_value_id":56,"prop_id":6,"prop_value":"连帽","prop_image":"","order_sort":5,"prop_index":"6_56"}],"prop_count":6}],"activeFilter":{"cat_id":"33"}}}';
    }
}

