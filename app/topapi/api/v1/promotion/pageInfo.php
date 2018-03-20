<?php
/**
 * topapi
 *
 * -- promotion.page。info
 * -- 获取促销页面信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_pageInfo implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取促销页面信息';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'page_id'       => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'title'=>'促销专题ID', 'description'=>'促销专题ID'],
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'default'=>'', 'example'=>'', 'title'=>'需要的字段', 'description'=>'需要的字段'],
        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        //获取促销页面信息
        $pageData = array(
            'page_id' => $params['page_id'],
            'fields' => $params['fields'],
        );

        $data = app::get('topapi')->rpcCall('promotion.get.page.info',$pageData);
        if($data['used_platform'] != 'app' || $data['is_display'] == 0 || time() < $data['display_time'])
        {
            $data = array();
        }
        else{
            $data['info'] = $data;
            if($data['page_tmpl']){
                $tmpldata['ptmpl_id'] = $data['page_tmpl'];
                $tmplInfo = app::get('topapi')->rpcCall('promotion.get.pagetmpl.info',$tmpldata);
                $data['info']['tmpl'] = $tmplInfo['content'];
            }
        }

        return $data;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"info":{"page_id":1,"page_name":"测试专题营销专题页","page_tmpl":"custom","page_desc":"这是一个测试专题营销专题页！！！","used_platform":"app","display_time":1479797400,"is_display":1,"created_time":1479797458,"updated_time":1479804621}}}';
    }

}
