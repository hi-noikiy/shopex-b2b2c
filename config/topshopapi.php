<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    /*
    |--------------------------------------------------------------------------
    | 定义所有topapi api接口路由
    |--------------------------------------------------------------------------
    | v1 表示API版本号
    | trade.shop.get API调用方法
    | topshopapi_api_v1_trade_get API实现类默认调用handle方法
     */
    'routes' => array(
        'v1' => [
            //shopex体系的数据结构
            'shopex.trade.get' => ['uses' => 'topshopapi_api_v1_trade_shopex_get@handle'],
            'trade.cancel'   => ['uses' => 'topshopapi_api_v1_trade_close@handle'],
            'trade.delivery' => ['uses' => 'topshopapi_api_v1_trade_delivery@handle'],

            'aftersales.get'           => ['uses' => 'topshopapi_api_v1_aftersales_get@handle'],
            'aftersales.status.update' => ['uses' => 'topshopapi_api_v1_aftersales_updateStatus@handle'],
            'aftersales.refundapply.shop.add' => ['uses'=>'topshopapi_api_v1_aftersales_refundapply_create@handle'],
            'aftersales.refundapply.get'      => ['uses'=>'topshopapi_api_v1_aftersales_refundapply_get@handle'],
            'aftersales.refundapply.shop.check' => ['uses'=>'topshopapi_api_v1_aftersales_refundapply_shopCheck@handle'],

            'item.shop.store.update' => ['uses' => 'topshopapi_api_v1_item_updateStore@handle'],
            'item.get'      => ['uses' => 'topshopapi_api_v1_item_get@handle'],
            'item.list.get' => ['uses' => 'topshopapi_api_v1_item_list@handle'],
            'item.search'   => ['uses' => 'topshopapi_api_v1_item_search@handle'],
            'item.sku.get'  => ['uses' => 'topshopapi_api_v1_item_getSkuGet@handle'],
        ]
    )
);
