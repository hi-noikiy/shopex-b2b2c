<?php
class syspromotion_api_activity_register{

    public $apiDescription = "活动报名";
    public $use_strict_filter = true; // 是否严格过滤参数
    public function getParams()
    {
        $data['params'] = array(
            'activity_id' => ['type' => 'int','valid'=>'required','desc'=>'活动id','msg'=>'活动id必填'],
            'shop_id' => ['type' => 'int','valid'=>'required','desc'=>'店铺id','msg'=>'店铺id必填'],
            'register_item' => ['type'=>'jsonArray','valid'=>'required','desc'=>'报名商品','msg'=>'请选择活动商品','params'=>array(
                'item_id' => ['type'=>'int','valid'=>'required','desc'=>'商品id '],
                'sku_id' =>['type'=>'string','valid'=>'','desc'=>'SKU ID以逗号隔开, 数据为空则表示所有规格都参加促销'],
                'activity_price' => ['type'=>'string', 'valid'=>'required','desc'=>'商品促销价格', 'msg'=>'请填写促销商品的促销价格'],
                'sku_activity_price' => ['type' => 'array','valid'=>'','desc'=>'货品活动价','msg'=>'请填写促销商品的促销价格'],
            )],
        );
        return $data;
    }

    public function registerActivity($params)
    {
        $objMdlActivityRegister = app::get('syspromotion')->model('activity_register');
        $objMdlActivityItem = app::get('syspromotion')->model('activity_item');

        $shopdata = $this->__getShopData($params);
        $activityData = $this->__getActivity($params,$shopdata);
        $itemData = $this->__getItemById($params,$actityData);

        //验证促销信息是否合格
        $registerItem = array_bind_key($params['register_item'],'item_id');
        foreach($itemData as $key=>$value)
        {
            $minprice = $value['price']*($activityData['discount_min']/100);
            $maxprice = $value['price']*($activityData['discount_max']/100);
            $activityPrice = $registerItem[$value['item_id']]['activity_price'];
            if($activityPrice < $minprice || $activityPrice > $maxprice)
            {
                throw new \LogicException(app::get('syspromotion')->_('请在商品折扣范围内设置促销价格！'));
            }

            $registerSku = array_bind_key($registerItem[$value['item_id']]['sku_activity_price'],'sku_id');
            $skuIds = explode(',',$registerItem[$value['item_id']]['sku_id']);
            foreach($value['sku'] as $k=>$skus)
            {
                if(!$value['nospec'] && !$registerSku[$skus['sku_id']] && !$skuIds)
                {
                    throw new \LogicException(app::get('syspromotion')->_('请填写sku促销价格！'));
                }

                if($skuIds && in_array($skus['sku_id'],$skuIds) && !$registerSku[$skus['sku_id']])
                {
                    throw new \LogicException(app::get('syspromotion')->_('请填写sku促销价格！'));
                }

                if($registerSku[$skus['sku_id']])
                {
                    $skuminprice = $skus['price']*($activityData['discount_min']/100);
                    $skumaxprice = $skus['price']*($activityData['discount_max']/100);
                    $activityPriceSku = $registerSku[$skus['sku_id']]['price'];
                    if($activityPriceSku < $skuminprice || $activityPriceSku > $skumaxprice)
                    {
                        throw new \LogicException(app::get('syspromotion')->_('请在商品折扣范围内设置sku促销价格！'));
                    }
                }
            }

            $itemIds[] = $value['item_id'];
        }

        //检查商品参加活动次数
        $checkItemActivity = array(
            'item_id' => $itemIds,
            'end_time|than' => time(),
            'activity_id|noequal' => $params['activity_id'],
        );
        $oldActivity = $objMdlActivityItem->getList('activity_id, title,verify_status',$checkItemActivity);
        if($oldActivity)
        {
            foreach($oldActivity as $data)
            {
                $title = $data['title']."、";
            }
            throw new \LogicException("商品 {$title} 已经参加别的团购，同一个商品只能应用于一个有效的团购促销中！");
        }


        //保存报名信息
        $db = app::get('syspromotion')->database();
        $db->beginTransaction();

        try{
            $objMdlActivityRegister->delete(array('activity_id'=>$params['activity_id'], 'shop_id'=>$params['shop_id']));
            $registerData = array(
                'activity_id' => $params['activity_id'],
                'shop_id' => $params['shop_id'],
                'modified_time' =>time(),
            );
            if( !$objMdlActivityRegister->save($registerData) )
            {
                throw \LogicException('活动报名保存失败');
            }

            $objMdlActivityItem->delete(array('activity_id'=>$params['activity_id'], 'shop_id'=>$params['shop_id']));
            foreach($registerItem as $itemId=>$data)
            {
                $skuIds = explode(',',$data['sku_id']);
                $skuPrice = array();
                foreach($data['sku_activity_price'] as $value)
                {
                    $skuPrice[$value['sku_id']] = $value['price'];
                }

                $saveRegisterItemData = array(
                    'activity_id' => $params['activity_id'],
                    'shop_id' => $params['shop_id'],
                    'item_id' => $data['item_id'],
                    'sku_ids' => $data['sku_id'],
                    'cat_id' => $itemData[$itemId]['cat_id'],
                    'title' => $itemData[$itemId]['title'],
                    'item_default_image' => $itemData[$itemId]['image_default_id'],
                    'price' => $itemData[$itemId]['price'],
                    'activity_price' => $data['activity_price'],
                    'sku_activity_price' => $skuPrice,
                    'start_time' => $activityData['start_time'],
                    'end_time' => $activityData['end_time'],
                    'activity_tag' => $activityData['activity_tag'],
                );
                if( !$objMdlActivityItem->save($saveRegisterItemData) )
                {
                    throw \LogicException("活动报名商品保存失败");
                }
            }

            $db->commit();
        }catch(LogicException $e){
            $db->rollback();
            throw $e;
        }
        return true;
    }

