<?php

class sysaftersales_shopex_aftersalesInfo {

    public $apiMethod = 'store.trade.aftersale.add';

    //返回shopex体系创建结构
    public function handle($params)
    {
        $apiParams['aftersales_bn'] = $params['aftersales_bn'];
        $apiParams['shop_id'] = $params['shop_id'];
        $apiParams['fields']  = '*,trade.tid,sku.bn,attachment';
        $tmpData = app::get('systrade')->rpcCall('aftersales.get', $apiParams);
        return $this->__getAftersales($tmpData);
    }

    private function __getAftersales($data)
    {
        $return['aftersale_id']   = strval($data['aftersales_bn']);
        $return['tid']            = strval($data['tid']);
        $return['title']          = $data['reason'];
        $return['content']        = $data['description'];
        $return['created']        = $data['created_time'];
        $return['memo']           = $data['memo'];
        $return['status']         = $this->__getStatus($data['progress']);
        $return['user_id']        = $data['user_id'];
        $return['buyer_name']     = '';
        $return['aftersale_items']= json_encode([$this->__gen_item_list($data)]);
        $return['attachment']     = urlencode($data['attachment']);
        $return['modified']       = $data['modified_time'];
        $return['return_type']    = $data['aftersales_type'];
        $return['logistics_info'] = "";
        $return['messager']       = "";

        return $return;
    }

    private function __gen_item_list($data)
    {
        $return = [
            'number'=> $data['num'],
            'sku_bn' => $data['sku']['bn'],
            'sku_name' => $data['title'],
        ];

        if( $data['gift_data'] )
        {
            foreach( $data['gift_data'] as $key=>$val)
            {
                $return['gift'][$key] = [
                    'num' => $val['gift_num'],
                    'bn' => $val['bn'],
                ];
            }
        }
        return $return;
    }

    private function __getStatus( $progress )
    {
        $progress = intval($progress);
        $switch = [
            0 => 1,
            1 => 3,
            2 => 3,
            3 => 5,
            4 => 4,
            5 => 7,
            6 => 9,
            7 => 4,
        ];

	    return $switch[$progress];
    }
}

