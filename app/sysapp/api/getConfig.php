<?php
/**
 * ShopEx licence
 * - sysapp.page.config
 * - 获取app端页面类型信息
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-23
 */
final class sysapp_api_getConfig{

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '获取app端页面类型信息';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        $return['params'] = array(
        );

        return $return;
    }

    /**
     * 获取app端页面模块配置信息
     * @desc 用于获取app指定页面相关信息
     * @return array 
     */
    public function get($params)
    {
        $return['tmpls'] = kernel::single('sysapp_module_config')->tmpls;// 页面类型
        $return['widgets'] = kernel::single('sysapp_module_config')->widgets;// 挂件类型
        $return['linkmapapp'] = kernel::single('sysapp_module_config')->linkmapapp;// 对应app端页面类型，用于app端判断怎么跳转页面

        return $return;
    }

}

