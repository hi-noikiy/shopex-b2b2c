<?php
/**
 * topapi
 *
 * -- item.packageInfo
 * -- 商品详情页获取组合促销详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_item_packageInfo implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '商品详情页组合促销详情';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'package_id' => ['type'=>'int', 'valid'=>'required|integer|min:1','example'=>'1', 'desc'=>'组合促销ID。必须是正整数'],
        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $packageItemParams = [
            'page_no' => 1,
            'page_size' => 10,
            'fields' =>'item_id,shop_id,title,image_default_id,price,package_price,sku_ids',
            'package_id' => $params['package_id'],
        ];

        $packageItemList = app::get('topc')->rpcCall('promotion.packageitem.list', $packageItemParams);

        $itemsIds = array_column($packageItemList['list'],'item_id');
        if(!$itemsIds) return;

        unset($packageItemList['promotionInfo']['used_platform']);
        unset($packageItemList['promotionInfo']['use_bound']);
        unset($packageItemList['promotionInfo']['created_time']);
        unset($packageItemList['promotionInfo']['start_time']);
        unset($packageItemList['promotionInfo']['end_time']);
        unset($packageItemList['promotionInfo']['package_status']);
        $result = $packageItemList['promotionInfo'];
        $packageItemList = array_bind_key($packageItemList['list'], 'item_id');

        $result['valid'] = true;
        foreach($itemsIds as $itemId)
        {
            $detailData = array();
            $itemParams = array(
                'item_id'=>$itemId,
                'fields' => "item_id,item_store,image_default_id,price,title,spec_desc,sku,spec_index,item_status",
            );
            $detailData = app::get('topc')->rpcCall('item.get',$itemParams);

            //设置组合促销当前商品是否有效
            if(!$detailData)
            {
                $detailData = $packageItemList[$itemId];
                $detailData['valid'] = false;
                $detailData['is_delete'] = true;
            }
            else
            {
                $detailData['valid'] = $this->__checkItemValid($detailData);
            }

            //设置当前组合促销是否失效
            if( $result['valid'] && ! $detailData['valid'] )
            {
                $result['valid'] = false;
            }

            $skuData = null;
            if( $packageItemList[$itemId]['sku_ids'] )
            {
                $specDesc = null;
                foreach( (array)explode(',',$packageItemList[$itemId]['sku_ids']) as $skuId )
                {
                    $skuData[$skuId] = $detailData['sku'][$skuId];
                    $specDescValue = $skuData[$skuId]['spec_desc']['spec_value_id'];
                    foreach( $specDescValue as $specId=>$specValueId )
                    {
                        $specDesc[$specId][$specValueId] = $detailData['spec_desc'][$specId][$specValueId];
                    }
                }
                $detailData['spec_desc'] = $specDesc;
            }
            else
            {
                $skuData = $detailData['sku'];
            }

            if(count($detailData['sku']) == 1)
            {
                $detailData['default_sku_id'] = array_keys($detailData['sku'])[0];
            }

            $detailData['sku'] = $this->__getSpec($detailData['spec_desc'], $skuData);
            unset($detailData['spec_desc']);
            $detailData['package_price'] = $packageItemList[$itemId]['package_price'];
            $result['item'][] = $detailData;
        }

        $result['package_id'] = $params['package_id'];
        $result['total_package_price'] = ecmath::number_plus(array_column($packageItemList,'package_price'));
        $result['total_old_price'] = ecmath::number_plus(array_column($packageItemList,'price'));

        return $result;
    }

    private function __getSpec($spec, $sku)
    {
        if( empty($spec) ) return null;

        foreach( $sku as $row )
        {
            $skuData['sku_id'] = $row['sku_id'];
            $skuData['item_id'] = $row['item_id'];
            $skuData['price'] = $row['price'];
            $skuData['mkt_price'] = $row['mkt_price'];
            $skuData['store'] = $row['realStore'];

            if( $row['status'] == 'delete')
            {
                $skuData['valid'] = false;
            }
            else
            {
                $skuData['valid'] = true;
            }

            $skuData['spec'] = implode('_',$row['spec_desc']['spec_value_id']);
            $result['specSku'][] = $skuData;

            $specIds = array_flip($row['spec_desc']['spec_value_id']);
            $specInfo = explode('、',$row['spec_info']);
            foreach( $specInfo  as $info)
            {
                $id = each($specIds)['value'];
                $specName[$id] = explode('：',$info)[0];
            }
        }

        foreach( $spec as $specId=>$specVal )
        {
            rsort($specVal);
            $result['spec_desc'][] = array(
                'spec_id' => $specId,
                'spec_name' => $specName[$specId],
                'spec_val' => $specVal,
            );
        }

        return $result;
    }

    private function __checkItemValid($itemsInfo)
    {
        if( empty($itemsInfo) ) return false;

        //违规商品
        if( $itemsInfo['violation'] == 1 ) return false;

        //未启商品
        if( $itemsInfo['disabled'] == 1 ) return false;

        //未上架商品
        if($itemsInfo['approve_status'] != 'onsale') return false;

        return true;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"package_id":"1","shop_id":1,"package_name":"第二件5折","package_desc":"","valid_grade":"1","package_total_price":"549.000","free_postage":0,"promotion_tag":"组合促销","reason":null,"valid":true,"item":[{"item_id":51,"image_default_id":"http://images.bbc.shopex123.com/images/5a/fb/70/095878c612c50e30fc2d92b9ac457521e759f388.png","price":"299.000","title":"Gap全棉原色靛蓝修身水洗牛仔裤|男装534318","store":99,"freez":0,"realStore":99,"shop_id":1,"approve_status":"onsale","reason":null,"list_time":1454245407,"delist_time":1453874646,"sku":{"specSku":[{"sku_id":191,"item_id":51,"price":"299.000","mkt_price":"0.000","store":20,"valid":true,"spec":"16_19"},{"sku_id":192,"item_id":51,"price":"299.000","mkt_price":"0.000","store":19,"valid":true,"spec":"16_20"},{"sku_id":193,"item_id":51,"price":"299.000","mkt_price":"0.000","store":20,"valid":true,"spec":"16_21"},{"sku_id":194,"item_id":51,"price":"299.000","mkt_price":"0.000","store":20,"valid":true,"spec":"16_22"},{"sku_id":195,"item_id":51,"price":"299.000","mkt_price":"0.000","store":10,"valid":true,"spec":"16_23"},{"sku_id":196,"item_id":51,"price":"299.000","mkt_price":"0.000","store":10,"valid":true,"spec":"16_24"}],"spec_desc":[{"spec_id":1,"spec_name":"颜色","spec_val":[{"private_spec_value_id":"","spec_value":"深蓝色","spec_value_id":"16","spec_image":"http://images.bbc.shopex123.com/images/5a/fb/70/095878c612c50e30fc2d92b9ac457521e759f388.png","spec_image_url":"http://images.bbc.shopex123.com/images/5a/fb/70/095878c612c50e30fc2d92b9ac457521e759f388.png"}]},{"spec_id":2,"spec_name":"尺码","spec_val":[{"private_spec_value_id":"","spec_value":"xxxl","spec_value_id":"24"},{"private_spec_value_id":"","spec_value":"xxl","spec_value_id":"23"},{"private_spec_value_id":"","spec_value":"xl","spec_value_id":"22"},{"private_spec_value_id":"","spec_value":"s","spec_value_id":"19"},{"private_spec_value_id":"","spec_value":"m","spec_value_id":"20"},{"private_spec_value_id":"","spec_value":"l","spec_value_id":"21"}]}]},"valid":true,"package_price":"150.000"},{"item_id":64,"image_default_id":"http://images.bbc.shopex123.com/images/18/0a/d3/6df7c7cb12554dc912c2c842fbe3c645783b8da0.png","price":"399.000","title":"Gap纯棉经典百搭直筒卡其裤|男装225117","store":360,"freez":0,"realStore":360,"shop_id":1,"approve_status":"onsale","reason":null,"list_time":1454245406,"delist_time":1453874646,"sku":{"specSku":[{"sku_id":303,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"4_19"},{"sku_id":304,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"4_20"},{"sku_id":305,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"4_21"},{"sku_id":306,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"4_22"},{"sku_id":307,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"4_23"},{"sku_id":308,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"4_24"},{"sku_id":309,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"11_19"},{"sku_id":310,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"11_20"},{"sku_id":311,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"11_21"},{"sku_id":312,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"11_22"},{"sku_id":313,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"11_23"},{"sku_id":314,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"11_24"},{"sku_id":315,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"16_19"},{"sku_id":316,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"16_20"},{"sku_id":317,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"16_21"},{"sku_id":318,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"16_22"},{"sku_id":319,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"16_23"},{"sku_id":320,"item_id":64,"price":"399.000","mkt_price":"0.000","store":20,"valid":true,"spec":"16_24"}],"spec_desc":[{"spec_id":1,"spec_name":"颜色","spec_val":[{"private_spec_value_id":"","spec_value":"黑色","spec_value_id":"4","spec_image":"http://images.bbc.shopex123.com/images/55/74/9e/581809ab39c58f8bb8954046eba847402641502f.jpg","spec_image_url":"http://images.bbc.shopex123.com/images/55/74/9e/581809ab39c58f8bb8954046eba847402641502f.jpg"},{"private_spec_value_id":"","spec_value":"深蓝色","spec_value_id":"16","spec_image":"http://images.bbc.shopex123.com/images/ca/0d/3c/b0cb6cd4eb77e5bb310d71c2a670cd960e5fa966.png","spec_image_url":"http://images.bbc.shopex123.com/images/ca/0d/3c/b0cb6cd4eb77e5bb310d71c2a670cd960e5fa966.png"},{"private_spec_value_id":"","spec_value":"卡其色","spec_value_id":"11","spec_image":"http://images.bbc.shopex123.com/images/c3/a4/a6/da59ec96048b815657eb3c4b8e6def270b24473f.png","spec_image_url":"http://images.bbc.shopex123.com/images/c3/a4/a6/da59ec96048b815657eb3c4b8e6def270b24473f.png"}]},{"spec_id":2,"spec_name":"尺码","spec_val":[{"private_spec_value_id":"","spec_value":"xxxl","spec_value_id":"24"},{"private_spec_value_id":"","spec_value":"xxl","spec_value_id":"23"},{"private_spec_value_id":"","spec_value":"xl","spec_value_id":"22"},{"private_spec_value_id":"","spec_value":"s","spec_value_id":"19"},{"private_spec_value_id":"","spec_value":"m","spec_value_id":"20"},{"private_spec_value_id":"","spec_value":"l","spec_value_id":"21"}]}]},"valid":true,"package_price":"399.000"}],"total_package_price":"549.00","total_old_price":"698.00"}}';
    }

}
