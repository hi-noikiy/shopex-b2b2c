<?php
/**
 * topapi
 *
 * -- member.favorite.all
 * -- 获取用户的所有收藏
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_favorite_all implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取用户的所有收藏';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [];
    }

    /**
     *
     * @return array item 会员收藏的商品ID
     * @return array shop 会员收藏的店铺ID
     */
    public function handle($params)
    {
        return app::get('topapi')->rpcCall('user.collect.info',$params);
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"item":[37],"shop":[3,4]}}';
    }
}

