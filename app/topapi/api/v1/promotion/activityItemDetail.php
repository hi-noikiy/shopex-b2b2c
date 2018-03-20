<?php
/**
 * topapi
 *
 * -- promotion.activity.itemdetail
 * -- 获取平台活动商品详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_activityItemDetail implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取平台活动商品详情';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'item_id'   => ['type'=>'int','valid'=>'required|min:1|numeric', 'example'=>'55', 'desc'=>'商品id'],
            //分页参数
            'activity_id'   => ['type'=>'int','valid'=>'required|min:1|numeric', 'example'=>'1', 'desc'=>'活动id'],
        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $filter['fields'] = "*";
        $filter['activity_id'] = $params['activity_id'];
        $filter['item_id'] = $params['item_id'];
        $groupItem = app::get('topapi')->rpcCall('promotion.activity.item.info', $filter);
        $activity = $groupItem['activity_info'];
        if($activity['release_time'] > time())
        {
            return (object)[];
        }
        unset($groupItem['activity_info']);
        if(!$groupItem)
        {
            return (object)[];
        }
        $pagedata['group_item'] = $groupItem;
        $pagedata['activity'] = $activity;
        if(time()<$pagedata['activity']['start_time'])
        {
            $pagedata['activity']['status'] = 'comming';
        }
        if(time()>$pagedata['activity']['start_time'] && time()<$pagedata['activity']['end_time'])
        {
            $pagedata['activity']['status'] = 'active';
        }
        if(time()>$pagedata['activity']['end_time'])
        {
            $pagedata['activity']['status'] = 'closed';
        }
        $pagedata['item'] = app::get('topapi')->rpcCall('item.get',array('item_id'=>$params['item_id'],'fields'=>'image_default_id,sub_title,item_id,title,price,rate_count,rate_good_count,list_image,shop_id,item_count.sold_quantity,item_count.item_id,item_desc.wap_desc'));
        unset($pagedata['item']['list_image']);
        if( $pagedata['item']['images'] )
        {
            foreach( $pagedata['item']['images']  as &$v)
            {
                $v = base_storager::modifier($v);
            }
        }
        $pagedata['shop'] = app::get('topapi')->rpcCall('shop.get',array('shop_id'=>$pagedata['item']['shop_id'],'fields'=>'shop_name,shop_id,shop_logo'));
        if( $pagedata['shop']['shop_logo'] )
        {
            $pagedata['shop']['shop_logo'] = base_storager::modifier($pagedata['shop']['shop_logo'], 't');
        }

        // $pagedata['shopDsrData'] = $this->__getShopDsr($pagedata['shop']['shop_id']);
        $pagedata['now_time'] = time();
        
        // h5端链接
        $pagedata['share']['image'] = base_storager::modifier($pagedata['item']['image_default_id'], 't');
        $pagedata['share']['h5href'] = url::action('topwap_ctl_activity@itemdetail', ['a'=>$params['activity_id'], 'g'=>$params['item_id']]);

        return $pagedata;
    }

    // 获取店铺的dsr信息
    private function __getShopDsr($shopId)
    {
        $params['shop_id'] = $shopId;
        $params['catDsrDiff'] = true;
        $dsrData = app::get('topapi')->rpcCall('rate.dsr.get', $params);
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
    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"group_item":{"id":14,"activity_id":1,"shop_id":1,"item_id":71,"cat_id":28,"title":"Gap纯棉毛圈经典徽标连帽卫衣|男装179949","item_default_image":"http://images.bbc.shopex123.com/images/08/0f/c1/02a2ed7b47d1696b0927371f319db9e668ebb041.png","price":"399.000","activity_price":"288.000","sales_count":0,"verify_status":"agree","start_time":1454083200,"end_time":1483113600,"activity_tag":"团购","status":1},"activity":{"activity_id":1,"activity_name":"年欢惠","activity_tag":"团购","activity_desc":"所有商品全部8折优惠","apply_begin_time":1453784400,"apply_end_time":1453910400,"release_time":1453996800,"start_time":1454083200,"end_time":1483113600,"buy_limit":3,"enroll_limit":100,"limit_cat":{"1":"服装鞋包","6":"家用电器","12":"手机数码","14":"电脑、办公","46":"母婴、玩具","66":"个护化妆","254":"食品、生鲜","299":"运动户外","330":"家居用品","363":"营养保健"},"shoptype":{"flag":"品牌旗舰店","brand":"品牌专卖店","cat":"类目专营店","self":"运营商自营"},"discount_min":70,"discount_max":90,"mainpush":0,"slide_images":"http://images.bbc.shopex123.com/images/bd/12/4e/f71b06d423f37dae13d5c3bc6d1ca97bb771f61f.jpg","enabled":0,"created_time":1453779753,"remind_enabled":1,"remind_way":"email","remind_time":10},"item":{"item_id":71,"pc_desc":"","sold_quantity":0},"shop":{"shop_name":"GAP官方","shop_id":1,"shop_type":"flag","shopname":"GAP官方旗舰店","shoptype":"品牌旗舰店","subdomain":"shop1"},"shopDsrData":{"countDsr":{"tally_dsr":"5.0","attitude_dsr":"5.0","delivery_speed_dsr":"5.0"},"catDsrDiff":null},"now_time":1475066245}}';
    }

}
