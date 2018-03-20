<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2014-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 实现商家报表返回数据
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package sysstat.lib.analysis
 */
class sysstat_shop_taskdata
{
    public function exec($params)
    {
        // 得到所有的商家id和热门商品
        $memberInfo = kernel::single('sysstat_shop_task')->getMemeberInfo($params);
        $memberInfo['createtime'] = $params['time_insert'];
        $statMemberMdl = app::get('sysstat')->model('statmember');
        $statmId = $statMemberMdl->getRow('statm_id',array('createtime'=>$params['time_insert']));
        //echo '<pre>';print_r($params);
        if(empty($statmId))
        {
            $statMemberMdl->save($memberInfo);
        }
        else
        {
            $statMemberMdl->update($memberInfo,array('statm_id'=>$statmId['statm_id']));
        }
        // 得到所有的商家id和热门商品
        $hotGoods = kernel::single('sysstat_shop_task')->hotGoods($params);
        // 得到所有的商家id和退货商品
        $refundGoods = kernel::single('sysstat_shop_task')->refundGoods($params);
        // 得到所有的商家id和换货商品
        $changingGoods = kernel::single('sysstat_shop_task')->changingGoods($params);

        // 得到所有的商家id和新增订单数,新增订单额
        $newTrade = kernel::single('sysstat_shop_task')->newTrade($params);
        // 得到所有的商家id和待付款订单数,待付款订单额
        $readyTrade = kernel::single('sysstat_shop_task')->readyTrade($params);
        // 得到所有的商家id和以付款订单数,以付款订单额
        $alreadyTrade = kernel::single('sysstat_shop_task')->alreadyTrade($params);
        // 得到所有的商家id和待发货订单数量,待发货订单额
        $readySendTrade = kernel::single('sysstat_shop_task')->readySendTrade($params);
        // 得到所有的商家id和待收货订单数量,待收货订单额
        $alreadySendTrade = kernel::single('sysstat_shop_task')->alreadySendTrade($params);

        // 得到所有的商家id和已完成订单数量,已完成订单额
        $completeTrade = kernel::single('sysstat_shop_task')->completeTrade($params);

        // 得到所有的商家id和已取消的订单数量,已取消订单额
        $cancleTrade = kernel::single('sysstat_shop_task')->cancleTrade($params);
        // 得到所有的商家id和退货退款订单的订单数量,退货退款订单的订单额
        $refundTrade = kernel::single('sysstat_shop_task')->refundTrade($params);
        // 得到所有的商家id和已换货订单的订单数量
        $exchangingTrade = kernel::single('sysstat_shop_task')->exchangingTrade($params);
        // 得到所有的商家id和拒收订单数量，拒收订单额
        $rejectTrade = kernel::single('sysstat_shop_task')->rejectTrade($params);

        $data = $this->getData($newTrade,$readyTrade,$readySendTrade,$alreadySendTrade,$completeTrade,$cancleTrade,$alreadyTrade,$refundTrade,$exchangingTrade,$rejectTrade,$params);
        $goodsData = $this->getGoodsData($hotGoods,$refundGoods,$changingGoods,$params);
        $tradeStaticsMdl = app::get('sysstat')->model('trade_statics');
        $itemStaticsMdl = app::get('sysstat')->model('item_statics');

        foreach ($data as $value)
        {
            $filter = array('shop_id'=>$value['shop_id']);
            $rows = app::get('sysstat')->database()->executeQuery('SELECT stat_id FROM sysstat_trade_statics where shop_id= ? and createtime= ?', [$value['shop_id'], $value['createtime']])->fetch();

            if($rows['stat_id'])
            {
                $value['stat_id'] = $rows['stat_id'];
            }
            $tradeStaticsMdl->save($value);
        }


        foreach ($goodsData as $key =>$value)
        {
            $filter = array('shop_id'=>$value['shop_id']);
            $rows = app::get('sysstat')->database()->executeQuery('SELECT item_stat_id FROM sysstat_item_statics where shop_id= ? and createtime= ? and item_id= ?', [$value['shop_id'], $value['createtime'],$value['item_id']])->fetch();

            if($rows['item_stat_id'])
            {
                $value['item_stat_id'] = $rows['item_stat_id'];
            }
            $itemStaticsMdl->save($value);
        }
    }

