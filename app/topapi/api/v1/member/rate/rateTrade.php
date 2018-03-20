<?php
/**
 * topapi
 *
 * -- member.ratetrade.get
 * -- 会员评价订单信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_rate_rateTrade implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '会员评价订单信息';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'tid'   => ['type'=>'string', 'valid'=>'numeric','desc'=>'订单ID'],
        );

        return $return;
    }

    public function handle($params)
    {
        $params['fields'] = "tid,orders.buyer_rate,orders.oid,orders.pic_path,orders.title,shop_id,status,buyer_rate,orders.item_id";
        $trade = app::get('topapi')->rpcCall('trade.get',$params);
        if( $trade &&  $trade['buyer_rate'] == '0' && $trade['status'] == 'TRADE_FINISHED' )
        {
            foreach( $trade['orders'] as &$row )
            {
                $row['pic_path']=  base_storager::modifier($row['pic_path'],'t');
            }

            $shopData = app::get('topapi')->rpcCall('shop.get', ['shop_id'=> $trade['shop_id'],'fields'=>'shop_id,shop_name,shop_logo,shop_type']);
            $trade['shopname'] = $shopData['shopname'];
            $trade['shoplogo'] = base_storager::modifier($shopData['shop_logo'],'t');
            $trade['shop_type'] = $shopData['shop_type'];
        }
        else
        {
            $trade = null;
        }

        return $trade;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"tid":1608151930150004,"shipping_type":"express","status":"WAIT_SELLER_SEND_GOODS","payment":"2400.000","points_fee":"0.000","cancel_status":"NO_APPLY_CANCEL","post_fee":"12.000","pay_type":"online","payed_fee":"2400.000","receiver_state":"天津市","receiver_city":"和平区","receiver_district":null,"receiver_address":"3213","trade_memo":"","receiver_name":"shopex","receiver_mobile":"13918087430","ziti_addr":null,"ziti_memo":null,"total_fee":"2388.000","discount_fee":"0.000","buyer_rate":0,"adjust_fee":"0.000","created_time":1471260638,"shop_id":3,"need_invoice":0,"invoice_name":"","invoice_type":"normal","invoice_main":"","invoice_vat_main":"","cancel_reason":null,"refund_fee":0,"orders":[{"price":"2388.000","aftersales_status":null,"num":1,"title":"华为 HUAWEI WATCH 动感系列 智能手表（黑色平尾","item_id":91,"pic_path":"http://images.bbc.shopex123.com/images/c9/cf/5f/896eceb25e1b37fca24552caeee0fb4a0df6510b.png","total_fee":"2388.000","adjust_fee":"0.000","spec_nature_info":null,"status":"WAIT_SELLER_SEND_GOODS","end_time":null,"buyer_rate":0,"oid":1608151930160004}],"shipping_type_name":"快递"}}';
    }
}
