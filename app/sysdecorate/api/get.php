<?php
/**
 * -- sysdecorate.widgets.get
 * -- 获取指定页面挂件配置
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysdecorate_api_get {

    public $apiDescription = '获取指定页面挂件配置';

    public $use_strict_filter = true; // 是否严格过滤参数

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'shop_id'    => ['type'=>'int',       'valid'=>'required|integer', 'desc'=>'店铺ID'],
            'page_name'  => ['type'=>'string',    'valid'=>'required', 'desc'=>'页面名称'],
            'platform'   => ['type'=>'string',    'valid'=>'required|in:pc,wap,app', 'desc'=>'平台名称'],
        );
        return $return;
    }

    /**
     * @desc 添加单图展示挂件
     */
    public function get($params)
    {
        return kernel::single('sysdecorate_new_widgets')->getConfig($params['shop_id'], $params['page_name'], $params['platform']);
    }
}
