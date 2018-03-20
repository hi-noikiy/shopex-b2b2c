<?php

class topshopapi_oms_item_search {

    public function handle( $list )
    {
        if ( !$list ) return [];

        foreach( $list['list'] as $k=>$v )
        {
            $data[] = [
                'iid'             => $v['item_id'],
                'outer_id'        => $v['bn'],
                'bn'              => $v['bn'],
                'num'             => $v['store'],
                'title'           => $v['title'],
                'shopcat_id'      => $v['shop_cat_id'],
                'brand_id'        => $v['brand_id'],
                'default_img_url' => base_storager::modifier($v['image_default_id']),
                'barcode'         => $v['barcode'],
                'costprice'       => $v['cost_price'],
                'list_time'       => $v['list_time'],
                'delist_time'     => $v['delist_time'],
                'status'          => $v['approve_status'],
                'price'           => $v['price'],
                'mktprice'        => $v['mkt_price'],
                'modified'        => $v['modified_time'],
            ];
        }

        $return['totalResults']  = $list['total_found'];
        $return['items']['item'] = $data;

        return $return;
    }
}
