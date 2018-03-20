<?php
/**
 * topapi
 *
 * -- member.aftersales.applyInfo.get
 * -- 售后申请数据获取
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_aftersales_getApplyInfo implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '售后申请数据获取';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'oid'   => ['type'=>'string', 'valid'=>'numeric', 'example'=>'', 'desc'=>'子订单ID'],
        );

        return $return;
    }

    public $aftersalesReason = [
        '实物不符','质量原因','现在不想购买','商品价格较贵',
        '价格波动','商品缺货','重复下单','订单商品选择有误',
        '支付方式选择有误','收货信息填写有误','支付方式选择有误',
        '发票信息填写有误','其他原因',
    ];

    public function handle($params)
    {
        $params['fields'] = 'tid,oid,title,price,num,pic_path,gift_data,status,cat_id,end_time,complaints_status,aftersales_status,item_id,sku_id,spec_nature_info';
        $order = app::get('topapi')->rpcCall('trade.order.get',$params);
        $return = null;
        if( $order )
        {
            if($order['cat_id'] && $order['status'] =='TRADE_FINISHED' && $order['end_time'])
            {
                $aftersalesEnabled = app::get('topapi')->rpcCall('aftersales.isEnabled',$params);
                $order['refund_enabled'] = $aftersalesEnabled['refund_enabled'];
                $order['changing_enabled'] = $aftersalesEnabled['changing_enabled'];
            }
            $order['pic_path'] = base_storager::modifier($order['pic_path'], 't');

            $giftData = $order['gift_data'];
            unset($order['gift_data']);
            if( $giftData )
            {
                foreach($giftData as $key=>$row)
                {
                    $order['gift_data'][] = [
                        'gift_id' => $row['gift_id'],
                        'title' => $row['title'],
                        'spec_info' => $row['spec_info'],
                        'item_id' => $row['item_id'],
                        'gift_num' => $row['gift_num'],
                        'image_default_id' => base_storager::modifier($row['image_default_id'], 't'),
                    ];
                }
            }

            $cur_symbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
            $return['orderInfo'] = $order;
            $return['cur_symbol'] = $cur_symbol;

            $logisticsParams['fields'] = "corp_code,corp_name";
            $logisticsParams['page_no'] = 1;
            $logisticsParams['page_size'] = 100;
            $corpData = app::get('topapi')->rpcCall('logistics.dlycorp.get.list',$logisticsParams);
            $return['logistics'] = $corpData['data'];

            $return['reason'] = $this->aftersalesReason;
        }

        return $return;
    }

    public function returnJson()
    {
        return '';
    }
}
