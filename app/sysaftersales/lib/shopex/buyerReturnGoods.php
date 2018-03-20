<?php

class sysaftersales_shopex_buyerReturnGoods {

    public $apiMethod = 'store.trade.aftersale.logistics.update';

    //返回shopex体系创建结构
    public function handle($params)
    {
        $data['aftersale_id'] = $params['aftersales_bn'];
        $data['tid'] = $params['tid'];
        $data['logistics_info'] = json_encode(['logistics_company'=>$params['corp_code'], 'logistics_no'=>$params['logi_no']]);
        return $data;
    }
}

