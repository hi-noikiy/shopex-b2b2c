<?php
/**
 * topapi
 *
 * -- trade.order.get
 * -- 子订单详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_trade_orderGet implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '子订单详情';

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

    public function handle($params)
    {
        $params['fields'] = 'tid,oid,title,price,num,pic_path,gift_data,status,complaints_status,aftersales_status,item_id,sku_id,spec_nature_info';
        $order = app::get('topapi')->rpcCall('trade.order.get',$params);
        if( $order )
        {

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
            $list['cur_symbol'] = $cur_symbol;
        }

        return $order;
    }

    public function returnJson()
    {
        return '';
    }
}
