<?php

/**
 * topapi
 *
 * -- logistics.ziti.list
 * -- 根据用户收货地址，获取自提地址
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_logistics_ziti_list implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '根据用户收货地区，获取自提地址';

    public function setParams()
    {
        return  [
            'area_id' =>['type'=>'string','valid'=>'required', 'desc'=>'收货地区id','example'=>'110100,110101'],
        ];
    }

    public function handle($params)
    {
        $data = app::get('topapi')->rpcCall('logistics.ziti.list',$params);
        if( $data )
        {
            $return['list'] = $data;
            return $return;
        }
        else
        {
            return (object)[];
        }
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"id":2,"name":"北京自提点","area_state_id":"1","area_city_id":"110100","area_district_id":"110105","area":"北京市朝阳区","addr":"望京","tel":"13652369526"}]}}';
    }
}
