<?php
/**
 * ShopEx licence
 *
 ** -- member.rate.append
 * -- 对商品评论进行追评
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 */
class topapi_api_v1_member_rate_append implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '对商品评论进行追评';

    public function setParams()
    {
        return array(
            'rate_id'  => ['type' => 'int',     'valid' => 'required|integer|min:1', 'example' => '1', 'desc' => '评论ID'],
            'content'  => ['type' => 'string',  'valid' => 'required|min:5|max:300', 'example' => '',  'desc' => '评价内容', 'msg'=> '请填写追评内容|追评内容最少5个字|追评内容最多300个字'],
            'rate_pic' => ['type' => 'string',  'valid' => '',                       'example' => '',  'desc' => '晒单图片，多个图片用逗号隔开,最多上传5张照片'],
        );

        return $return;
    }

    public function handle($params)
    {
        if( $params['rate_pic'] )
        {
            $params['pic'] = $params['rate_pic'];
        }

        $result = app::get('topapi')->rpcCall('rate.append', $params);

        return $result;
    }
}


