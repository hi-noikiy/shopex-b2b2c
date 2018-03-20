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
    | 定义所有luckymall预设的api接口路由
    |--------------------------------------------------------------------------
    |
    | key代表, api method name.
    | rpc::call('method', array($param1, $param2));
    |
     */
    'routes' => array(
        /*
         *=======================================
         *  售后服务API
         *=======================================
         */
        //创建售后服务
        'aftersales.apply' => ['uses' => 'sysaftersales_api_apply@create', 'version'=>['v1']],
        //获取单个售后详情
        'aftersales.get' => ['uses' => 'sysaftersales_api_info@getData', 'version'=>['v1'], 'oauth'=>true, 'level'=>70 ],
        'aftersales.get.bn' => ['uses' => 'sysaftersales_api_infobn@getData', 'version'=>['v1']],
        //获取售后列表
        'aftersales.list.get' => ['uses' => 'sysaftersales_api_list@getData', 'version'=>['v1']],
        //根据子订单编号，验证该组子订单号是否可以申请售后过售后
        'aftersales.verify' => ['uses' => 'sysaftersales_api_verify@verify', 'version'=>['v1']],
        //商家审核售后服务
        'aftersales.check' => ['uses' => 'sysaftersales_api_check@check', 'version'=>['v1']],

        //获取指定类目退货换货设置详情
        'aftersales.setting.get' => ['uses' => 'sysaftersales_api_aftersalessetting_get@get', 'version'=>['v1']],
        //获取特殊类目售后设置列表
        'aftersales.cat.setting.list' => ['uses' => 'sysaftersales_api_aftersalessetting_list@getList', 'version'=>['v1']],
        //获取子订单是否可进行退换货
        'aftersales.isEnabled' => ['uses' => 'sysaftersales_api_isAftersalesEnabled@get', 'version'=>['v1']],

        //售后状态更新
        'aftersales.status.update' => ['uses' => 'sysaftersales_api_updateStatus@update', 'version'=>['v1'], 'oauth'=>true, 'level'=>70],

        //消费者回寄退货物流信息
        'aftersales.send.back' => ['uses' => 'sysaftersales_api_sendBack@send', 'version'=>['v1']],
        //消费者申请换货，商家确认收到回寄商品，进行重新进行发货
        'aftersales.send.confirm' => ['uses' => 'sysaftersales_api_sendConfirm@send', 'version'=>['v1']],

        'aftersales.refundapply.shop.add' => ['uses'=>'sysaftersales_api_refundapply_createByShop@create', 'version'=>['v1'], 'oauth'=>true, 'level'=>70],
        //已支付的订单进行售后或者取消的情况下生成退款申请单
        'aftersales.refundapply.create' => ['uses'=>'sysaftersales_api_refundapply_create@create', 'version'=>['v1']],
        //获取退款申请单列表
        'aftersales.refundapply.list.get' => ['uses'=>'sysaftersales_api_refundapply_list@get', 'version'=>['v1']],
        //获取退款申请单详情
        'aftersales.refundapply.get' => ['uses'=>'sysaftersales_api_refundapply_get@get', 'version'=>['v1'], 'oauth'=>true, 'level'=>70],
        //更新退款申请单状态
        'aftersales.refundapply.shop.reply' => ['uses'=>'sysaftersales_api_refundapply_shopreply@reply', 'version'=>['v1']],
        //商家审核，更新退款申请单
        'aftersales.refundapply.shop.check' => ['uses'=>'sysaftersales_api_refundapply_shopCheck@reply', 'version'=>['v1'],'oauth'=>true,'level'=>70],
        //平台进行退款处理
        'aftersales.refunds.restore' => ['uses'=>'sysaftersales_api_refundapply_restore@update', 'version'=>['v1']],
        //根据售后单下载售后凭证
        'aftersales.download.evidencePic' => ['uses'=>'sysaftersales_api_evidencePic@download', 'version'=>['v1']],

        /*
         *=======================================
         *  交易相关API
         *=======================================
         */
        //获取单笔订单
        'trade.get' => ['uses' => 'systrade_api_getTradeInfo@getData', 'version'=>['v1']],
        //商家获取单笔订单的数据
        'trade.shop.get' => ['uses' => 'systrade_api_getTradeInfoByShop@getData', 'version'=>['v1'], 'oauth'=>true, 'level'=>70],
        //获取单笔子订单交易信息
        'trade.order.get' => ['uses' => 'systrade_api_getOrderInfo@getData', 'version'=>['v1']],
        //获取多条子订单列表信息
        'trade.order.list.get' => ['uses' => 'systrade_api_getOrderList@getData', 'version'=>['v1']],

        //商家取消订单
        'trade.cancel' => ['uses' => 'systrade_api_trade_cancel_closeByShop@close', 'version'=>['v1'], 'oauth'=>true,'level'=>70],
        //申请取消订单
        'trade.cancel.create' => ['uses' => 'systrade_api_trade_cancel_create@cancelTrade', 'version'=>['v1']],
        //获取取消订单列表
        'trade.cancel.list.get' => ['uses' => 'systrade_api_trade_cancel_list@get', 'version'=>['v1']],
        //获取取消订单明细详情
        'trade.cancel.get' => ['uses' => 'systrade_api_trade_cancel_get@get', 'version'=>['v1']],
        //取消订单退款成功后的操作
        'trade.cancel.succ' => ['uses' => 'systrade_api_trade_cancel_succdo@succdo', 'version'=>['v1']],
        //取消订单商家审核
        'trade.cancel.shop.check' => ['uses' => 'systrade_api_trade_cancel_shopreply@reply', 'version'=>['v1']],

        //获取订单金额
        'trade.money.get' => ['uses' => 'systrade_api_tradeMoney@getList', 'version'=>['v1']],
        // 统计会员使用某促销的次数
        'trade.promotion.applynum' => ['uses' => 'systrade_api_countPromotion@countPromotion', 'version'=>['v1']],
        // 更新优惠券到购物车
        'trade.cart.cartCouponAdd' => ['uses' => 'systrade_api_cartCouponAdd@cartCouponAdd', 'version'=>['v1']],
        // 取消结算页使用优惠券
        'trade.cart.cartCouponCancel' => ['uses' => 'systrade_api_cartCouponCancel@cartCouponCancel', 'version'=>['v1']],
        //订单结算使用购物券
        'trade.cart.voucher.add'      => ['uses' => 'systrade_api_voucher_cartAdd@handle', 'version'=>['v1']],
        'trade.cart.voucher.cancel'      => ['uses' => 'systrade_api_voucher_cartCancel@handle', 'version'=>['v1']],
        // 获取购物车信息
        'trade.cart.getCartInfo' => ['uses' => 'systrade_api_cart_getCartInfo@getCartInfo', 'version'=>['v1']],
        // 获取简单购物车信息
        'trade.cart.getBasicCartInfo' => ['uses' => 'systrade_api_cart_getBasicCartInfo@getBasicCartInfo', 'version'=>['v1']],
        // 获取购物车商品数量
        'trade.cart.getCount' => ['uses' => 'systrade_api_cart_getCount@getCount', 'version'=>['v1']],
        //子订单售后状态更新
        'order.aftersales.status.update' => ['uses' => 'systrade_api_order_status@update', 'version'=>['v1']],

        //买家对订单发起投诉
        'trade.order.complaints.create' => ['uses' => 'systrade_api_complaints_create@create', 'version'=>['v1']],
        //平台对订单投诉同步处理结果
        'trade.order.complaints.process' => ['uses' => 'systrade_api_complaints_process@process', 'version'=>['v1']],
        //根据自订单号获取单个订单投诉详情
        'trade.order.complaints.info' => ['uses' => 'systrade_api_complaints_info@get', 'version'=>['v1']],
        //买家撤销投诉
        'trade.order.complaints.buyer.close' => ['uses' => 'systrade_api_complaints_buyerClose@close', 'version'=>['v1']],
        'trade.order.complaints.list' => ['uses' => 'systrade_api_complaints_list@getList', 'version'=>['v1']],

        //获取订单列表
        'trade.get.list' => ['uses' => 'systrade_api_trade_list@tradeList', 'version'=>['v1']],

        //商家获取订单列表
        'trade.get.shop.list' => ['uses' => 'systrade_api_trade_listByShop@tradeList', 'version'=>['v1']],

        //订单完成状态更改
        'trade.confirm' => ['uses' => 'systrade_api_trade_confirm@confirmTrade', 'version'=>['v1']],
        //订单价格调整
        'trade.update.price' => ['uses' => 'systrade_api_trade_updatePrice@tradePriceUpdate', 'version'=>['v1']],

        //订单发货
        'trade.delivery' => ['uses' => 'systrade_api_trade_delivery@deliveryTrade', 'version'=>['v1'],'oauth'=>true, 'level'=>70],
        'logistics.shop.trade.delivery' => ['uses' => 'syslogistics_api_tradeDelivery@deliveryTrade', 'version'=>['v1']],
        //自提订单发送短信提货码
        'trade.shop.delivery.vcode.send' => ['uses' => 'systrade_api_trade_ziti_sendDeliveryVcode@send', 'version'=>['v1']],
        //自提订单验证提货码
        'trade.shop.delivery.vcode.verify' => ['uses' => 'systrade_api_trade_ziti_verifyDeliveryVcode@verify', 'version'=>['v1']],

        'trade.shop.delivery.vcode.get' => ['uses' => 'systrade_api_trade_ziti_getDeliveryVcode@get', 'version'=>['v1']],

        //订单支付状态更改
        'trade.pay.finish' => ['uses' => 'systrade_api_trade_payFinish@tradePay', 'version'=>['v1']],
        'trade.update.hongbao.money' => ['uses' => 'systrade_api_trade_updateHongbaoPayMoney@update', 'version'=>['v1']],

        //购物车数据删除
        'trade.cart.delete' => ['uses' => 'systrade_api_cart_deleteCart@deleteCart', 'version'=>['v1']],
        //购物车数据更新
        'trade.cart.update' => ['uses' => 'systrade_api_cart_updateCart@updateCart', 'version'=>['v1']],
        //购物车数据增加
        'trade.cart.add' => ['uses' => 'systrade_api_cart_addCart@addCart', 'version'=>['v1']],
        //订单创建
        'trade.create' => ['uses' => 'systrade_api_trade_create@createTrade', 'version'=>['v1']],
        //计算订单金额（包含运费）
        'trade.price.total' => ['uses' => 'systrade_api_trade_totalPrice@total', 'version'=>['v1']],
        //计算订单数量
        'trade.count' => ['uses' => 'systrade_api_trade_count@tradeCount', 'version'=>['v1']],
        //未评价订单统计
        'trade.notrate.count' => ['uses' => 'systrade_api_trade_notRateCount@count', 'version'=>['v1']],
        //商家添加订单备注
        'trade.add.memo' => ['uses' => 'systrade_api_trade_addMemo@add', 'version'=>['v1']],
        //用户购买记录
        'trade.user.buyerList' => ['uses' => 'systrade_api_getUserBuyerList@get', 'version' => ['v1']],

        'clearing.subsidy.voucher.detail.list' => ['uses' => 'sysclearing_api_getVoucherSubsidyDetail@handle', 'version' => ['v1']],
        'clearing.subsidy.voucher.basic.shop' => ['uses' => 'sysclearing_api_basicShopVoucherSubsidy@handle', 'version' => ['v1']],

        //当线下支付时，商家有权进行确认收款和确认收货
        'trade.moneyAndGoods.receipt' => ['uses' => 'systrade_api_trade_moneyAndGoods@receipt', 'version' => ['v1']],
        //结算明细创建
        'clearing.detail.add' => ['uses' => 'sysclearing_api_createClearingDetail@add', 'version' => ['v1']],
        'clearing.detail.getlist' => ['uses' => 'sysclearing_api_getSettlementDetailList@getList', 'version' => ['v1']],
        'clearing.getList' => ['uses' => 'sysclearing_api_getSettlementList@getList', 'version' => ['v1']],
        //根据商品信息获取子订单列表
        'trade.order.list.item' =>['uses' => 'systrade_api_getOrderListByItem@getData', 'version' => ['v1']],
        //更新订单结算状态
        'update.settleStatus' =>['uses' => 'systrade_api_trade_updateSettleStatus@update', 'version' => ['v1']],
        /*
         *  商品相关API
         *=======================================
         */
        //库存报警
        'item.store.police' => ['uses' => 'sysitem_api_item_storePolice@storePolice', 'version'=>['v1']],
        //库存报警总数
        'item.store.police.count' => ['uses' => 'sysitem_api_item_storePoliceCount@storePolice', 'version'=>['v1']],
         //库存报警
        'item.store.police.add' => ['uses' => 'sysshop_api_shop_saveStorePolice@saveStorePolice', 'version'=>['v1']],
         //库存报警信息
        'item.store.info' => ['uses' => 'sysshop_api_shop_getStorePolice@getStorePolice', 'version'=>['v1']],
        //订单取消时恢复库存
        'item.store.recover' => ['uses' => 'sysitem_api_item_recoverStore@storeRecover', 'version'=>['v1']],
        //下单或支付时扣减库存
        'item.store.minus' => ['uses' => 'sysitem_api_item_minusStore@storeMinus', 'version'=>['v1']],
        //商家通过bn修改商品库存
        'item.shop.store.update' => ['uses' => 'sysitem_api_item_updateStore@updateStore', 'version'=>['v1'], 'oauth'=>true, 'level'=>70],
        //修改商品销量
        'item.updateSoldQuantity' => ['uses' => 'sysitem_api_updateSoldQuantity@updateSoldQuantity', 'version'=>['v1']],
        //修改评论数量
        'item.updateRateQuantity' => ['uses' => 'sysitem_api_updateRateQuantity@update', 'version'=>['v1']],
        //获取单个商品详细信息
        'item.get' => ['uses' => 'sysitem_api_item_get@get', 'version'=>['v1'], 'oauth'=>true, 'level'=>70],
        //商品id列表，多个item_id用逗号隔开 调用一次不超过20个
        'item.list.get' => ['uses' => 'sysitem_api_item_list@get', 'version'=>['v1'], 'oauth'=>true, 'level'=>70],
        //商品搜索
        'item.search' => ['uses' => 'sysitem_api_search_searchItems@getList', 'version'=>['v1'], 'oauth'=>true, 'level'=>70],
        //获取指定商品的货品
        'item.sku.list' => ['uses' => 'sysitem_api_item_getSkuList@getList', 'version'=>['v1']],
        //根据sku_id获取货品数据
        'item.sku.get' => ['uses' => 'sysitem_api_item_getSkuGet@get', 'version'=>['v1'], 'oauth'=>true, 'level'=>70],

        //搜索商品给出渐进式的筛选项
        'item.search.filterItems' => ['uses' => 'sysitem_api_search_filterItems@get', 'version'=>['v1']],

        // 更新商品促销标签
        'item.promotion.addTag' => ['uses' => 'sysitem_api_promotion_itemPromotionTagAdd@itemPromotionTagAdd', 'version'=>['v1']],
        // 删除商品的某个促销标签
        'item.promotion.deleteTag' => ['uses' => 'sysitem_api_promotion_itemPromotionTagDelete@itemPromotionTagDelete', 'version'=>['v1']],
        // 获取商品的促销标签及促销信息
        'item.promotion.get' => ['uses' => 'sysitem_api_promotion_itemPromotionTagGet@itemPromotionTagGet', 'version'=>['v1']],
        // 批量获取商品的促销标签及促销信息
        'item.promotion.list' => ['uses' => 'sysitem_api_promotion_itemPromotionTagList@itemPromotionTagList', 'version'=>['v1']],


        //统计商品的数量
        'item.count' => ['uses' => 'sysitem_api_item_count@itemCount', 'version'=>['v1']],
        //获取商品统计数据
        'item.get.count' => ['uses' => 'sysitem_api_item_getCount@get', 'version'=>['v1']],
        //商品添加
        'item.create' => ['uses' => 'sysitem_api_item_create@itemCreate', 'version'=>['v1']],
        //商品上下架状态修改
        'item.sale.status' => ['uses' => 'sysitem_api_item_updateStatus@updateStatus', 'version'=>['v1']],
        // 商品删除
        'item.delete' => ['uses' => 'sysitem_api_item_delete@itemDelete', 'version'=>['v1']],
        // 商品的运费模板修改
        'item.update.dlytmpl' => ['uses' => 'sysitem_api_item_updateDlytmpl@updateDlytmpl', 'version'=>['v1']],
        //获取商品的自然属性
        'item.get.nature.prop' =>['uses' => 'sysitem_api_itemNatureProp@getItemNatutrProp','version'=>['v1']],

        //获取sku的list
        'sku.list' =>['uses' => 'sysitem_api_sku_list@get','version'=>['v1']],

        //根据搜索条件获取sku list
        'sku.search' =>['uses' => 'sysitem_api_sku_search@getList','version'=>['v1']],

        // 根据商品搜索条件和库存报警量获取商品列表
        'search.item.oversku' =>['uses' => 'sysitem_api_search_searchItemsByoversku@getItemList','version'=>['v1']],

        /*
         *=======================================
         *  评价系统相关API
         *=======================================
         */
        //新增订单评论，包含多个子订单的评论一起,店铺评分
        'rate.add' => ['uses' => 'sysrate_api_add@add', 'version'=>['v1']],
        //删除评价
        'rate.delete' => ['uses' => 'sysrate_api_delete@del', 'version'=>['v1']],
        //商家申诉修改评价成功
        'rate.update' => ['uses' => 'sysrate_api_update@update', 'version'=>['v1']],
        //获取单条评论详情
        'rate.get' => ['uses' => 'sysrate_api_info@getData', 'version'=>['v1']],
        //获取评论列表，支持分页
        'rate.list.get' => ['uses' => 'sysrate_api_list@getData', 'version'=>['v1']],
        //商家解释，回复评论
        'rate.reply.add' => ['uses' => 'sysrate_api_reply@add', 'version'=>['v1']],
        //将评论的实名修改为匿名，但是修改为匿名之后则不能再次修改为实名
        'rate.set.anony' => ['uses' => 'sysrate_api_anony@set', 'version'=>['v1']],
        //追评
        'rate.append' => ['uses' => 'sysrate_api_append_add@add', 'version'=>['v1']],
        //回复追评
        'rate.append.reply' => ['uses' => 'sysrate_api_append_reply@reply', 'version'=>['v1']],

        //商家对评论进行申诉
        'rate.appeal.add' => ['uses' => 'sysrate_api_appeal_add@add', 'version'=>['v1']],
        //平台对商家评论申诉进行审核
        'rate.appeal.check' => ['uses' => 'sysrate_api_appeal_check@check', 'version'=>['v1']],

        //获取店铺动态评分
        'rate.dsr.get' => ['uses' => 'sysrate_api_dsr_get@getData', 'version'=>['v1']],

        'feedback.add' => ['uses' =>'sysrate_api_addFeedback@doSave', 'version'=>['v1']],
        //评分统计
        'rate.count' => ['uses' => 'sysrate_api_countRate@countRate','version' =>['v1']],

        //获取咨询列表，支持分页
        'rate.gask.list' =>['uses'=>'sysrate_api_consultation_list@getData','version'=>['v1']],
        //删除商品咨询或者回复
        'rate.gask.delete' =>['uses'=>'sysrate_api_consultation_delete@deleteConsultation','version'=>['v1']],
        //商品咨询新增
        'rate.gask.create' =>['uses'=>'sysrate_api_consultation_create@create','version'=>['v1']],
        //统计咨询
        'rate.gask.count' =>['uses'=>'sysrate_api_consultation_count@countAsk','version'=>['v1']],
        //商家回复咨询
        'rate.gask.reply' =>['uses'=>'sysrate_api_consultation_reply@doReply','version'=>['v1']],
        'rate.gask.display' =>['uses'=>'sysrate_api_consultation_update@update','version'=>['v1']],

        /*
         *=======================================
         *  会员相关
         *=======================================
         */

        //获取积分平摊值
        'point.deduction.num' => ['uses' => 'sysuser_api_point_computeDeduction@compute', 'version' => ['v1']],
        //获取系统积分的配置信息
        'point.setting.get' => ['uses' => 'sysuser_api_point_setting@get', 'version' => ['v1']],
        //更新会员的积分总额
        'user.updateUserPoint' => ['uses' => 'sysuser_api_point_update@updateUserPoint', 'version' => ['v1']],
        //更新会员的成长值总额
        'user.updateUserExp' => ['uses' => 'sysuser_api_exp_update@updateUserExp', 'version' => ['v1']],
        //获取积分记录列表
        'user.pointGet' => ['uses' => 'sysuser_api_point_list@getList', 'version' => ['v1']],
        //获取指定会员的积分值
        'user.point.get' => ['uses' => 'sysuser_api_point_get@get', 'version' => ['v1']],
        //获取成长值记录列表
        'user.experienceGet' => ['uses' => 'sysuser_api_exp_list@getList', 'version' => ['v1']],
        //获取等级列表
        'user.grade.fullinfo' => ['uses' => 'sysuser_api_grade_fullinfo@fullinfo', 'version' => ['v1']],
        // 获取会员基本等级信息
        'user.grade.basicinfo' => ['uses' => 'sysuser_api_grade_basicinfo@basicinfo', 'version' => ['v1']],
        // 获取系统会员等级列表
        'user.grade.list' => ['uses' => 'sysuser_api_grade_list@gradeList', 'version' => ['v1']],
        //获取等级列表
        'user.pointcount' => ['uses' => 'sysuser_api_point_count@count', 'version' => ['v1']],
        //获取用户的优惠券列表
        'user.coupon.list' => ['uses' => 'sysuser_api_couponList@couponList', 'version' => ['v1']],
        //获取用户的优惠券数量
        'user.coupon.count' => ['uses' => 'sysuser_api_couponCount@couponCount', 'version' => ['v1']],
        //获取用户领取的优惠券信息
        'user.coupon.get' => ['uses' => 'sysuser_api_couponGet@couponGet', 'version' => ['v1']],
        // 取消订单返还优惠券
        'user.coupon.back' => ['uses' => 'sysuser_api_couponBack@couponBack', 'version' => ['v1']],
        //领取优惠券
        'user.coupon.getCode' => ['uses' => 'sysuser_api_getCoupon@getCoupon', 'version' => ['v1']],
        // 删除用户的优惠券
        'user.coupon.remove' => ['uses' => 'sysuser_api_couponDelete@couponDelete', 'version' => ['v1']],
        // 更新会员的优惠券信息
        'user.coupon.useLog' => ['uses' => 'sysuser_api_couponUseLog@couponUseLog', 'version' => ['v1']],
        //过期优惠券状态更改
        'user.coupon.expire' => ['uses' => 'sysuser_api_expireUserCoupon@updateCoupon', 'version' => ['v1']],
        //获取商品收藏列表
        'user.itemcollect.list' => ['uses' => 'sysuser_api_getItemCollectList@getItemCollectList', 'version' => ['v1']],
        'user.itemcollect.count' => ['uses' => 'sysuser_api_countCollectItem@getCount', 'version' => ['v1']],
        //商品收藏添加
        'user.itemcollect.add' => ['uses' => 'sysuser_api_addCollectItem@addItemCollect', 'version' => ['v1']],
        //商品收藏删除
        'user.itemcollect.del' => ['uses' => 'sysuser_api_delCollectItem@delItemCollect', 'version' => ['v1']],
        //获取商品收藏列表
        'user.shopcollect.list' => ['uses' => 'sysuser_api_getShopCollectList@getShopCollectList', 'version' => ['v1']],
        'user.shopcollect.count' => ['uses' => 'sysuser_api_countCollectShop@getCount', 'version' => ['v1']],
        //店铺收藏添加
        'user.shopcollect.add' => ['uses' => 'sysuser_api_addCollectShop@addCollectShop', 'version' => ['v1']],
        //店铺收藏删除
        'user.shopcollect.del' => ['uses' => 'sysuser_api_delCollectShop@delCollectShop', 'version' => ['v1']],
        //收藏信息
        'user.collect.info' => ['uses' => 'sysuser_api_getCollectInfo@getCollectInfo', 'version' => ['v1']],
        //会员地址添加
        'user.address.add' => ['uses' => 'sysuser_api_addUserAdress@addUserAdress', 'version' => ['v1']],
        //会员地址默认设置
        'user.address.setDef' => ['uses' => 'sysuser_api_addressSetDef@addressSetDef', 'version' => ['v1']],
        //删除会员地址
        'user.address.del' => ['uses' => 'sysuser_api_delUserAddress@delUserAddress', 'version' => ['v1']],
        //获取会员目前地址数量和地址最大限制数量
        'user.address.count' => ['uses' => 'sysuser_api_getAddrCount@getAddrCount', 'version' => ['v1']],
        //获取会员地址列表
        'user.address.list' => ['uses' => 'sysuser_api_getAddrList@getAddrList', 'version' => ['v1']],
        'user.address.info' => ['uses' => 'sysuser_api_getAddrInfo@getAddrInfo', 'version' => ['v1']],
        //会员添加
        'user.create' => ['uses' => 'sysuser_api_user_create@add', 'version' => ['v1']],

        //添加签到记录
        'user.add.checkin.log' => ['uses' => 'sysuser_api_addCheckinLog@add', 'version' => ['v1']],
        //获取用户签到记录
        'user.get.checkin.info' => ['uses' => 'sysuser_api_getCheckinInfo@getCheckinInfo', 'version' => ['v1']],
        //获取用户登录信息
        'user.get.account.info' => ['uses' => 'sysuser_api_user_account_getInfo@get', 'version' => ['v1']],
        //根据会员ID获取对应的用户名
        'user.get.account.name' => ['uses' => 'sysuser_api_user_account_getName@getName', 'version' => ['v1']],
        //根据用户名/手机/邮箱 获取会员ID
        'user.get.account.id' => ['uses' => 'sysuser_api_user_account_getIdByName@getId', 'version' => ['v1']],
        //根据用户名获取用户类型
        'user.get.account.type' => ['uses' => 'sysuser_api_user_account_checkLoginUserType@checkType', 'version' => ['v1']],

        // 信任登陆绑定
        'user.trust.authorize' => ['uses' => 'sysuser_api_user_trust_authorize@authorize', 'version' => ['v1']],
        //

        //获取用户详细信息
        'user.get.info' => ['uses' => 'sysuser_api_user_getUserInfo@getList', 'version' => ['v1']],

        //用户基本信息更新
        'user.basics.update' => ['uses' => 'sysuser_api_user_basicsUpdate@update', 'version' => ['v1']],
        //用户密码修改
        'user.pwd.update' => ['uses' => 'sysuser_api_user_account_updatePwd@passwordUpdate', 'version' => ['v1']],
        //修改用户登录信息
        'user.account.update' => ['uses' => 'sysuser_api_user_account_updateAccount@accountUpdate', 'version' => ['v1']],
//      //用户是否登陆状态
//      'user.check' => ['uses' => 'sysuser_api_user_account_check@check', 'version' => ['v1']],
        //用户登录
        'user.login' => ['uses' => 'sysuser_api_user_account_login@userLogin', 'version' => ['v1']],
        //用户退出
        //'user.logout' => ['uses' => 'sysuser_api_user_account_logout@userLogout', 'version' => ['v1']],
        //检测会员登录密码
        'user.login.pwd.check' => ['uses' => 'sysuser_api_user_account_checkLoginPwd@checkPwd', 'version' => ['v1']],
        //邮箱验证
        'user.email.verify' => ['uses' => 'sysuser_api_user_verifyEmail@verifyEmail', 'version' => ['v1']],
        //邮件订阅
        'user.notifyitem' => ['uses' => 'sysuser_api_addUserNotifyItem@addUserNotifyItem', 'version' => ['v1']],
        'user.updatenotifyitem' => ['uses' => 'sysuser_api_updateUserNotifyItem@updateUserNotifyItem', 'version' => ['v1']],
        'user.notifyItemList' => ['uses' => 'sysuser_api_getUserNotifyItemList@getUserNotifyItemList', 'version' => ['v1']],

        //设置支付密码
        'user.deposit.password.set' =>['uses' => 'sysuser_api_user_deposit_setPassword@setPassword', 'version' => ['v1']],
        //修改支付密码
        'user.deposit.password.change' =>['uses' => 'sysuser_api_user_deposit_changePassword@changePassword', 'version' => ['v1']],
        //判断是否存在支付密码
        'user.deposit.password.has' =>['uses' => 'sysuser_api_user_deposit_hasPassword@hasPassword', 'version' => ['v1']],
        // 验证原支付密码
        'user.check.deposit.oldpwd' =>['uses' => 'sysuser_api_user_deposit_checkOldPassword@checkOldPwd', 'version' => ['v1']],

        // 验证支付密码和登录密码不能一致
        'user.check.loginPwd.DepositPwd' =>['uses' => 'sysuser_api_user_checkloginPwdAndDepositPwd@checkpwd', 'version' => ['v1']],

        //用户领取红包
        'user.hongbao.get' => ['uses' => 'sysuser_api_user_hongbao_getHongbao@get', 'version' => ['v1']],
        //用户使用红包
        'user.hongbao.use' => ['uses' => 'sysuser_api_user_hongbao_useHongbao@useHongbao', 'version' => ['v1']],

        'user.hongbao.refund' => ['uses' => 'sysuser_api_user_hongbao_refundHongbao@get', 'version' => ['v1']],

        'user.hongbao.list.get' => ['uses' => 'sysuser_api_user_hongbao_list@get', 'version' => ['v1']],

        'user.hongbao.count' => ['uses' => 'sysuser_api_user_hongbao_count@get', 'version' => ['v1']],

        //通过这个领取的红包会在异次元空间里，需要走receive接口才能领到
        'user.hongbao.tmp.get'     => ['uses' => 'sysuser_api_user_hongbao_getTmpHongbao@get', 'version' => ['v1']],
        //这个接口可以从异次元中把红包拿回来
        'user.hongbao.tmp.receive' => ['uses' => 'sysuser_api_user_hongbao_receiveTmpHongbao@receive', 'version' => ['v1']],
        //这个接口可以查看会员丢在异次元空间中的红包
        'user.hongbao.tmp.list' => ['uses' => 'sysuser_api_user_hongbao_tmpHongbaoList@get', 'version' => ['v1']],

        //消费浏览商品历史纪录存储
        'user.browserHistory.set' =>['uses' => 'sysuser_api_user_browserHistory_store@store', 'version' => ['v1']],
        //获取指定消费者浏览商品历史纪录
        'user.browserHistory.get' =>['uses' => 'sysuser_api_user_browserHistory_get@get', 'version' => ['v1']],

        //记录会员登录日志
        'user.login.log.add' => ['uses' =>'sysuser_api_user_addLoginLog@addLog','version'=>['v1']],
        //会员获取购物券
        'user.voucher.code.get' => ['uses' =>'sysuser_api_user_voucher_genCode@handle','version'=>['v1']],
        'user.voucher.list.get' => ['uses' =>'sysuser_api_user_voucher_list@handle','version'=>['v1']],
        'user.voucher.get' => ['uses' =>'sysuser_api_user_voucher_get@handle','version'=>['v1']],
        'user.voucher.used' => ['uses' =>'sysuser_api_user_voucher_used@handle','version'=>['v1']],
        'user.voucher.stop' => ['uses' =>'sysuser_api_user_voucher_stop@handle','version'=>['v1']],
        'user.voucher.back' => ['uses' =>'sysuser_api_user_voucher_back@handle','version'=>['v1']],

        'user.list.byfilter' => ['uses' =>'sysuser_api_user_getUserListByFilter@getList','version'=>['v1']],

        /*
         *=======================================
         *类目相关
         *=======================================
         */
        //获取类目单条信息
        'category.cat.get.info' => ['uses' => 'syscategory_api_cat_getinfo@getList', 'version' => ['v1']],
        //获取指定一级类目以及他的二三级类目树形结构
        'category.cat.get' => ['uses' => 'syscategory_api_cat_get@getList', 'version' => ['v1']],
        //获取类目列表（所有类目树形结构）
        'category.cat.get.list' => ['uses' => 'syscategory_api_cat_list@getList', 'version' => ['v1']],
        //类目删除
        'category.cat.remove' => ['uses' => 'syscategory_api_cat_remove@toRemove', 'version' => ['v1']],
        //获取指定类目和他的父级类目信息
        'category.cat.get.data' => ['uses' => 'syscategory_api_cat_getData@getList', 'version' => ['v1']],
        // 根据任意类目id获取对应类目的叶子类目ID
        'category.cat.get.leafCatId' => ['uses' => 'syscategory_api_cat_getLeafCatId@getLeafCatId', 'version' => ['v1']],
        //获取品牌详情
        'category.brand.get.info' => ['uses' => 'syscategory_api_brand_getInfo@get', 'version' => ['v1']],
        //获取品牌列表
        'category.brand.get.list' => ['uses' => 'syscategory_api_brand_getList@get', 'version' => ['v1']],
        //运营商品牌添加
        'category.brand.add' => ['uses' => 'syscategory_api_brand_add@addBrand', 'version' => ['v1']],
        //运营商品牌修改
        'category.brand.update' => ['uses' => 'syscategory_api_brand_update@updateBrand', 'version' => ['v1']],
        //获取指定店铺或者指定类目关联的品牌(cat_id 必填)
        'category.get.cat.rel.brand' => ['uses' => 'syscategory_api_getCatRelBrand@getData', 'version' => ['v1']],

        //获取指定的三级类目关联的属性
        'category.catprovalue.get' => ['uses' => 'syscategory_api_getCatProValue@getCatProValue', 'version' => ['v1']],
        //获取属性列表
        'category.prop.list' => ['uses' => 'syscategory_api_getPropList@getList', 'version' => ['v1']],


        /*
         *=======================================
         *虚拟分类相关
         *=======================================
         */
        //获取虚拟类目信息
        'category.virtualcat.info' => ['uses' => 'syscategory_api_virtualcat_virtualcatInfo@getInfo', 'version' => ['v1']],
        //获取分类类目列表（所有类目树形结构）
        'category.virtualcat.get.list' => ['uses' => 'syscategory_api_virtualcat_virtualcatList@getList', 'version' => ['v1']],
        //获取的父类下子类列表
        'category.virtualcat.get' => ['uses' => 'syscategory_api_virtualcat_get@get', 'version' => ['v1']],
        //根据三级分类id获取相关信息
        'category.virtualcat.getData' => ['uses' => 'syscategory_api_virtualcat_getData@getList', 'version' => ['v1']],

        /*
         *=======================================
         *店铺相关
         *=======================================
         */
        //检测店铺名称
        'shop.name.check' => ['uses' => 'sysshop_api_checkShopName@check','version'=>['v1']],
        //获取店铺签约的类目和品牌的id（只对内）
        'shop.authorize.catbrandids.get' => ['uses' => 'sysshop_api_shopAuthorize@getCatBrand', 'version' => ['v1']],
        //获取店铺自有类目(树形)
        'shop.cat.get' => ['uses' => 'sysshop_api_getShopCat@getShopCat', 'version' => ['v1']],
        //获取店铺自有类目(普通列表)
        'shop.cat.list' => ['uses' => 'sysshop_api_getShopCatList@getShopCatList', 'version' => ['v1']],
        //获取店铺基本信息
        'shop.get' => ['uses' => 'sysshop_api_shop_get@get', 'version' => ['v1']],
        //根据店铺名称查询店铺列表数据
        'shop.get.search' => ['uses' => 'sysshop_api_shop_search@getList', 'version' => ['v1']],
        //根据店铺ID获取店铺列表数据
        'shop.get.list' => ['uses' => 'sysshop_api_shop_list@getList', 'version' => ['v1']],
        //获取店铺详细信息
        'shop.get.detail' => ['uses' => 'sysshop_api_shop_detail@getList', 'version' => ['v1']],
        //获取店铺签约的所有类目费率
        'shop.get.cat.fee' => ['uses' => 'sysshop_api_shop_getCatFee@getCatFee', 'version' => ['v1']],
        //批量获取店铺名称
        'shop.get.shopname' => ['uses' => 'sysshop_api_shop_getName@getList', 'version' => ['v1']],
        //更新店铺基本信息
        'shop.update' => ['uses' => 'sysshop_api_shop_update@update', 'version' => ['v1']],
        //保存店铺通知
        'shop.savenotice' => ['uses' => 'sysshop_api_shop_saveNotice@saveNotice', 'version' => ['v1']],
        //获取店铺通知一条数据
        'shop.get.shopnoticeinfo' => ['uses' => 'sysshop_api_shop_getNoticeInfo@getNoticeInfo', 'version' => ['v1']],
        //获取店铺通数据
        'shop.get.shopnoticelist' => ['uses' => 'sysshop_api_shop_getNoticeList@getNoticeList', 'version' => ['v1']],
        //获取店铺签约品牌
        //'shop.authorize.brand' => ['uses' => 'sysshop_api_getShopAuthorizeBrand@getAuthorizeBrand', 'version' => ['v1']],
        //获取店铺签约类目
        'shop.authorize.cat' => ['uses' => 'sysshop_api_getShopAuthorizeCat@getAuthorizeCat', 'version' => ['v1']],
        //获取当前卖家的店铺id
        'shop.get.loginId' => ['uses' => 'sysshop_api_getShopId@getSellerShopId', 'version' => ['v1']],

        //根据品牌id获取店铺列表
        'shop.get.by.brand' => ['uses' => 'sysshop_api_getShopByBrand@getShop', 'version' => ['v1']],
        //根据类目id获取店铺列表
        'shop.get.by.cat' => ['uses' => 'sysshop_api_getShopByCat@getShop', 'version' => ['v1']],
        //保存店铺分类
        'shop.save.cat' => ['uses' => 'sysshop_api_shop_saveShopCat@saveShopCat', 'version' => ['v1']],

        // 获取店铺二级域名
        'shop.subdomain.get' => ['uses' => 'sysshop_api_shop_getSubdomain@getSubdomain', 'version' => ['v1']],
        // 申请店铺二级域名
        'shop.subdomain.apply' => ['uses' => 'sysshop_api_shop_applySubdomain@applySubdomain', 'version' => ['v1']],
        // 根据二级域名获取店铺shop_id
        'shop.subdomain.getshopid' => ['uses' => 'sysshop_api_shop_getShopIdBySubdomain@getShopId', 'version' => ['v1']],
        // 获取当前所有店铺列表
        'shop.list.get' => ['uses' => 'sysshop_api_getShopList@getShopList', 'version' => ['v1']],
        //获取店铺首页装修详情
        'shop.newIndex' => ['uses' => 'sysshop_api_shop_decorateDetail@get', 'version' => ['v1']],


        /*
         *=======================================
         *店铺入驻相关
         *=======================================
         */
        //获取入驻申请信息
        'shop.get.enterapply' => ['uses' => 'sysshop_api_enterapply_get@get', 'version' => ['v1']],
        //入驻申请创建
        'shop.create.enterapply' => ['uses' => 'sysshop_api_enterapply_create@create', 'version' => ['v1']],
        //入驻申请修改
        'shop.update.enterapply' => ['uses' => 'sysshop_api_enterapply_update@update', 'version' => ['v1']],
        //检测该品牌是否以后店铺签约为旗舰店
        'shop.check.brand.sign' => ['uses' => 'sysshop_api_enterapply_getSignBrand@getSignBrand', 'version' => ['v1']],
        'shop.type.get' => ['uses' => 'sysshop_api_getShopType@getList', 'version' => ['v1']],
        'shop.type.getinfo' => ['uses' => 'sysshop_api_getShopTypeInfo@getShopTypeInfo', 'version' => ['v1']],


        'shop.dlycorp.getinfo' => ['uses' => 'sysshop_api_shop_getDlycorpInfo@get', 'version' => ['v1']],
        'shop.dlycorp.getlist' => ['uses' => 'sysshop_api_shop_getDlycorp@getList', 'version' => ['v1']],
        'shop.dlycorp.save' => ['uses' => 'sysshop_api_shop_saveDlycorp@savedata', 'version' => ['v1']],
        'shop.dlycorp.remove' => ['uses' => 'sysshop_api_shop_removeShopDlycorp@remove', 'version' => ['v1']],

        'shop.apply.cat.save' => ['uses' => 'sysshop_api_applycat_saveApplyCat@saveData', 'version' => ['v1']],
        'shop.apply.cat.getlist' => ['uses' => 'sysshop_api_applycat_getApplyList@getAppleyList', 'version' => ['v1']],
        'shop.apply.cat.remove' => ['uses' => 'sysshop_api_applycat_removeApply@removeApply', 'version' => ['v1']],
        'shop.apply.cat.get' => ['uses' => 'sysshop_api_applycat_getApply@getAppleyList', 'version' => ['v1']],

        /*
         *=======================================
         *营销相关
         *=======================================
         */
        // 获取商家所有可用促销活动列表
        'promotion.promotion.list.get' =>['uses' => 'syspromotion_api_promotionList@getList', 'version'=>['v1']],
        // 获取过期促销id
        'promotion.overdue.get' => ['uses' => 'syspromotion_api_overdueGet@overdueGet', 'version'=>['v1']],
        // 获取各促销信息的中转api
        'promotion.promotion.get' => ['uses' => 'syspromotion_api_promotionGet@promotionGet', 'version' => ['v1']],
        'promotion.promotion.list.tag' => ['uses' => 'syspromotion_api_promotionListTag@getlist', 'version' => ['v1']],

        'promotion.voucher.add' => ['uses' => 'syspromotion_api_voucher_add@add', 'version' => ['v1']],
        'promotion.voucher.update' => ['uses' => 'syspromotion_api_voucher_update@update', 'version' => ['v1']],
        'promotion.voucher.get' => ['uses' => 'syspromotion_api_voucher_get@get', 'version' => ['v1']],
        'promotion.voucher.stop' => ['uses' => 'syspromotion_api_voucher_stop@update', 'version' => ['v1']],
        'promotion.voucher.shop.list.get' => ['uses' => 'syspromotion_api_voucher_listByShop@get', 'version' => ['v1']],
        'promotion.voucher.register' => ['uses' => 'syspromotion_api_voucher_register@handle', 'version' => ['v1']],
        'promotion.voucher.register.approve' => ['uses' => 'syspromotion_api_voucher_registerApprove@handle', 'version' => ['v1']],
        'promotion.voucher.register.get' => ['uses' => 'syspromotion_api_voucher_getRegister@handle', 'version' => ['v1']],
        'promotion.voucher.register.stop' => ['uses' => 'syspromotion_api_voucher_registerStop@handle', 'version' => ['v1']],
        'promotion.voucher.code.get' => ['uses' => 'syspromotion_api_voucher_genCode@handle', 'version' => ['v1']],
        'promotion.voucher.code.usedQuantity' => ['uses' => 'syspromotion_api_voucher_upUsedCodeQuantity@handle', 'version' => ['v1']],
        'promotion.voucher.list.get' => ['uses' => 'syspromotion_api_voucher_list@handle', 'version' => ['v1']],

        // 优惠券接口
        'promotion.coupon.add' => ['uses' => 'syspromotion_api_coupon_couponAdd@couponAdd', 'version' => ['v1']],
        'promotion.coupon.update' => ['uses' => 'syspromotion_api_coupon_couponUpdate@couponUpdate', 'version' => ['v1']],
        'promotion.coupon.delete' => ['uses' => 'syspromotion_api_coupon_couponDelete@couponDelete', 'version' => ['v1']],
        'promotion.coupon.get' => ['uses' => 'syspromotion_api_coupon_couponGet@couponGet', 'version' => ['v1']],
        'promotion.coupon.list' => ['uses' => 'syspromotion_api_coupon_couponList@couponList', 'version' => ['v1']],
        'promotion.coupon.gencode' => ['uses' => 'syspromotion_api_coupon_couponGenCode@couponGenCode', 'version' => ['v1']],
        'promotion.coupon.use' => ['uses' => 'syspromotion_api_coupon_couponUse@couponUse', 'version' => ['v1']],// 结算页使用优惠券
        'promotion.coupon.apply' => ['uses' => 'syspromotion_api_coupon_couponApply@couponApply', 'version' => ['v1']],
        'promotion.couponitem.list' => ['uses' => 'syspromotion_api_coupon_couponItemList@couponItemList', 'version' => ['v1']],
        'promotion.coupon.list.byid' => ['uses' => 'syspromotion_api_coupon_couponListById@getList', 'version' => ['v1']],
        'promotion.coupon.cancel' => ['uses' => 'syspromotion_api_coupon_couponCancel@couponCancel', 'version' => ['v1']],
        'promotion.coupon.updateCouponQuantity' => ['uses' => 'syspromotion_api_coupon_updateCouponQuantity@updateCouponQuantity', 'version' => ['v1']],
        'promotion.coupon.approve' => ['uses' => 'syspromotion_api_coupon_couponApprove@approve', 'version' => ['v1']],
        // 满减接口
        'promotion.fullminus.add' => ['uses' => 'syspromotion_api_fullminus_fullminusAdd@fullminusAdd', 'version' => ['v1']],
        'promotion.fullminus.update' => ['uses' => 'syspromotion_api_fullminus_fullminusUpdate@fullminusUpdate', 'version' => ['v1']],
        'promotion.fullminus.delete' => ['uses' => 'syspromotion_api_fullminus_fullminusDelete@fullminusDelete', 'version' => ['v1']],
        'promotion.fullminus.get' => ['uses' => 'syspromotion_api_fullminus_fullminusGet@fullminusGet', 'version' => ['v1']],
        'promotion.fullminus.list' => ['uses' => 'syspromotion_api_fullminus_fullminusList@fullminusList', 'version' => ['v1']],
        'promotion.fullminus.apply' => ['uses' => 'syspromotion_api_fullminus_fullminusApply@fullminusApply', 'version' => ['v1']],
        'promotion.fullminusitem.list' => ['uses' => 'syspromotion_api_fullminus_fullminusItemList@fullminusItemList', 'version' => ['v1']],
        'promotion.fullminus.cancel' => ['uses' => 'syspromotion_api_fullminus_fullminusCancel@fullminusCancel', 'version' => ['v1']],
        'promotion.fullminus.approve' => ['uses' => 'syspromotion_api_fullminus_fullminusApprove@approve', 'version' => ['v1']],
        // 满折接口
        'promotion.fulldiscount.add' => ['uses' => 'syspromotion_api_fulldiscount_fulldiscountAdd@fulldiscountAdd', 'version' => ['v1']],
        'promotion.fulldiscount.update' => ['uses' => 'syspromotion_api_fulldiscount_fulldiscountUpdate@fulldiscountUpdate', 'version' => ['v1']],
        'promotion.fulldiscount.delete' => ['uses' => 'syspromotion_api_fulldiscount_fulldiscountDelete@fulldiscountDelete', 'version' => ['v1']],
        'promotion.fulldiscount.get' => ['uses' => 'syspromotion_api_fulldiscount_fulldiscountGet@fulldiscountGet', 'version' => ['v1']],
        'promotion.fulldiscount.list' => ['uses' => 'syspromotion_api_fulldiscount_fulldiscountList@fulldiscountList', 'version' => ['v1']],
        'promotion.fulldiscount.apply' => ['uses' => 'syspromotion_api_fulldiscount_fulldiscountApply@fulldiscountApply', 'version' => ['v1']],
        'promotion.fulldiscountitem.list' => ['uses' => 'syspromotion_api_fulldiscount_fulldiscountItemList@fulldiscountItemList', 'version' => ['v1']],
        'promotion.fulldiscount.cancel' => ['uses' => 'syspromotion_api_fulldiscount_fulldiscountCancel@fulldiscountCancel', 'version' => ['v1']],
        'promotion.fulldiscount.approve' => ['uses' => 'syspromotion_api_fulldiscount_fulldiscountApprove@approve', 'version' => ['v1']],
        // X件Y折接口
        'promotion.xydiscount.add' => ['uses' => 'syspromotion_api_xydiscount_xydiscountAdd@xydiscountAdd', 'version' => ['v1']],
        'promotion.xydiscount.update' => ['uses' => 'syspromotion_api_xydiscount_xydiscountUpdate@xydiscountUpdate', 'version' => ['v1']],
        'promotion.xydiscount.delete' => ['uses' => 'syspromotion_api_xydiscount_xydiscountDelete@xydiscountDelete', 'version' => ['v1']],
        'promotion.xydiscount.get' => ['uses' => 'syspromotion_api_xydiscount_xydiscountGet@xydiscountGet', 'version' => ['v1']],
        'promotion.xydiscount.list' => ['uses' => 'syspromotion_api_xydiscount_xydiscountList@xydiscountList', 'version' => ['v1']],
        'promotion.xydiscount.apply' => ['uses' => 'syspromotion_api_xydiscount_xydiscountApply@xydiscountApply', 'version' => ['v1']],
        'promotion.xydiscountitem.list' => ['uses' => 'syspromotion_api_xydiscount_xydiscountItemList@xydiscountItemList', 'version' => ['v1']],
        'promotion.xydiscount.cancel' => ['uses' => 'syspromotion_api_xydiscount_xydiscountCancel@xydiscountCancel', 'version' => ['v1']],
        'promotion.xydiscount.approve' => ['uses' => 'syspromotion_api_xydiscount_xydiscountApprove@approve', 'version' => ['v1']],
        // 组合促销接口
        'promotion.package.add' => ['uses' => 'syspromotion_api_package_packageAdd@packageAdd', 'version' => ['v1']],
        'promotion.package.update' => ['uses' => 'syspromotion_api_package_packageUpdate@packageUpdate', 'version' => ['v1']],
        'promotion.package.delete' => ['uses' => 'syspromotion_api_package_packageDelete@packageDelete', 'version' => ['v1']],
        'promotion.package.get' => ['uses' => 'syspromotion_api_package_packageGet@packageGet', 'version' => ['v1']],
        'promotion.package.list' => ['uses' => 'syspromotion_api_package_packageList@packageList', 'version' => ['v1']],
        'promotion.package.apply' => ['uses' => 'syspromotion_api_package_packageApply@packageApply', 'version' => ['v1']],
        'promotion.packageitem.list' => ['uses' => 'syspromotion_api_package_packageItemList@packageItemList', 'version' => ['v1']],
        'promotion.package.getPackageItemsByItemId' => ['uses' => 'syspromotion_api_package_getPackageItemsByItemId@getPackageItemsByItemId', 'version' => ['v1']],
        'promotion.package.cancel' => ['uses' => 'syspromotion_api_package_packageCancel@packageCancel', 'version' => ['v1']],
        'promotion.package.approve' => ['uses' => 'syspromotion_api_package_packageApprove@approve', 'version' => ['v1']],

        //获取参与活动的商品列表
        'promotion.activity.item.list' => ['uses' => 'syspromotion_api_activity_itemList@getList', 'version' => ['v1']],
        //获取参与活动的商品详情
        'promotion.activity.item.info' => ['uses' => 'syspromotion_api_activity_itemInfo@getInfo', 'version' => ['v1']],
        //获取活动列表
        'promotion.activity.list' => ['uses' => 'syspromotion_api_activity_list@getList', 'version' => ['v1']],
        //获取活动详情
        'promotion.activity.info' => ['uses' => 'syspromotion_api_activity_info@getInfo', 'version' => ['v1']],
        // 报名活动
        'promotion.activity.register' => ['uses' => 'syspromotion_api_activity_register@registerActivity', 'version' => ['v1']],
        // 报名列表
        'promotion.activity.register.list' => ['uses' => 'syspromotion_api_activity_registerList@registerList', 'version' => ['v1']],
        // 活动报名审核
        'promotion.activity.register.approve' => ['uses' => 'syspromotion_api_activity_registerApprove@registerApprove', 'version' => ['v1']],

        //活动开售提醒提交
        'promotion.activity.remind.add' =>['uses' =>'syspromotion_api_remind_addRemind@remindAdd', 'version' => ['v1']],
        'promotion.activity.remind.get' =>['uses' =>'syspromotion_api_remind_getData@get', 'version' => ['v1']],
        'promotion.setting' =>['uses' =>'syspromotion_api_setting@get', 'version' => ['v1']],
        // 促销专题页接口
        'promotion.get.page.info' =>['uses' =>'syspromotion_api_getPageInfo@getInfo', 'version' => ['v1']],
        // 促销专题页接口
        'promotion.get.pagetmpl.info' =>['uses' =>'syspromotion_api_getPagetmpl@getInfo', 'version' => ['v1']],

        //赠品促销相关
        'promotion.gift.add' => ['uses' => 'syspromotion_api_gift_giftAdd@giftAdd','version' => ['v1']],
        'promotion.gift.update' => ['uses' => 'syspromotion_api_gift_giftUpdate@giftUpdate','version' => ['v1']],
        'promotion.gift.list' => ['uses' => 'syspromotion_api_gift_giftList@giftList','version' =>['v1']],
        'promotion.gift.get' => ['uses' => 'syspromotion_api_gift_giftGet@getGift','version' => ['v1']],
        'promotion.gift.delete' => ['uses' => 'syspromotion_api_gift_giftDelete@giftDelete','version' => ['v1']],
        'promotion.gift.cancel' => ['uses' => 'syspromotion_api_gift_giftCancel@giftCancel', 'version' => ['v1']],
        'promotion.gift.item.info' => ['uses' => 'syspromotion_api_gift_itemInfo@getInfo', 'version' => ['v1']],
        'promotion.gift.item.get' => ['uses' => 'syspromotion_api_gift_giftItemGet@getInfo', 'version' => ['v1']],
        'promotion.gift.sku.get' => ['uses' => 'syspromotion_api_gift_giftSkuGet@getInfo', 'version' => ['v1']],
        'promotion.gift.approve' => ['uses' => 'syspromotion_api_gift_giftApprove@approve', 'version' => ['v1']],



        /*---------红包相关-------------*/
        //红包规则创建
        'promotion.hongbao.create' =>['uses' =>'syspromotion_api_hongbao_create@create', 'version' => ['v1']],
        //红包规则列表获取
        'promotion.hongbao.list.get' =>['uses' =>'syspromotion_api_hongbao_list@get', 'version' => ['v1']],
        //红包详情
        'promotion.hongbao.get' =>['uses' =>'syspromotion_api_hongbao_info@get', 'version' => ['v1']],
        //红包发放
        'promotion.hongbao.issued' =>['uses' =>'syspromotion_api_hongbao_getHongbao@get', 'version' => ['v1']],
        //批量发放红包
        'promotion.hongbao.batch.issued' =>['uses' =>'syspromotion_api_hongbao_batchGetHongbao@get', 'version' => ['v1']],
        //红包使用
        'promotion.hongbao.use' =>['uses' =>'syspromotion_api_hongbao_useHongbao@useHongbao', 'version' => ['v1']],

        'promotion.hongbao.updateStatus' =>['uses' =>'syspromotion_api_hongbao_updateStatus@update', 'version' => ['v1']],

        /*---------转盘相关---------------*/
        //获取单个转盘抽奖活动详情
        'promotion.lottery.get' =>['uses' =>'syspromotion_api_lottery_info@get', 'version' => ['v1']],
        //转盘活动更新
        'promotion.lottery.updateStatus' =>['uses' =>'syspromotion_api_lottery_updateStatus@update', 'version' => ['v1']],
        //发放奖励
        'promotion.bonus.issue' =>['uses' =>'syspromotion_api_lottery_bonusIssue@issue', 'version' => ['v1']],
        //更新转盘抽奖收货地址
        'promotion.lottery.updateAddr' =>['uses' =>'syspromotion_api_lottery_updateAddr@update', 'version' => ['v1']],
        //获取中奖记录列表
        'lottery.result.list' =>['uses' =>'syspromotion_api_lottery_list@getList', 'version' => ['v1']],

        /*---------转盘相关---------------*/
        //获取刮刮卡活动详情
        'promotion.scratchcard.get' =>['uses' =>'syspromotion_api_scratchcard_info@get', 'version' => ['v1']],
        //获取刮刮卡列表
        'promotion.scratchcard.list' =>['uses' =>'syspromotion_api_scratchcard_list@scratchcardList', 'version' => ['v1']],
        //会员刮卡
        'promotion.scratchcard.receive' =>['uses' =>'syspromotion_api_scratchcard_receive@receive', 'version' => ['v1']],
        //刮刮卡兑换结果
        'promotion.scratchcard.exchange' =>['uses' =>'syspromotion_api_scratchcard_exchange@exchange', 'version' => ['v1']],

        /*---------发放定向优惠----------------------*/
        'promotion.distribute.create' =>['uses' =>'syspromotion_api_distribute_createDistribute@create', 'version' => ['v1']],

        /*
         *=======================================
         * 支付相关
         *=======================================
         */
        //修改支付单中的应支付的金额
        'payment.money.update' => ['uses' => 'ectools_api_paymentMoney@update', 'version' => ['v1']],
        //获取支付方式列表
        'payment.get.list' => ['uses' => 'ectools_api_getPayments@getList', 'version' => ['v1']],
        //获取支付方式的配置信息
        'payment.get.conf' => ['uses' => 'ectools_api_getPaymentConf@getConf', 'version' =>['v1']],
        //获取支付单信息
        'payment.bill.get' => ['uses' => 'ectools_api_getPaymentBill@getInfo', 'version' => ['v1']],
        //支付单创建
        'payment.bill.create' => ['uses' => 'ectools_api_payment_createBill@create', 'version' => ['v1']],
        //订单支付请求支付网关
        'payment.trade.pay' => ['uses' => 'ectools_api_payment_pay@doPay', 'version' => ['v1']],
        //订单退款支付请求支付网关
        'payment.trade.refundpay' => ['uses' => 'ectools_api_payment_refundPay@refundPay', 'version' => ['v1']],
        //创建并完成支付单
        'payment.trade.payandfinish' => ['uses' => 'ectools_api_payment_payAndFinish@payAndFinish', 'version' => ['v1']],
        //退款单创建
        'refund.create' => ['uses' => 'ectools_api_refund_create@create', 'version' => ['v1']],
        //更新退款单
        'refund.update' => ['uses' => 'ectools_api_refund_update@update', 'version' => ['v1']],
         //根据支付单检查支付单的状态
        'payment.checkpayment.statu' => ['uses' => 'ectools_api_payment_checkPayment@checkPayment', 'version' => ['v1']],
        // 获取当前平台指定货币的符号
        'currency.get.symbol' =>['uses' => 'ectools_api_getCurrency@getCur', 'version' => ['v1']],
        // 获取订单支付信息列表
        'trade.payment.list' =>['uses' => 'ectools_api_getTradePaymentList@getList', 'version' => ['v1']],
        // 获取子订单退款信息列表
        'order.refund.list' =>['uses' => 'ectools_api_getOrderRefundList@getList', 'version' => ['v1']],

        /*
         *=======================================
         *统计相关
         *=======================================
         */
        //商家统计数据
        'sysstat.data.get' => ['uses' => 'sysstat_api_getStatData@getStatData', 'version' => ['v1']],
        //获取商家统计时间
        'sysstat.datatime.get' => ['uses' => 'sysstat_api_getPageTime@getPageTime', 'version' => ['v1']],
        //获取指定时间商家的统计数据
        'stat.trade.data.count.get' => ['uses' => 'sysstat_api_getTradeDataCount@getTradeInfo', 'version' => ['v1']],

        //创建网站流量统计数据
        'sysstat.traffic.data.create' => ['uses' => 'sysstat_api_trafficStat_create@create', 'version' => ['v1']],
        //获取网站流量统计数据
        'sysstat.traffic.data.get' => ['uses' => 'sysstat_api_trafficStat_get@getData', 'version' => ['v1']],

        /*
         *=======================================
         *物流及运费和运费模板
         *=======================================
         */

        //获取物流公司列表
        'logistics.dlycorp.get.list' => ['uses' => 'syslogistics_api_dlycorp_getlist@getList', 'version' => ['v1']],
        'logistics.dlycorp.get' => ['uses' => 'syslogistics_api_dlycorp_get@getList', 'version' => ['v1']],
        //获取运费模板（根据店铺id）
        'logistics.dlytmpl.get.list' => ['uses' => 'syslogistics_api_dlytmpl_getlist@getList', 'version' => ['v1']],
        'logistics.dlytmpl.get' => ['uses' => 'syslogistics_api_dlytmpl_get@getList', 'version' => ['v1']],
        'logistics.dlytmpl.add' => ['uses' => 'syslogistics_api_dlytmpl_add@create', 'version' => ['v1']],
        'logistics.dlytmpl.update' => ['uses' => 'syslogistics_api_dlytmpl_update@update', 'version' => ['v1']],
        'logistics.dlytmpl.delete' => ['uses' => 'syslogistics_api_dlytmpl_delete@delete', 'version' => ['v1']],
        //计算运费（根据运费模板）
        'logistics.fare.count' => ['uses' => 'syslogistics_api_fare@countFare', 'version' => ['v1']],
        //获取地区数据
        'logistics.area' =>['uses' => 'syslogistics_api_getAreaList@getList', 'version' => ['v1']],

        //发货单创建
        'delivery.create' => ['uses' => 'syslogistics_api_delivery_create@create', 'version' => ['v1']],
        //发货单更新
        'delivery.update' => ['uses' => 'syslogistics_api_delivery_update@update', 'version' => ['v1']],
        //配送信息更新
        'delivery.updateLogistic' => ['uses' => 'syslogistics_api_delivery_updateLogistic@update', 'version' => ['v1']],
        //获取发货信息
        'delivery.get' => ['uses' => 'syslogistics_api_delivery_getInfo@getInfo', 'version' => ['v1']],

        'delivery.list' => ['uses' => 'syslogistics_api_delivery_list@get', 'version' => ['v1']],

        //获取快递鸟物流跟踪
        'logistics.tracking.get.hqepay' => ['uses' => 'syslogistics_api_getHqepayTracking@getTracking', 'version' => ['v1']],

        //获取自提点列表
        'logistics.ziti.add' => ['uses' => 'syslogistics_api_ziti_addNew@create','version' => ['v1']],
        'logistics.ziti.list' => ['uses' => 'syslogistics_api_ziti_list@get','version' => ['v1']],
        'logistics.ziti.get' => ['uses' => 'syslogistics_api_ziti_get@get','version' => ['v1']],
        'logistics.ziti.update' => ['uses' => 'syslogistics_api_ziti_update@update','version' => ['v1']],
        'logistics.ziti.list.get' => ['uses' => 'syslogistics_api_ziti_listById@get','version' => ['v1']],

        /*
         *=======================================
         *文章相关
         *=======================================
         */
        'syscontent.node.get.list' => ['uses' => 'syscontent_api_getNodeList@getNodeList', 'version' => ['v1']],
        'syscontent.content.get.list' => ['uses' => 'syscontent_api_getContentList@getContentList', 'version' => ['v1']],
        'syscontent.content.get.info' => ['uses' => 'syscontent_api_getContentInfo@getContentInfo', 'version' => ['v1']],
        'syscontent.content.map' => ['uses' => 'syscontent_api_getContentMaps@get', 'version' => ['v1']],
        // 商家文章
        'syscontent.shop.save.article.node' => ['uses' => 'syscontent_api_shop_saveNode@save', 'version' => ['v1']],
        'syscontent.shop.del.article.node' => ['uses' => 'syscontent_api_shop_delNode@del', 'version' => ['v1']],
        'syscontent.shop.list.article.node' => ['uses' => 'syscontent_api_shop_listNode@getList', 'version' => ['v1']],
        'syscontent.shop.get.article.node' => ['uses' => 'syscontent_api_shop_getNode@get', 'version' => ['v1']],
        'syscontent.shop.list.article' => ['uses' => 'syscontent_api_shop_list@getList', 'version' => ['v1']],
        'syscontent.shop.info.article' => ['uses' => 'syscontent_api_shop_info@get', 'version' => ['v1']],
        'syscontent.shop.save.article' => ['uses' => 'syscontent_api_shop_save@save', 'version' => ['v1']],
        'syscontent.shop.del.article' => ['uses' => 'syscontent_api_shop_del@del', 'version' => ['v1']],
        /*
         *=======================================
         * 图片相关
         *=======================================
         */
         //获取商家图片列表
         'image.shop.list' => ['uses' => 'image_api_shop_list@get', 'version'=>['v1']],
         //修改图片名称
         'image.shop.upImageName' => ['uses' => 'image_api_shop_upImageName@up', 'version'=>['v1']],
         //数据库中删除图片链接，但是不删除真实图片文件
         'image.delete.imageLink' => ['uses' => 'image_api_deleteImage@delete', 'version'=>['v1']],

         'image.shop.cat.add' => ['uses' => 'image_api_shop_cat_add@create', 'version'=>['v1']],
         'image.shop.cat.delete' => ['uses' => 'image_api_shop_cat_delete@delete', 'version'=>['v1']],
         'image.shop.cat.update' => ['uses' => 'image_api_shop_cat_update@edit', 'version'=>['v1']],
         'image.shop.cat.imagetype.list' => ['uses' => 'image_api_shop_cat_imagetypelist@get', 'version'=>['v1']],
         'image.shop.cat.list' => ['uses' => 'image_api_shop_cat_list@get', 'version'=>['v1']],
         'image.shop.move.cat' => ['uses' => 'image_api_shop_moveImageCat@move', 'version'=>['v1']],

        /*
         *=======================================
         *子帐号，角色相关
         *=======================================
         */
         'account.shop.roles.add' => ['uses' => 'sysshop_api_account_rolesAdd@save', 'version'=>['v1']],
         'account.shop.roles.update' => ['uses' => 'sysshop_api_account_rolesUpdate@update', 'version'=>['v1']],
         'account.shop.roles.list' => ['uses' => 'sysshop_api_account_rolesList@get', 'version'=>['v1']],
         'account.shop.roles.get' => ['uses' => 'sysshop_api_account_rolesInfo@get', 'version'=>['v1']],
         'account.shop.roles.delete' => ['uses' => 'sysshop_api_account_rolesDel@delete', 'version'=>['v1']],

         //对子账号进行操作
         'account.shop.user.add' => ['uses' => 'sysshop_api_account_sellerAdd@save', 'version'=>['v1']],
         'account.shop.user.update' => ['uses' => 'sysshop_api_account_sellerUpdate@update', 'version'=>['v1']],
         'account.shop.user.list' => ['uses' => 'sysshop_api_account_sellerList@get', 'version'=>['v1']],
         'account.shop.user.get' => ['uses' => 'sysshop_api_account_sellerInfo@get', 'version'=>['v1']],
         'account.shop.user.delete' => ['uses' => 'sysshop_api_account_sellerDel@delete', 'version'=>['v1']],

         //商家认证操作
         'auth.shop.updata' => ['uses' => 'sysshop_api_account_sellerAuthUpdate@update', 'version'=>['v1']],

         //oauth需要用到的接口
         'account.shop.oauth.login' => ['uses'=>'sysshop_api_oauth_sellerLogin@login', 'version'=>['v1']],

         //demo站点辅助工具
         'demo.shop.create' => ['uses'=>'sysshop_api_demo_createShop@create', 'version'=>['v1']],
         //更新店铺状态
         'demo.shop.status' => ['uses' => 'sysshop_api_demo_status@update', 'version' => ['v1']],

         //这里给开放接口用的
         //oauth登陆
         'open.oauth.login' => ['uses'=>'sysopen_api_oauth_login@login', 'version'=>['v1'], 'level'=>70],
         'open.shop.develop.info' => ['uses'=>'sysopen_api_open_shopInfo@get', 'version'=>['v1']],

         'open.shop.develop.conf' => ['uses'=>'sysopen_api_open_getShopConf@get', 'version'=>['v1']],
         'open.shop.develop.setConf' => ['uses'=>'sysopen_api_open_setShopConf@set', 'version'=>['v1']],

         'open.shop.develop.apply' => ['uses'=>'sysopen_api_open_applyForOpen@apply', 'version'=>['v1']],
         'open.shop.isopen' => ['uses'=>'sysopen_api_open_isOpen@handle', 'version'=>['v1']],
         'open.shop.apply.node' => ['uses'=>'sysopen_api_open_shopex_applyNode@handle', 'version'=>['v1']],
         'open.shop.node.get' => ['uses'=>'sysopen_api_open_shopex_getNode@handle', 'version'=>['v1']],
         'open.shop.apply.bind' => ['uses'=>'sysopen_api_open_shopex_apply@handle', 'version'=>['v1']],
         'open.shop.shopex.bind.list' => ['uses'=>'sysopen_api_open_shopex_list@handle', 'version'=>['v1']],
         'open.shop.show.bind' => ['uses'=>'sysopen_api_open_shopex_showbind@handle', 'version'=>['v1']],

        /*
         *=======================================
         * 即时通信工具
         *=======================================
         */
         //商家获取webcall的接口
         'im.shop.webcall.get' => ['uses'=>'sysim_api_webcall_get@get', 'version'=>['v1']],
         //商家申请webcall账号的接口
         'im.shop.webcall.apply' => ['uses'=>'sysim_api_webcall_signUp@sign', 'version'=>['v1']],
         //商家编辑的接口
         'im.shop.webcall.edit' => ['uses'=>'sysim_api_webcall_edit@save', 'version'=>['v1']],

         //店铺装修
         'sysdecorate.shopsign.add'         => ['uses'=>'sysdecorate_api_shopsign_add@save', 'version' => ['v1']],
         'sysdecorate.oneimg.add'           => ['uses'=>'sysdecorate_api_oneimg_add@save', 'version' => ['v1']],
         'sysdecorate.nav.add'              => ['uses'=>'sysdecorate_api_nav_add@save', 'version' => ['v1']],
         'sysdecorate.goods.add'      => ['uses'=>'sysdecorate_api_goods_add@save', 'version' => ['v1']],
         'sysdecorate.goods.data.get' => ['uses'=>'sysdecorate_api_goods_getViewData@get', 'version' => ['v1']],
         'sysdecorate.slider.add'           => ['uses'=>'sysdecorate_api_slider_add@save', 'version' => ['v1']],
         'sysdecorate.coupon.add'         => ['uses'=>'sysdecorate_api_coupon_add@save', 'version' => ['v1']],
         'sysdecorate.widgets.get'          => ['uses'=>'sysdecorate_api_get@get', 'version' => ['v1']],
         'sysdecorate.widgets.delete'       => ['uses'=>'sysdecorate_api_delete@delete', 'version' => ['v1']],
         'sysdecorate.widgets.clean'       => ['uses'=>'sysdecorate_api_clean@clean', 'version' => ['v1']],

        /*
         *=======================================
         * app模块配置
         *=======================================
         */
         //获取app端页面类型信息
         'sysapp.page.config' => ['uses'=>'sysapp_api_getConfig@get', 'version'=>['v1']],
         //用于获取app端页面模块配置信息
         'sysapp.theme.get' => ['uses'=>'sysapp_api_theme@modules', 'version'=>['v1']],

         //用于push设备注册
         'sysapp.push.register' => ['uses'=>'sysapp_api_push_register@reg', 'version'=>['v1']],

         //用于push设备用户登录时绑定用户与设备
         'sysapp.push.login' => ['uses'=>'sysapp_api_push_login@login', 'version'=>['v1']],

         /*
         *=======================================
         *  财务相关API
         *=======================================
         */
         //用于获取单店保证金详情
         'sysfinance.shop.guaranteeMoney.get' => ['uses'=>'sysfinance_api_guaranteeMoney_getDetail@get', 'version'=>['v1']],

         //获取保证金操作列表
         'sysfinance.guaranteeMoney.log.get' => ['uses'=>'sysfinance_api_guaranteeMoney_logList@getList', 'version'=>['v1']],

         //获取单条保证金记录详情
         'sysfinance.guaranteeMoney.logdetail.get' => ['uses'=>'sysfinance_api_guaranteeMoney_logdetail@get', 'version'=>['v1']],

    ),

    /*
    |--------------------------------------------------------------------------
    | 定义所有luckymall预设的app的api依赖关系
    |--------------------------------------------------------------------------
    |
    | 其实就是哪个app调用那个app的api,这里可以做prism上的权限调配
    | limit_count和limit_seconds是做流量限制的，以后流量限制会调用这里的，现在的话，只能到prism上调整
    |
     */
    'depends'=> array (
        'ectools' => array (
            'systrade' => array ( 'appName' => 'systrade', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'ectools' => array ( 'appName' => 'ectools', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysuser' => array ( 'appName' => 'sysuser', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'image' => array (
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'pam' => array (
            'sysuser' => array ( 'appName' => 'sysuser', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'systrade' => array ( 'appName' => 'systrade', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'sysaftersales' => array (
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'systrade' => array ( 'appName' => 'systrade', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'ectools' => array ( 'appName' => 'ectools', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysaftersales' => array ( 'appName' => 'sysaftersales', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysuser' => array ( 'appName' => 'sysuser', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'syscategory' => array (
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syscategory' => array ( 'appName' => 'syscategory', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysitem' => array ( 'appName' => 'sysitem', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'syscontent' => array (
            'syscontent' => array ( 'appName' => 'syscontent', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'sysdecorate' => array (
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysitem' => array ( 'appName' => 'sysitem', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'sysitem' => array (
            'syscategory' => array ( 'appName' => 'syscategory', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syspromotion' => array ( 'appName' => 'syspromotion', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysitem' => array ( 'appName' => 'sysitem', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'systrade' => array ( 'appName' => 'systrade', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysuser' => array ( 'appName' => 'sysuser', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'syslogistics' => array (
            'systrade' => array ( 'appName' => 'systrade', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syslogistics' => array ( 'appName' => 'syslogistics', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'sysopen' => array (
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'systrade' => array ( 'appName' => 'systrade', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'syspromotion' => array (
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syspromotion' => array ( 'appName' => 'syspromotion', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysitem' => array ( 'appName' => 'sysitem', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syscategory' => array ( 'appName' => 'syscategory', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'systrade' => array ( 'appName' => 'systrade', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysuser' => array ( 'appName' => 'sysuser', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'sysrate' => array (
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysitem' => array ( 'appName' => 'sysitem', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'systrade' => array ( 'appName' => 'systrade', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysrate' => array ( 'appName' => 'sysrate', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'sysshop' => array (
            'syscategory' => array ( 'appName' => 'syscategory', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'sysstat' => array (
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'systrade' => array (
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syslogistics' => array ( 'appName' => 'syslogistics', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysuser' => array ( 'appName' => 'sysuser', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'systrade' => array ( 'appName' => 'systrade', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysitem' => array ( 'appName' => 'sysitem', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syspromotion' => array ( 'appName' => 'syspromotion', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'ectools' => array ( 'appName' => 'ectools', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'sysuser' => array (
            'syspromotion' => array ( 'appName' => 'syspromotion', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'systrade' => array ( 'appName' => 'systrade', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'topc' => array (
            'syspromotion' => array ( 'appName' => 'syspromotion', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysitem' => array ( 'appName' => 'sysitem', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysrate' => array ( 'appName' => 'sysrate', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syscategory' => array ( 'appName' => 'syscategory', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'systrade' => array ( 'appName' => 'systrade', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syslogistics' => array ( 'appName' => 'syslogistics', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysuser' => array ( 'appName' => 'sysuser', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syscontent' => array ( 'appName' => 'syscontent', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysaftersales' => array ( 'appName' => 'sysaftersales', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'ectools' => array ( 'appName' => 'ectools', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'topm' => array (
            'syspromotion' => array ( 'appName' => 'syspromotion', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysitem' => array ( 'appName' => 'sysitem', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysrate' => array ( 'appName' => 'sysrate', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syscategory' => array ( 'appName' => 'syscategory', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'systrade' => array ( 'appName' => 'systrade', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysuser' => array ( 'appName' => 'sysuser', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syslogistics' => array ( 'appName' => 'syslogistics', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syscontent' => array ( 'appName' => 'syscontent', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysaftersales' => array ( 'appName' => 'sysaftersales', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'ectools' => array ( 'appName' => 'ectools', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'topshop' => array (
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysaftersales' => array ( 'appName' => 'sysaftersales', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysuser' => array ( 'appName' => 'sysuser', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syslogistics' => array ( 'appName' => 'syslogistics', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysrate' => array ( 'appName' => 'sysrate', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syscategory' => array ( 'appName' => 'syscategory', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'systrade' => array ( 'appName' => 'systrade', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysitem' => array ( 'appName' => 'sysitem', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysstat' => array ( 'appName' => 'sysstat', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysopen' => array ( 'appName' => 'sysopen', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'syspromotion' => array ( 'appName' => 'syspromotion', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'image' => array ( 'appName' => 'image', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'toputil' => array (
            'syscategory' => array ( 'appName' => 'syscategory', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => '*', 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
        'openstandard' => array (
            'sysshop' => array ( 'appName' => 'sysshop', 'path' => array(), 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysaftersales' => array (
                'appName' => 'sysaftersales',
                'path' => array(
                    'aftersales.refundapply.shop.check',
                    'aftersales.refundapply.shop.add',
                    'aftersales.refundapply.get',
                    'aftersales.get',
                    'aftersales.status.update',
                ), 'limit_count' => 1000, 'limit_seconds' => 60,),
            'sysitem' => array ( 'appName' => 'sysitem', 'path' => array('item.shop.store.update','item.search', 'item.list.get', 'item.get', 'item.sku.get', ), 'limit_count' => 1000, 'limit_seconds' => 60,),
            'systrade' => array ( 'appName' => 'systrade', 'path' => array('trade.shop.get','trade.cancel','trade.delivery'), 'limit_count' => 1000, 'limit_seconds' => 60,),
        ),
    ),
);
