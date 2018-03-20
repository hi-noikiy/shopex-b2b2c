<?php

class systrade_finder_canceltrade {
    public $detail_basic = '详情';
    public function detail_basic($id)
    {
        $objMdlCancelTrade = app::get('systrade')->model('trade_cancel');
        $pagedata = $objMdlCancelTrade->getRow('*',array('cancel_id'=>$id));

        $apiData['tid'] = $pagedata['tid'];
        $apiData['fields'] ='tid,payment,discount_fee,points_fee,post_fee,orders.item_id,orders.bn,orders.title,orders.price,orders.num,orders.total_fee,orders.gift_data';
        $tradeInfo = app::get('systrade')->rpcCall('trade.get',$apiData);

        $pagedata['goodsItems'] = $tradeInfo['orders'];
        $pagedata['payment'] = $tradeInfo['payment'];

        return view::make('systrade/admin/cancel_trade.html', $pagedata)->render();
    }
}

