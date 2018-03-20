<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class site_utils_linkcfg {

    // PC端页面链接
    public $topc = [
        'activity' => [
            'name'=>'活动列表页(pc)',
            'obj'=>[],
            'action'=>'topc_ctl_activity@index',
        ],
        'activitygoodslist' => [
            'name'=>'活动详情页(pc)',
            'obj'=> [
                'object'=>'activity@syspromotion',
                'textcol'=>'activity_name',
                'emptytext'=>'请选择活动'
            ],
            'action'=>'topc_ctl_activity@activity_item_list',
            'pk_name'=>'id',
        ],
        'cart' => [
            'name'=>'购物车页(pc)',
            'obj'=>[],
            'action'=>'topc_ctl_cart@index',
        ],
        'content' => [
            'name'=>'文章列表页(pc)',
            'obj'=> [
                'object'=>'article_nodes@syscontent',
                'filter'=>'node_depth=2',
                'textcol'=>'node_name',
                'emptytext'=>'请选择文章类目'
            ],
            'action'=>'topc_ctl_content@index',
            'pk_name'=>'node_id',
        ],
        'content_info' => [
            'name'=>'文章详情页(pc)',
            'obj'=> [
                'object'=>'article@syscontent',
                'filter'=>'platform=pc',
                'textcol'=>'title',
                'emptytext'=>'请选择文章'
            ],
            'action'=>'topc_ctl_content@getContentInfo',
            'pk_name'=>'article_id',
        ],
        'item' => [
            'name'=>'商品详情页(pc)',
            'obj'=> [
                'object'=>'item@sysitem',
                'textcol'=>'title',
                'emptytext'=>'请选择商品'
            ],
            'action'=>'topc_ctl_item@index',
            'pk_name'=>'item_id',
        ],
        'member' => [
            'name'=>'会员中心页(pc)',
            'obj'=>[],
            'action'=>'topc_ctl_member@index',
        ],
        'topics' => [
            'name'=>'类目专题页(pc)',
            'obj'=> [
                'object'=>'cat@syscategory',
                'filter'=>'is_leaf=0',
                'textcol'=>'title',
                'emptytext'=>'请选择商品'
            ],
            'action'=>'topc_ctl_topics@index',
            'pk_name'=>'cat_id',
        ],
        'catlist' => [
            'name'=>'三级分类商品列表页(pc)',
            'obj'=> [
                'object'=>'cat@syscategory',
                'filter'=>'is_leaf=1',
                'textcol'=>'cat_name',
                'emptytext'=>'请选择三级分类'
            ],
            'action'=>'topc_ctl_list@index',
            'pk_name'=>'cat_id',
        ],
        'promotion' => [
            'name'=>'商家促销关联商品页(pc)',
            'obj'=> [
                'object'=>'promotions@syspromotion',
                'textcol'=>'promotion_name',
                'emptytext'=>'请选择普通促销'
            ],
            'action'=>'topc_ctl_promotion@getPromotionItem',
            'pk_name'=>'promotion_id'
        ],
        'coupon' => [
            'name'=>'商家优惠券关联商品页(pc)',
            'paramkey'=>'coupon_id',
            'obj'=> [
                'object'=>'coupon@syspromotion',
                'textcol'=>'coupon_name',
                'emptytext'=>'请选择优惠券促销'
            ],
            'action'=>'topc_ctl_promotion@getCouponItem',
            'pk_name'=>'coupon_id',
        ],
        'shop' => [
            'name'=>'店铺首页(pc)',
            'obj'=> [
                'object'=>'shop@sysshop',
                'textcol'=>'shop_name',
                'emptytext'=>'请选择店铺'
            ],
            'action'=>'topc_ctl_shopcenter@index',
            'pk_name'=>'shop_id'
        ],
        'promotion-page' => [
            'name'=>'营销专题页(pc)',
            'obj'=> [
                'object'=>'page@syspromotion',
                'filter'=>'used_platform=pc',
                'textcol'=>'page_name',
                'emptytext'=>'请选择营销专题'
            ],
            'action'=>'topc_ctl_promotion@ProjectPage',
            'pk_name'=>'page_id'
        ],
    ];

    // WAP端页面链接
    public $topwap = [
        'activity' => [
            'name'=>'活动列表页(h5)',
            'obj'=>[],
            'action'=>'topwap_ctl_activity@active_list',
        ],
        'activitygoodslist' => [
            'name'=>'活动详情页(h5)',
            'obj'=> [
                'object'=>'activity@syspromotion',
                'textcol'=>'activity_name',
                'emptytext'=>'请选择活动'
            ],
            'action'=>'topwap_ctl_activity@detail',
            'pk_name'=>'id',
        ],
        'cart' => [
            'name'=>'购物车页(h5)',
            'obj'=>[],
            'action'=>'topwap_ctl_cart@index',
        ],
        'category' => [
            'name'=>'商品类目页(h5)',
            'obj'=>[],
            'action'=>'topwap_ctl_category@index',
        ],
        'contenthome' => [
            'name'=>'文章类目首页(h5)',
            'obj'=> [],
            'action'=>'topwap_ctl_content@index',
        ],
        'content' => [
            'name'=>'三级文章列表页(h5)',
            'obj'=> [
                'object'=>'article_nodes@syscontent',
                'filter'=>'node_depth=2',
                'textcol'=>'node_name',
                'emptytext'=>'请选择文章类目'
            ],
            'action'=>'topwap_ctl_content@index',
            'pk_name'=>'node_id',
        ],
        'content_info' => [
            'name'=>'文章详情页(h5)',
            'obj'=> [
                'object'=>'article@syscontent',
                'filter'=>'platform=wap',
                'textcol'=>'title',
                'emptytext'=>'请选择文章'
            ],
            'action'=>'topwap_ctl_content@getContentInfo',
            'pk_name'=>'aid',
        ],
        'item' => [
            'name'=>'商品详情页(h5)',
            'obj'=> [
                'object'=>'item@sysitem',
                'textcol'=>'title',
                'emptytext'=>'请选择商品'
            ],
            'action'=>'topwap_ctl_item_detail@index',
            'pk_name'=>'item_id',
        ],
        'member' => [
            'name'=>'会员中心页(h5)',
            'obj'=>[],
            'action'=>'topwap_ctl_member@index',
        ],
        'catlist' => [
            'name'=>'三级分类商品列表页(h5)',
            'obj'=> [
                'object'=>'cat@syscategory',
                'filter'=>'is_leaf=1',
                'textcol'=>'cat_name',
                'emptytext'=>'请选择三级分类'
            ],
            'action'=>'topwap_ctl_item_list@index',
            'pk_name'=>'cat_id',
        ],
        'promotion' => [
            'name'=>'商家促销关联商品页(h5)',
            'obj'=> [
                'object'=>'promotions@syspromotion',
                'textcol'=>'promotion_name',
                'emptytext'=>'请选择普通促销'
            ],
            'action'=>'topwap_ctl_promotion@getPromotionItem',
            'pk_name'=>'promotion_id'
        ],
        'coupon' => [
            'name'=>'商家优惠券关联商品页(h5)',
            'paramkey'=>'coupon_id',
            'obj'=> [
                'object'=>'coupon@syspromotion',
                'textcol'=>'coupon_name',
                'emptytext'=>'请选择优惠券促销'
            ],
            'action'=>'topwap_ctl_promotion@getCouponItem',
            'pk_name'=>'coupon_id',
        ],
        'shop' => [
            'name'=>'店铺首页(h5)',
            'obj'=> [
                'object'=>'shop@sysshop',
                'textcol'=>'shop_name',
                'emptytext'=>'请选择店铺'
            ],
            'action'=>'topwap_ctl_shop@index',
            'pk_name'=>'shop_id'
        ],
        'promotion-page' => [
            'name'=>'营销专题页(h5)',
            'obj'=> [
                'object'=>'page@syspromotion',
                'filter'=>'used_platform=wap',
                'textcol'=>'page_name',
                'emptytext'=>'请选择营销专题'
            ],
            'action'=>'topwap_ctl_promotion@ProjectPage',
            'pk_name'=>'page_id'
        ],
        'scratchcard' => [
            'name'=>'刮刮卡活动页(h5)',
            'obj'=> [
                'object'=>'scratchcard@syspromotion',
                'textcol'=>'scratchcard_name',
                'emptytext'=>'请选择刮刮卡'
            ],
            'action'=>'topwap_ctl_promotion_scratchcard@index',
            'pk_name'=>'scratchcard_id'
        ],

    ];

}//End Class
