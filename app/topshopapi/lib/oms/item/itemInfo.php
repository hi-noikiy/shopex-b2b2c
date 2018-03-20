<?php

class topshopapi_oms_item_itemInfo {

    public function handle($info)
    {
        if( !$info ) return [];
        $data = ['item' => [
            'iid'             => $info['item_id'],
            'title'           => $info['title'],
            'outer_id'        => $info['bn'],
            'shopcat_id'      => $info['shop_cat_id'],
            'barcode'         => $info['barcode'],
            'mktprice'        => $info['mkt_price'],
            'costprice'       => $info['cost_price'],
            'bn'              => $info['bn'],
            'brand_id'        => $info['brand_id'],
            'num'             => $info['store'],
            'status'          => $info['approve_status'],
            'price'           => $info['price'],
            'modified'        => $info['modified_time'],
            'description'     => $info['sub_title'],
            'default_img_url' => $info['image_default_id'],
            'item_imgs'       => $this->__getItemImgs($info['images']),
            'delist_time'     => $info['delist_time'],
            'skus'            => $this->__getSkus($info['sku']),
        ]];

        return $data;
    }

    private function __getSkus( $skus )
    {
        if( !$skus ) return null;
        $result['sku'] = [];
        foreach( $skus as $k=>$v )
        {
            $result['sku'][] = [
                'sku_id'     => $v['sku_id'],
                'iid'        => $v['item_id'],
                'outer_id'   => $v['bn'],
                'created'    => $v['created_time'],
                'status'     => $v['status'],
                'bn'         => $v['bn'],
                'modified'   => $v['modified_time'],
                'price'      => $v['price'],
                'properties' => $v['properties'],
                'quantity'   => $v['store']
            ];
        }

        return $result;
    }

    private function __getItemImgs( $imglist )
    {
        if (!$imglist) return [ 'item_img'=>[]];
        $isD = false;
        foreach( $imglist as $k=>$v )
        {
            $isD = ($k == 1) ? true : false;
            $list['item_img'][] = [
                'image_id'     => $k,
                'big_url'      => base_storager::modifier($v,'l'),
                'thisuasm_url' => base_storager::modifier($v,'m'),
                'small_url'    => base_storager::modifier($v,'s'),
                'is_default'   => $isD,
            ];
        }
        return $list;
    }
}
