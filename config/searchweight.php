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
    | 搜索权重分配置
    |--------------------------------------------------------------------------
    | 分数有加减乘除，最后算出总分
    | 每个大项总分 1000 分，最低 0 分
    |
    */
    'goods_quality' => array(
        'every_pic'      => 40, // 每张图片 40 分,满分 200 分
        'every_sold'     => 1, // 每个销量 1 分，大于600的销量封顶600分
        'every_rate_good' => 5, // 每个好评 5 分，好中差评总分加起来不超过 200 分
        'every_rate_neutral' => 3, // 每个中评 3 分，好中差评总分加起来不超过 200 分
        'every_rate_bad' => 1, // 每个差评 1 分，好中差评总分加起来不超过 200 分

        // 'sub_title'      => 10, // 有子标题 10 分
        // 'pc_desc'        => 10, // 有pc端商品描述 10 分
        // 'wap_desc'       => 10, // 有移动端商品描述 10 分
    ),
    'goods_updown' => array(
        'new' => 1000, // 第一次上架 1000 分
        'relist' => 500, // 三个月内再次上架 500 分
    ),
    'goods_maintenance' => array( // 总分默认 1000 分，实行扣减制
        'every_nosku' => -100, // 缺货一个sku扣减100分
        'every_artersales_num' => -100, // 30天内5单售后内不减分，多于5单售后，每单扣减100分
    ),
    'shop' =>  array(
        'shoptype_self'  => 400, // 自营店 D 分
        'shoptype_flag'  => 350, // 旗舰店 A 分
        'shoptype_brand' => 300, // 品牌专营店 B 分
        'shoptype_store' => 250, // 多类目店 E 分
        'shoptype_cat'   => 200, // 类目专营店 C 分
    ),
);
