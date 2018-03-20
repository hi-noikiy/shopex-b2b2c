<?php
/**
 * topapi
 *
 * -- content.info
 * -- 获取文章内容
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topapi_api_v1_content_getContentInfo implements topapi_interface_api{

	/**
     * 接口作用说明
     */
    public $apiDescription = '获取文章详情';

    public function setParams()
    {
        return [
            'article_id' => ['type'=>'int',  'valid'=>'required|numeric|min:1','example'=>'1',      'desc'=>'文章ID',      'msg'=>'文章id必填'],
            'fields' => ['type'=>'field_list',  'valid'=>'',                   'example'=>'title,modified,content,node_id','desc'=>'要获取的字段集。多个字段用“,”分隔','msg'=>''],
        ];
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $params['article_id'] = intval($params['article_id']);
        $params['fields'] = $params['fields']?:'article_id,title,modified,content,node_id';
        $result = app::get('topapi')->rpcCall('syscontent.content.get.info',$params);

        return $result;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"article_id":44,"title":"测试文章4","modified":1475031906,"content":" 测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4测试文章4","node_id":4}}';
    }
}
