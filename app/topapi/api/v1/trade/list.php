<?php
/**
 * topapi
 *
 * -- trade.list
 * -- 会员订单列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_trade_list implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '会员订单列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'status'    => ['type'=>'string', 'valid'=>'in:WAIT_BUYER_PAY,WAIT_SELLER_SEND_GOODS,WAIT_BUYER_CONFIRM_GOODS,WAIT_RATE',  'example'=>'', 'desc'=>'订单状态 WAIT_BUYER_PAY 等待付款 WAIT_SELLER_SEND_GOODS 待发货 WAIT_BUYER_CONFIRM_GOODS 等待确认收货 WAIT_RATE 待评价'],
            'page_no'   => ['type'=>'int',    'valid'=>'numeric',  'example'=>'', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int',    'valid'=>'numeric',  'example'=>'', 'desc'=>'每页数据条数,默认10条'],
            'fields'    => ['type'=>'string', 'valid'=>'',  'example'=>'', 'desc'=>'需返回的字段'],
        );

        return $return;
    }

    public $status = [
        'WAIT_BUYER_PAY' => '待付款',
        'WAIT_SELLER_SEND_GOODS' => '待发货',
        'WAIT_BUYER_CONFIRM_GOODS' => '待收货',
        'TRADE_FINISHED' => '已完成',
        'TRADE_CLOSED' => '已关闭',
        'TRADE_CLOSED_BY_SYSTEM' => '已关闭'
    ];

    public function handle($params)
    {
        $apiParams = array(
            'user_id' => $params['user_id'],
            'page_no' =>intval($params['page_no']) ? intval($params['page_no']) : 1,
            'page_size' =>intval($params['page_size']) ? intval($params['page_size']) : 10,
            'order_by' =>'created_time desc',
        );

        if( !empty($params['status']) )
        {
            $apiParams['status'] = $params['status'];
        }

        if( $params['status'] == 'WAIT_RATE' )
        {
            $apiParams['buyer_rate'] = 0;
            $apiParams['status'] = 'TRADE_FINISHED';
        }

        if( !$params['fields'] || $params['fields'] == '*' )
        {
            $apiParams['fields'] = 'tid,shop_id,user_id,status,cancel_status,payment,pay_type,created_time,order.title,order.num,order.pic_path,order.oid,order.aftersales_status,buyer_rate,order.complaints_status,order.item_id,order.status,order.gift_data,delivery';
        }
        else
        {
            $apiParams['fields'] = $params['fields'];
        }

        $tradelist = app::get('topapi')->rpcCall('trade.get.list',$apiParams);
        if( $tradelist['list'] )
        {
            $shopIds = array_column($tradelist['list'],'shop_id');
            $shopIds = array_unique($shopIds);
            $shopData = app::get('topapi')->rpcCall('shop.get.list', ['shop_id'=>implode(',',$shopIds),'fields'=>'shop_id,shop_name']);
            foreach( $shopData as $shopRow )
            {
                $shopname[$shopRow['shop_id']] = $shopRow['shopname'];
            }

            foreach( $tradelist['list'] as $row )
            {
                $totalItem = 0;
                $row['is_buyer_rate'] = false;
                foreach( $row['order'] as &$orderRow )
                {
                    $orderRow['gift_count'] = 0;
                    if(isset($orderRow['gift_data']) && $orderRow['gift_data'])
                    {
                        $orderRow['gift_data'] = $this->__preGiftData($orderRow['gift_data']);
                        $orderRow['gift_count'] += array_sum(array_column($orderRow['gift_data'],'gift_num'));
                    }

                    $orderRow['pic_path'] = base_storager::modifier($orderRow['pic_path'], 't');
                    $totalItem += ($orderRow['num']+$orderRow['gift_count']);
                }

                if ($row['buyer_rate'] == '0' && $row['status'] == 'TRADE_FINISHED')
                {
                    $row['is_buyer_rate'] = true;
                }

                $row['totalItem'] = $totalItem;
                if( $row['pay_type'] == 'offline' && in_array($row['status'],['WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS']))
                {
                    $row['status_desc'] = $this->status[$row['status']].'(货到付款)';
                }
                else
                {
                    $row['status_desc'] = $this->status[$row['status']];
                }

                $row['shopname'] = $shopname[$row['shop_id']];
                $list['list'][] = $row;
            }
            $list['pagers']['total'] = $tradelist['count'];

            $cur_symbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
            $list['cur_symbol'] = $cur_symbol;
        }

        return $list;
    }

    private function __preGiftData($data)
    {
        foreach($data as $key=>$row)
        {
            $return[] = [
                'gift_id' => $row['gift_id'],
                'item_id' => $row['item_id'],
                'gift_num' => $row['gift_num'],
                'image_default_id' => base_storager::modifier($row['image_default_id'], 't'),
            ];
        }
        return $return;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"tid":1609111253210004,"shop_id":3,"user_id":4,"status":"WAIT_BUYER_PAY","cancel_status":"NO_APPLY_CANCEL","payment":"111.000","pay_type":"online","created_time":1473569594,"buyer_rate":0,"order":[{"title":"罗技（Logitech）M275 无线鼠标 黑色","num":1,"pic_path":"http://images.bbc.shopex123.com/images/55/fc/3f/df86d11be400518108e5db03f889246edfc6f67c.jpg_t.jpg","oid":1609111253220004,"aftersales_status":null,"complaints_status":"NOT_COMPLAINTS","item_id":37,"status":"WAIT_BUYER_PAY","gift_data":[{"gift_id":1,"item_id":26,"gift_num":1,"image_default_id":"http://images.bbc.shopex123.com/images/74/32/fc/414d7236e416eb52ebd17b2e30f8d1f0fb85838c.jpg_t.jpg"},{"gift_id":1,"item_id":26,"gift_num":1,"image_default_id":"http://images.bbc.shopex123.com/images/74/32/fc/414d7236e416eb52ebd17b2e30f8d1f0fb85838c.jpg_t.jpg"},{"gift_id":1,"item_id":26,"gift_num":1,"image_default_id":"http://images.bbc.shopex123.com/images/74/32/fc/414d7236e416eb52ebd17b2e30f8d1f0fb85838c.jpg_t.jpg"}],"tid":1609111253210004}],"is_buyer_rate":false,"gift_count":3,"totalItem":4,"status_desc":"待付款","shopname":"onexbbc自营店（自营店铺）自营店"}],"pagers":{"total":47}}}';
    }
}