    /**
     * 获取商品统计数据
     * @param null
     * @return null
     */
    public function getGoodsData($hotGoods,$refundGoods,$changingGoods,$params)
    {
        foreach ($hotGoods as $key => $value) {
            $goodsData[$value['item_id']]['item_id'] = $value['item_id'];
            $goodsData[$value['item_id']]['shop_id'] = $value['shop_id'];
            $goodsData[$value['item_id']]['title'] = $value['title'];
            $goodsData[$value['item_id']]['pic_path'] = $value['pic_path'];
            $goodsData[$value['item_id']]['amountnum'] = $value['itemnum'];
            $goodsData[$value['item_id']]['amountprice'] = $value['amountprice'];
            $goodsData[$value['item_id']]['createtime'] = $params['time_insert'];
        }
        foreach ($refundGoods as $k => $v) {
            $goodsData[$v['item_id']]['item_id'] = $v['item_id'];
            $goodsData[$v['item_id']]['shop_id'] = $v['shop_id'];
            $goodsData[$v['item_id']]['title'] = $v['title'];
            $goodsData[$v['item_id']]['pic_path'] = $v['pic_path'];
            $goodsData[$v['item_id']]['createtime'] = $params['time_insert'];
            $goodsData[$v['item_id']]['refundnum'] = $v['refundnum'];
        }
        foreach ($changingGoods as $val) {
            $goodsData[$val['item_id']]['item_id'] = $val['item_id'];
            $goodsData[$val['item_id']]['shop_id'] = $val['shop_id'];
            $goodsData[$val['item_id']]['title'] = $val['title'];
            $goodsData[$val['item_id']]['pic_path'] = $val['pic_path'];
            $goodsData[$val['item_id']]['createtime'] = $params['time_insert'];
            $goodsData[$val['item_id']]['changingnum'] = $val['changingnum'];
        }
        
        return $goodsData;
    }
    /**
     * 获取统计数据
     * @param null
     * @return null
     */
    public function getData($newTrade,$readyTrade,$readySendTrade,$alreadySendTrade,$completeTrade,$cancleTrade,$alreadyTrade,$refundTrade,$exchangingTrade,$rejectTrade,$params)
    {
        $tradeData = array_merge($newTrade,$readyTrade,$readySendTrade,$alreadySendTrade,$completeTrade,$cancleTrade,$alreadyTrade,$refundTrade,$exchangingTrade,$rejectTrade);
        foreach ($tradeData as $key => $value)
        {
            $arr[$value['shop_id']][] = $value;
        }
        foreach ($arr as $key => $value)
        {
            foreach ($value as $ke => $val)
            {
                foreach($val as $k => $v)
                {
                    $tradeArr[$key][$k] = $v;
                }
            }
        }
        foreach ($tradeArr as $key => $value)
        {
            $data[$key]['shop_id'] = $value['shop_id'];
            $data[$key]['new_trade'] = $value['new_trade']?$value['new_trade']:0;
            $data[$key]['new_fee'] = $value['new_fee']?$value['new_fee']:0;
            $data[$key]['ready_trade'] = $value['ready_trade']?$value['ready_trade']:0;
            $data[$key]['ready_fee'] = $value['ready_fee']?$value['ready_fee']:0;

            $data[$key]['ready_send_trade'] = $value['ready_send_trade']?$value['ready_send_trade']:0;
            $data[$key]['ready_send_fee'] = $value['ready_send_fee']?$value['ready_send_fee']:0;

            $data[$key]['already_send_trade'] = $value['already_send_trade']?$value['already_send_trade']:0;
            $data[$key]['already_send_fee'] = $value['already_send_fee']?$value['already_send_fee']:0;
            $data[$key]['cancle_trade'] = $value['cancle_trade']?$value['cancle_trade']:0;
            $data[$key]['cancle_fee'] = $value['cancle_fee']?$value['cancle_fee']:0;

            $data[$key]['complete_trade'] = $value['complete_trade']?$value['complete_trade']:0;
            $data[$key]['complete_fee'] = $value['complete_fee']?$value['complete_fee']:0;

            $data[$key]['alreadytrade'] = $value['alreadytrade']?$value['alreadytrade']:0;
            $data[$key]['alreadyfee'] = $value['alreadyfee']?$value['alreadyfee']:0;

            $data[$key]['refund_trade'] = $value['refund_trade']?$value['refund_trade']:0;
            $data[$key]['refund_fee'] = $value['refund_fee']?$value['refund_fee']:0;

            $data[$key]['reject_trade'] = $value['reject_trade']?$value['reject_trade']:0;
            $data[$key]['reject_fee'] = $value['reject_fee']?$value['reject_fee']:0;

            $data[$key]['total_refund_fee'] = $value['refund_fee'] + $value['reject_fee'];

            $data[$key]['changing_trade'] = $value['changing_trade']?$value['changing_trade']:0;

            $data[$key]['createtime'] = $params['time_insert'];
        }
        return $data;
    }

}
