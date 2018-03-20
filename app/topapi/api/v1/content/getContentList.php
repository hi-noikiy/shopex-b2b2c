<?php
/**
 * topapi
 *
 * -- content.list
 * -- 获取文章列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topapi_api_v1_content_getContentList implements topapi_interface_api{

	/**
     * 接口作用说明
     */
    public $apiDescription = '获取文章列表';

    public function setParams()
    {
        return [
            'node_id' => ['type'=>'int',  'valid'=>'required|numeric|min:1','example'=>'1',      'desc'=>'文章ID',      'msg'=>'node_id必填'],
            'page_no' => ['type'=>'int',  'valid'=>'numeric', 'example'=>'', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int', 'valid'=>'numeric', 'example'=>'', 'desc'=>'每页数据条数,默认10条'],
            'orderBy' => ['type'=>'string', 'valid'=>'',    'example'=>'',   'desc'=>'排序方式',   'msg'=>''],
            'fields' => ['type'=>'field_list',  'valid'=>'',    'example'=>'title,modified,content,node_id','desc'=>'要获取的字段集。多个字段用“,”分隔','msg'=>''],
            'platform' => ['type'=>'string',  'valid'=>'in:pc,wap',       'default'=>'wap',   'example'=>'true',    'desc'=>'平台,默认是wap'], 
        ];
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $params['page_no'] = $params['page_no'] ?: 1;
        $params['page_size'] = $params['page_size'] ?: 10;
        $params['platform'] = 'wap';
        $params['fields'] = $params['fields']?:'article_id,title';

        $result = app::get('topapi')->rpcCall('syscontent.content.get.list',$params);

        if(!$result['articleList']){
            $result = [];
        }else{
            $result['pagers']['total'] = $result['articlecount'];
        }

        return $result;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"articleList":[{"article_id":44,"title":"测试文章4","node_id":4,"modified":1475031906},{"article_id":43,"title":"测试文章3","node_id":4,"modified":1475031888},{"article_id":42,"title":"测试文章2","node_id":4,"modified":1475031875},{"article_id":41,"title":"测试www","node_id":4,"modified":1475031851},{"article_id":6,"title":"订单的几种状态","node_id":4,"modified":1475031351},{"article_id":5,"title":"顾客必读","node_id":4,"modified":1475031344}],"articlecount":6,"nodeInfo":{"node_id":4,"node_name":"新手上路"},"pagers":{"total":6}}}';
    }
}
