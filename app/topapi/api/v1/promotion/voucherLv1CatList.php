<?php
class topapi_api_v1_promotion_voucherLv1CatList implements topapi_interface_api{

    public $apiDescription = '获取购物券绑定一级类目';
    public $use_strict_filter = true;

    public function setParams()
    {
        return [
            'voucher_id'   => ['type'=>'int','valid'=>'required|min:1', 'example'=>'active', 'desc'=>'购物券id', 'msg'=>''],
        ];
        return $return;
    }

    public function handle($params)
    {
        //获取购物券信息
        $pagedata = [];
        $voucherParams['voucher_id'] = $params['voucher_id'];
        $voucherParams['fields'] = 'voucher_id,voucher_name,promotion_tag,used_platform,limit_cat,limit_money,deduct_money,canuse_start_time,canuse_end_time,valid_status';
        $voucher = app::get('topapi')->rpcCall('promotion.voucher.get',$voucherParams);

        if( $voucher['limit_cat'] )
        {
            $catLv1Data = app::get('topapi')->rpcCall('category.cat.get.info',['cat_id'=>$voucher['limit_cat'],'level'=>1,'fields'=>'cat_id,cat_name,child_count']);
            foreach($catLv1Data as $value)
            {
                $cat['cat_id'] = $value['cat_id'];
                $cat['cat_name'] = $value['cat_name'];
                $newCat[] = $cat;
            }
            $pagedata['cat'] = $newCat;
            $pagedata['activeFilter']['lv1_cat_id'] = $voucher['limit_cat'][0];
        }
        return $pagedata;

    }

    private function __getApiFilter(&$params,$voucher)
    {
        $limitCat = $voucher['limit_cat'];
        $registeShop = $voucher['registerShop'];

        if(!$limitCat) return '';
        $filter['limit_cat'] = $limitCat;
        if(!$params['lv1_cat_id'])
        {
            $catId = $limitCat[0];
        }
        else
        {
            $catId = $params['lv1_cat_id'];
        }

        if(!$params['shoo_id'])
        {
            foreach($registerShop as $row)
            {
                if(in_array($catId,explode(',',$row['cat_id'])))
                {
                    $params['shop_id'][] = $row['shop_id'];
                }
            }
        }

        if($params['shop_id'])
        {
            $filter['shop_id'] = $params['shop_id'] = implode(',',$params['shop_id']);
        }
        else
        {
            $params['shop_id'] = '-1';
        }

        if(!$params['cat_id'] && $catId)
        {
            $catList = app::get('topapi')->rpcCall('category.cat.get',['cat_id'=>$catId,'fields'=>'cat_id,cat_name']);
            $catIds = [];
            foreach($catList as $lv2Row)
            {
                foreach($lv2Row['lv2'] as $value)
                {
                    $catIds = array_merge($catIds,array_column($value['lv3'],'cat_id'));
                }
            }
            $filter['cat_id'] = $params['cat_id'] = implode(',',$catIds);
        }
        else
        {
            $filter['cat_id'] = $params['cat_id'];
        }

        return $filter;
    }

}
