<?php
/**
 * topapi
 *
 * -- content.shop.info
 * -- 获取商家文章内容
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topapi_api_v1_content_shopArticleInfo implements topapi_interface_api{

	/**
     * 接口作用说明
     */
    public $apiDescription = '获取商家文章详情';

    public function setParams()
    {
        return [
            'article_id' => ['type'=>'int',  'valid'=>'required|numeric|min:1','example'=>'1', 'desc'=>'文章ID', 'msg'=>'文章id必填'],
            'shop_id' => ['type'=>'int','valid'=>'required|numeric|min:1','example'=>'','desc'=>'店铺id', 'msg'=>'商家id必填'],
            'fields' => ['type'=>'field_list',  'valid'=>'',   'example'=>'title,modified,content,node_id','desc'=>'要获取的字段集。多个字段用“,”分隔','msg'=>''],
        ];
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $params['article_id'] = intval($params['article_id']);
		$params['shop_id'] = intval($params['shop_id']);
        $params['fields'] = $params['fields']?:'article_id,title,modified,content,node_id';
        $result['list'] = app::get('topapi')->rpcCall('syscontent.shop.info.article',$params);

        return $result;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":{"article_id":58,"title":"测试文章","modified":1479039979,"content":"这是测试文章！这是测试文章这是测试文章！这是测试文章！是测试文章！！这是测试文章！这是测试文章！这是测试文章！这是测试文章！这是测试文章！这是测试文章！这是测试文章！","node_id":8}}}';
    }
}
