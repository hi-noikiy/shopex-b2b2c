<?php
/**
 * topapi
 *
 * -- category.itemCategory
 * -- 获取平台分类树
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_category_itemCategory implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取平台分类树';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [];
    }

    public function handle($params)
    {
        $virtualcatEnable = app::get('syscategory')->getConf('virtualcat.appenable');
        if($virtualcatEnable =='true'){
            $pagedata['is_virtualcat'] = true;
            $pagedata['categorys'] = app::get('topapi')->rpcCall('category.virtualcat.get.list',['platform'=>'app']);
            return $pagedata;
        }

        $pagedata['categorys'] =app::get('topapi')->rpcCall('category.cat.get.list');
        return $pagedata;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{ errorcode:0,msg:"", data:{categorys:[{cat_id:1,parent_id: 0, cat_name: "服装鞋包", cat_logo: "", cat_path: ",", level: "1", is_leaf: 0, child_count: 6, order_sort: 0, lv2: [ { cat_id: 376, parent_id: 1, cat_name: "男鞋", cat_logo: null, cat_path: ",1,", level: "2", is_leaf: 0, child_count: 2, order_sort: 5, lv3: [ { cat_id: 387, parent_id: 376, cat_name: "休闲鞋", cat_logo: null, cat_path: ",1,376,", level: "3", is_leaf: 1, child_count: 0, order_sort: 0 }] } ] }] } }';
    }

}
