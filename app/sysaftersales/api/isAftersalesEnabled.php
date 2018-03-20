<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取子订单是否可进行退换货
 */
class sysaftersales_api_isAftersalesEnabled {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取子订单是否可进行退换货';
    public $use_strict_filter = true;

    /**
     * 消费者提交退货物流信息参数
     */
    public function getParams()
    {
        /*
         * 参数说明：oid 必填
         */
        $return['params'] = array(
            'oid'   => ['type'=>'int',    'valid'=>'required|numeric',  'title'=>'子订单号',         'desc'=>'申请售后的子订单编号'],
        );

        return $return;
    }

    /**
     * 消费者提交退货物流信息
     */
    public function get($params){
        $params['fields'] = 'tid,oid,num,pic_path,status,cat_id,end_time,complaints_status,aftersales_status,item_id,end_time,sku_id,spec_nature_info';
        $order = app::get('sysaftersales')->rpcCall('trade.order.get', $params);
        $aftersalesSetting = app::get('topc')->rpcCall('aftersales.setting.get',['cat_id'=>$order['cat_id']]);
        if(!$aftersalesSetting['refund_active']) {
            $pagedata['refund_enabled'] = true;
        }elseif($aftersalesSetting['refund_days'] !=0) {
            $refund_endtime = strtotime("+".$aftersalesSetting['refund_days']." days", $order['end_time']);
            if($refund_endtime > strtotime(date('Y-m-d 00:00:00', time()))){
                $pagedata['refund_enabled'] = true;
            }
        }

        if(!$aftersalesSetting['changing_active']){
            $pagedata['changing_enabled'] = true;
        }elseif($aftersalesSetting['changing_days'] !=0) {
            $changing_endtime = strtotime("+".$aftersalesSetting['changing_days']." days", $order['end_time']);
            if($changing_endtime > strtotime(date('Y-m-d 00:00:00', time()))){
                $pagedata['changing_enabled'] = true;
            }
        }

        if($order['complaints_status'] == 'FINISHED' && $order['aftersales_status'] == "SELLER_REFUSE_BUYER"){
            $pagedata['refund_enabled'] = true;
            $pagedata['changing_enabled'] = true;
        }

        return $pagedata;
    }
       
}