    private function __getItemById($params,$activityData)
    {
        $itemIds = array_column($params['register_item'], 'item_id');
        $shopId = $params['shop_id'];
        if( count($itemIds) > 1000 )
        {
            throw new LogicException('最多添加1000个商品');
        }

        $itemIdsChunk = array_chunk($itemIds, 20);
        $itemsListAll = array();
        foreach( $itemIdsChunk as $value )
        {
            $searchParams = array(
                'item_id' => implode(',',$value),
                'shop_id' => $shopId,
                'fields' => 'item_id,title,image_default_id,cat_id,brand_id,price,nospec,sku.price,sku.sku_id',
            );
            $itemsListData = app::get('syspromotion')->rpcCall('item.list.get',$searchParams);
            $itemsListAll = array_merge($itemsListAll,$itemsListData);
        }
        $itemsListAll = array_bind_key($itemsListAll, 'item_id');
        if( !$itemsListAll )
        {
            throw new LogicException('选择的商品数据有误');
        }

        return $itemsListAll;
    }

    private function __getActivity($params,$shopdata)
    {
        $nowtime = time();
        $catId = $shopdata['cat_id'];
        $shoptype = $shopdata['shoptype'];
        $itemContent = array_column($params['register_item'], 'item_id');

        $activityParams = array(
            'activity_id' => $params['activity_id'],
            'fields' => '*',
        );

        $activityInfo = app::get('syspromotion')->rpcCall('promotion.activity.info', $activityParams);
        if(!$activityInfo)
        {
            throw new LogicException('报名的活动信息有误');
        }

        $activityItemIds = array_keys($activityInfo['limit_cat']);
        //判断活动数据
        if($activityInfo['apply_begin_time']< $nowtime && $nowtime<$activityInfo['apply_end_time'])
        {
            if(!(array_intersect($catId,$activityItemIds) && $activityInfo['shoptype'][$shoptype]))
            {
                throw new \LogicException(app::get('syspromotion')->_('抱歉,您不符合申请标准！'));
            }
            else
            {
                if(count($itemContent)>$activityInfo['enroll_limit'])
                {
                    throw new \LogicException(app::get('syspromotion')->_('抱歉,申请报名商品数量超出活动限制数量,申请无效！'));
                }
            }
        }
        else
        {
            throw new \LogicException(app::get('syspromotion')->_('抱歉,当前时间不在活动申请时间范围,申请无效！'));
        }

        return $activityInfo;
    }

    private function __getShopData($params)
    {
        $shopParams = array(
            'shop_id' => $params['shop_id'],
            'fields' =>'cat.cat_name,cat.cat_id,brand.brand_name,brand.brand_id,info',
        );
        $shopdata = app::get('syspromotion')->rpcCall('shop.get.detail',$shopParams);
        if(!$shopdata['shop'])
        {
            throw new LogicException('店铺信息有误');
        }

        foreach($shopdata['cat'] as $key=>$value)
        {
            $result['cat_id'][$key] = $value['cat_id'];
        }

        $result['shoptype'] = $shopdata['shop']['shop_type'];
        return $result;
    }
}
