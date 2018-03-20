<?php

class topshopapi_oms_item_skuInfo {

    public function handle($data)
    {
        return [ 'sku' => [
                'sku_id'     => $data['sku_id'],
                'iid'        => $data['item_id'],
                'outer_id'   => $data['bn'],
                'created'    => $data['created_time'],
                'status'     => $data['status'],
                'bn'         => $data['bn'],
                'modified'   => $data['modified_time'],
                'price'      => $data['price'],
                'properties' => $data['properties'],
                'quantity'   => $data['store']
        ]];
    }
}
