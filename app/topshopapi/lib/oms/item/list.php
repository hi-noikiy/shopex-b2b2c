<?php

class topshopapi_oms_item_list {

    public function handle($list)
    {
        if ( !$list ) return [];
        foreach( $list as $k=>$v )
        {
            $data[] = [
                'iid'             => $v['item_id'],
                'title'           => $v['title'],
                'outer_id'        => $v['bn'],
                'shopcat_id'      => $v['shop_cat_id'],
                'barcode'         => $v['barcode'],
                'costprice'       => $v['cost_price'],
                'price'           => $v['price'],
                'mktprice'        => $v['mkt_price'],
                'bn'              => $v['bn'],
                'brand_id'        => $v['brand_id'],
                'num'             => $v['store'],
                'status'          => $v['approve_status'],
                'modified'        => $v['modified_time'],
                'description'     => $v['sub_title'],
                'default_img_url' => base_storager::modifier($v['image_default_id']),
                'item_imgs'       => $this->__getItemImgs($v['list_image']),
                'delist_time'     => $v['item_status']['delist_time'],
                'skus'            => $this->__getSkus($v['sku']),
            ];
        }

        $return['items']['item'] = $data;
        return $return;
    }

    private function __getSkus( $sku )
    {
        if  (!$sku) return [];
        foreach( $sku as $k=>$v )
        {
            $data['sku'][] = [
                'sku_id' => $v['sku_id'],
                'iid' => $v['item_id'],
                'outer_id' => $v['bn'],
                'created' => $v['created_time'],
                'status' => $v['status'],
                'bn' => $v['bn'],
                'modified' => $v['modified_time'],
                'price' => $v['price'],
                'properties' => $v['properties'],
                'quantity' => $v['store']
            ];
        }

        return $data;
    }


    private function __getItemImgs( $imglist )
    {
        if (!$imglist) return [ 'item_img'=>[]];
        $isD = false;
        foreach( $imglist as $k=>$v )
        {
            $isD = ($k == 1) ? true : false;
            $list['item_img'][] = [
                'image_id' => $k,
                'big_url' => base_storager::modifier($v,'l'),
                'thisuasm_url' => base_storager::modifier($v,'m'),
                'small_url' =>  base_storager::modifier($v,'s'),
                'is_default' => $isD,
            ];
        }
        return $list;
    }
}

