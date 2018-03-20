<?php

/**
 * basic.php 
 * -- 店铺基本信息
 * -- shop.basic
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_shop_basic implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取店铺基本信息';
    
    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
                'shop_id'   => ['type'=>'int','valid'=>'required|min:1', 'example'=>'1', 'desc'=>'店铺id'],
        ];
        return $return;
    }
    
    /**
     * @return string shop_name 店铺名称
     * @return string shop_logo 店铺logo
     * @return string shop_descript 店铺描述
     * @return string shop_descript 店铺描述
     * @return string mobile 联系电话
     * @return int open_time 开店时间
     * @return string attitude_dsr 服务评分
     * @return string tally_dsr 商品评分
     * @return string delivery_speed_dsr 配送评分
     */
    public function handle($params)
    {
        $result['shopInfo'] = [];
        $result['shopDsrData'] = [];
        $shopId = $params['shop_id'];
        $result['shopInfo'] = app::get('topapi')->rpcCall('shop.get',['shop_id'=>$shopId]);
        $result['shopInfo']['shop_logo'] = base_storager::modifier($result['shopInfo']['shop_logo'], 't');
        $result['shopDsrData'] = $this->__getShopDsr($shopId);
        
        return $result;
    }
    
    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"shopInfo":{"shop_id":3,"shop_name":"onexbbc自营店（自营店铺）","shop_descript":"onexbbc自营体验店","shop_type":"self","status":"active","open_time":1453699800,"qq":"","wangwang":"","shop_logo":"http://images.bbc.shopex123.com/images/e4/64/42/37eff0aeba30e897184f510c248deebd79cff488.png_t.png","shop_area":"上海","shop_addr":"桂林路396号","mobile":"13788822222","shopname":"onexbbc自营店（自营店铺）自营店","shoptype":"运营商自营"},"shopDsrData":{"tally_dsr":"2.5","attitude_dsr":"3.0","delivery_speed_dsr":"3.0"}}}';
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
        $shopDsrData = $countDsr;
        return $shopDsrData;
    }
}
 