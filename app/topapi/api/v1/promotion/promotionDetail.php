<?php
/**
 * topapi
 *
 * -- promotion.shop.cartpromotion.detail
 * -- 获取商家促销详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_promotionDetail implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取商家促销详情';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'promotion_id'   => ['type'=>'int','valid'=>'required|min:1', 'example'=>'active', 'desc'=>'促销id。购物车内唯一选择的促销，包括满减，满折，XY折', 'msg'=>''],
            //分页参数
            'page_no'   => ['type'=>'int','valid'=>'min:1|numeric', 'example'=>'1', 'desc'=>'分页当前页数,默认为1', 'msg'=>''],
            'page_size' => ['type'=>'int','valid'=>'', 'example'=>'10', 'desc'=>'每页数据条数,默认10条', 'msg'=>''],
            'orderBy'   => ['type'=>'string','valid'=>'', 'example'=>'', 'desc'=>'商品列表排序，默认 sales_count desc', 'msg'=>''],

            //返回字段
            // 'info_fields'    => ['type'=>'field_list','valid'=>'', 'example'=>'*', 'desc'=>'促销详情需要返回的字段', 'msg'=>''],
            // 'item_fields'    => ['type'=>'field_list','valid'=>'', 'example'=>'*', 'desc'=>'促销商品列表需要返回的字段', 'msg'=>''],
        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        // 获取促销详情基本信息
        $promotionInfo = app::get('topapi')->rpcCall('promotion.promotion.get', array('promotion_id'=>$params['promotion_id']));
        if($promotionInfo['valid'])
        {
            $pagedata = $this->__commonPromotionItemList($params, $promotionInfo);
        }
        else
        {
            throw new \LogicException(app::get('topapi')->_('促销不存在或者已经失效'), 30001);
        }

        return $pagedata;
    }

    /**
     * 返回促销关联的商品页面
     * @param  array $filter 获取促销关联商品所需的，分页
     * @param  array $promotionInfo 对应促销的促销id，促销类型
     * @return mixed 返回促销关联商品列表等信息
     */
    private function __commonPromotionItemList($params, $promotionInfo)
    {
        $apiFilter = array(
            'page_no' => $params['page_no'] ? (int)$params['page_no'] : 1,
            'page_size' => $params['page_size'] ? (int)$params['page_size'] : 10,
            'orderBy' => $params['orderBy'],
            'fields' =>'item_id,shop_id,title,image_default_id,price',
        );
        //获取促销商品列表
        $promotionItem = $this->__promotionItemList($promotionInfo, $apiFilter);

        $pagedata['info'] = $promotionInfo;
        $pagedata['info']['promotion_desc'] = $promotionItem['promotionInfo']['promotion_desc'];

        if( $promotionItem['list'] )
        {
            foreach( $promotionItem['list'] as &$row )
            {
                $row['image_default_id'] = base_storager::modifier($row['image_default_id'] , 's');
            }
        }

        $pagedata['list'] = $promotionItem['list'] ? : (object)[];
        $pagedata['pagers']['total'] = $promotionItem['total_found'] ? : 0;

        return $pagedata;
    }

    //获取促销的类型以及商品数据
    private function __promotionItemList($promotionInfo,$params)
    {
        switch ($promotionInfo['promotion_type'])
        {
            case 'fullminus':
                $params['fullminus_id'] = $promotionInfo['rel_promotion_id'];
                $promotionInfo = app::get('topapi')->rpcCall('promotion.fullminusitem.list', $params);
                $promotionInfo['promotionInfo']['promotion_desc'] = $promotionInfo['promotionInfo']['fullminus_desc'];
                break;
            case 'fulldiscount':
                $params['fulldiscount_id'] = $promotionInfo['rel_promotion_id'];
                $promotionInfo = app::get('topapi')->rpcCall('promotion.fulldiscountitem.list', $params);
                $promotionInfo['promotionInfo']['promotion_desc'] = $promotionInfo['promotionInfo']['fulldiscount_desc'];
                break;
            case 'xydiscount':
                $params['xydiscount_id'] = $promotionInfo['rel_promotion_id'];
                $promotionInfo = app::get('topapi')->rpcCall('promotion.xydiscountitem.list', $params);
                $promotionInfo['promotionInfo']['promotion_desc'] = $promotionInfo['promotionInfo']['xydiscount_desc'];
                break;
        }

        return $promotionInfo;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"info":{"promotion_id":1,"rel_promotion_id":1,"promotion_type":"fullminus","shop_id":1,"promotion_name":"满299减50","promotion_tag":"满减","promotion_desc":"","used_platform":"0","start_time":1453734000,"end_time":1598371200,"created_time":1453718018,"check_status":"agree","valid":true},"list":[{"item_id":28,"shop_id":1,"title":"Gap纯棉经典徽标字母圆领卫衣|男装109453","image_default_id":"http://images.bbc.shopex123.com/images/67/2c/53/c2dbb5db5c08107f602318c45b784b0c1ec509b8.png","price":"199.000"},{"item_id":30,"shop_id":1,"title":"Gap纯棉徽标字母印花连帽卫衣|男装721398 ","image_default_id":"http://images.bbc.shopex123.com/images/22/36/32/d12162a86dbc1ba7aba9801b00ba43b0d1793aa0.png","price":"399.000"},{"item_id":32,"shop_id":1,"title":"Gap棉质时尚拼色绒里连帽卫衣|男装124968","image_default_id":"http://images.bbc.shopex123.com/images/6b/89/1f/e1b9404a412e5a38e2728686979800b8de3ce9e6.png","price":"349.000"},{"item_id":71,"shop_id":1,"title":"Gap纯棉毛圈经典徽标连帽卫衣|男装179949","image_default_id":"http://images.bbc.shopex123.com/images/08/0f/c1/02a2ed7b47d1696b0927371f319db9e668ebb041.png","price":"399.000"}],"pagers":{"total":4}}}';
    }

}
