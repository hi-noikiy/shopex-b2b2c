<?php
class topapi_api_v1_promotion_voucherItemList implements topapi_interface_api{

	/**
     * 接口作用说明
     */
    public $apiDescription = '获取购物券商品列表';
    public $use_strict_filter = true;

    public function __construct()
    {
        $this->objLibSearch = kernel::single('topapi_item_search');
    }

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'voucher_id'   => ['type'=>'int','valid'=>'required|min:1', 'desc'=>'购物券id', 'msg'=>''],
            'lv1_cat_id'   => ['type'=>'int','valid'=>'min:1', 'desc'=>'一级类目id', 'msg'=>''],
            //分页参数
            'page_no'   => ['type'=>'int','valid'=>'min:1|numeric', 'desc'=>'分页当前页数,默认为1', 'msg'=>''],
            'page_size' => ['type'=>'int','valid'=>'', 'desc'=>'每页数据条数,默认10条', 'msg'=>''],
            'orderBy'   => ['type'=>'string','valid'=>'', 'desc'=>'商品列表排序，默认 sales_count desc', 'msg'=>''],
        ];
        return $return;
    }

    /**
     * @return
     */

    public function handle($params)
    {
    	$params = $this->__getApiFilter($params);
        $filter = $params;

        $filter['page_no'] = $params['page_no'] ? : 1;
        $limit = $params['page_size'] ? : 10;
        $itemsList = $this->objLibSearch->setLimit($limit)
                          ->search($filter)
                          ->setItemsActivetyTag()
                          ->setItemsPromotionTag()
                          ->getData();

        if( $itemsList['list'] )
        {
            $pagedata['items'] = $itemsList['list'];
            $pagedata['cur_symbol'] = app::get('topapi')->rpcCall('currency.get.symbol',array());
        }

        if( $this->objLibSearch->getTotalResults() )
        {
            $pagedata['pagers']['total'] = $this->objLibSearch->getTotalResults();
        }

        return $pagedata;
    }

    private function __getApiFilter($params)
    {
        $voucher = app::get('topapi')->rpcCall('promotion.voucher.get',['voucher_id'=>$params['voucher_id'],'fields'=>'voucher_id,voucher_name,promotion_tag,used_platform,limit_cat,limit_money,deduct_money,canuse_start_time,canuse_end_time,valid_status']);

    	$limitCat = $voucher['limit_cat'];
    	$registerShop = $voucher['registerShop'];

    	if(!$limitCat) return $params;

    	if(!$params['lv1_cat_id'])
    	{
    		$catId = $limitCat[0];
    	}
    	else
    	{
    		$catId = $params['lv1_cat_id'];
    	}

    	if(!$params['shoo_id'])
    	{
    		foreach($registerShop as $row)
    		{
    			if(in_array($catId,explode(',',$row['cat_id'])))
    			{
    				$params['shop_id'][] = $row['shop_id'];
    			}
    		}
    	}

    	if($params['shop_id'])
    	{
    		$params['shop_id'] = implode(',',$params['shop_id']);
    	}
    	else
    	{
    		$params['shop_id'] = '-1';
    	}

    	if(!$params['cat_id'])
    	{
    		$catList = app::get('topapi')->rpcCall('category.cat.get',['cat_id'=>$catId,'fields'=>'cat_id,cat_name']);
    		$catIds = [];
    		foreach($catList as $lv2Row)
    		{
    			foreach($lv2Row['lv2'] as $value)
    			{
    				$catIds = array_merge($catIds,array_column($value['lv3'],'cat_id'));
    			}
    		}
    		$params['cat_id'] = implode(',',$catIds);
    	}
        unset($params['voucher_id']);
    	return $params;
    }
}

