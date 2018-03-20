<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysapp_module_config {

    // 挂件类型
    public $widgets = [
        'category_nav' => '商品分类展示',
        'floor' => '楼层',
        'icons_nav' => '快捷导航',
        'slider' => '轮播',
        'single_pic' => '单图',
        'double_pics' => '双图',
    ];

    // 页面类型
    public $tmpls = [
        'index' => '首页',
        // 'activityindex' => '活动首页',
    ];

    // 对应app端页面类型，用于app端判断怎么跳转页面
    public $linktype = [
        'topics' => '专题页',
        'catlist' => '分类页',
        'item' => '商品详情页',
        'member' => '会员中心页',
        'content' => '文章详情页',
        'shopcenter' => '店铺页',
        'activity' => '活动详情页',
        'promotion' => '促销详情页',
        'scratchcard' => '刮刮卡详情页',
        'h5' => '自定义h5页',
        'promotion-page' => '营销专题页',
    ];

    // name 页面显示，paramkey 对应app端页面需要的参数,目前只支持单个参数
    public $linkmapapp = [
        'h5' => ['name'=>'自定义h5页'],

        'activity' => ['name'=>'活动列表页', 'apppage'=>'_www/view/activity/activity.html'],
        'activitygoodslist' => ['name'=>'活动详情页', 'apppage'=>'_www/view/activity/activitygoodslist.html', 'paramkey'=>'activity_id', 'obj'=> ['object'=>'activity@syspromotion','textcol'=>'activity_name','emptytext'=>'请选择活动'] ],
        
        'cart' => ['name'=>'购物车页', 'apppage'=>'_www/view/cart/cart.html',],

        'category' => ['name'=>'类目页', 'apppage'=>'_www/view/category/category.html'],

        'content' => ['name'=>'文章列表页', 'apppage'=>'_www/view/artical/artical.html', 'paramkey'=>'nodeid', 'obj'=> ['object'=>'article_nodes@syscontent', 'filter'=>'node_depth=2', 'textcol'=>'node_name','emptytext'=>'请选择文章类目'] ],
        'content_info' => ['name'=>'文章详情页', 'apppage'=>'_www/view/artical/articaldetail.html', 'paramkey'=>'articleid', 'obj'=> ['object'=>'article@syscontent', 'filter'=>'platform=wap', 'textcol'=>'title','emptytext'=>'请选择文章'] ],

        'item' => ['name'=>'商品详情页', 'apppage'=>'_www/view/item/goodsdetail.html', 'paramkey'=>'itemid', 'obj'=> ['object'=>'item@sysitem','textcol'=>'title','emptytext'=>'请选择商品'] ],

        'catlist' => ['name'=>'三级分类页', 'apppage'=>'_www/view/list/goods.html', 'paramkey'=>'catid', 'obj'=> ['object'=>'cat@syscategory', 'filter'=>'is_leaf=1', 'textcol'=>'cat_name','emptytext'=>'请选择三级分类'] ],

        'member' => ['name'=>'会员中心页', 'apppage'=>'_www/view/member/member.html'],

        'promotion' => ['name'=>'商家促销详情页', 'apppage'=>'_www/view/promotion/promotion.html', 'paramkey'=>'promotion_id', 'obj'=> ['object'=>'promotions@syspromotion','textcol'=>'promotion_name','emptytext'=>'请选择普通促销'] ],

        'scratchcard' => ['name'=>'刮刮卡详情页', 'apppage'=>'_www/view/promotion/scratchcard/index.html', 'paramkey'=>'scratchcard_id', 'obj'=> ['object'=>'scratchcard@syspromotion', 'filter'=>'used_platform|noequal=1', 'textcol'=>'scratchcard_name','emptytext'=>'请选择刮刮卡'] ],

        'coupon' => ['name'=>'商家优惠券详情页', 'apppage'=>'_www/view/promotion/coupon.html', 'paramkey'=>'coupon_id', 'obj'=> ['object'=>'coupon@syspromotion','textcol'=>'coupon_name','emptytext'=>'请选择优惠券促销'] ],

        'shop' => ['name'=>'店铺页', 'apppage'=>'_www/view/shop/shop.html', 'paramkey'=>'shopid', 'obj'=> ['object'=>'shop@sysshop','textcol'=>'shop_name','emptytext'=>'请选择店铺'] ],
        'promotion-page' => ['name'=>'营销专题页', 'apppage'=>'_www/view/promotion/page.html', 'paramkey'=>'page_id', 'obj'=> ['object'=>'page@syspromotion', 'filter'=>'platform=wap', 'textcol'=>'page_name','emptytext'=>'请选择营销专题'] ],

    ];

}//End Class
