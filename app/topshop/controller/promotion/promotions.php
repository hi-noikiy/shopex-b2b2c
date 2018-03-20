<?php
class topshop_ctl_promotion_promotions extends topshop_controller {

    /**
     *获取当前店铺所有可用活动列表
     *
     **/
    public function ajaxPromotionList()
    {
        // 店铺优惠券信息,
        $params = array(
            'shop_id' => $this->shopId,
            'used_platform' => intval(input::get('used_platform','0')),
        );

        $pagedata = app::get('topc')->rpcCall('promotion.promotion.list.get', $params);
        $pagedata['total'] = count($promotionList['list']);

        return view::make('topshop/promotion/ajaxPromotionList.html', $pagedata);
    }
}