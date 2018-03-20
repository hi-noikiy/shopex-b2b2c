<?php

class syspromotion_data_promotion_gift extends syspromotion_abstract_promotions {

    /**
     * 促销标签
     */
    public function getPromotionTag()
    {
        return '赠品';
    }

    /**
     * 促销类型
     */
    public function getPromotionType()
    {
        return 'gift';
    }

    /**
    |*促销状态字段名称
     */
    public function getPromotionStatusCol()
    {
        return 'gift_status';
    }

    /**
    |*促销生效开始时间字段名称
     */
    public function getPromotionStartTimeCol()
    {
        return 'start_time';
    }

    //获取促销规则的model
    public function getMdlPromotion()
    {
        return app::get('syspromotion')->model('gift');
    }

    /**
     * 获取促销规则关联商品表的model
     */
    public function getMdlPromotionItem()
    {
        return app::get('syspromotion')->model('gift_item');;
    }

    public function save()
    {
        $data = $this->promotionData;
        $this->getMdlPromotion()->save($data);

        if( $data['gift_id'] )
        {
            $this->__saveGiftItem($data['gift_id']);

            if( !$this->__saveGiftSku($data['gift_id']) )
            {
                throw new \LogicException('赠品促销关联的商品赠品信息保存失败!');
            }
        }

        return $data['gift_id'];
    }

    private function __saveGiftSku($giftId)
    {
        $objMdlGiftSku = app::get('syspromotion')->model('gift_sku');
        $objMdlGiftSku->delete(array('gift_id'=>$giftId));
        foreach($this->promotionCondition['gift_item'] as $key=>$value)
        {
            $giftRelationSku = array(
                'gift_id' => $giftId,
                'sku_id' => $value['sku_id'],
                'item_id' => $value['item_id'],
                'shop_id' => $this->getShopId(),
                'gift_num' => $value['gift_num'],
                'withoutReturn' => $value['withoutReturn'],
                'start_time' => $this->promotionData['start_time'],
                'end_time' => $this->promotionData['end_time'],
            );
            $objMdlGiftSku->save($giftRelationSku);
        }
        return true;
    }

    private function __saveGiftItem($giftId)
    {
        $this->getMdlPromotionItem()->delete(array('gift_id'=>$giftId));
        foreach($this->promotionData['gift_rel_item'] as $itemRow)
        {
            $itemid = $itemRow['item_id'];
            if(!$this->promotionRelItems[$itemid])
            {
                continue;
            }

            $skuIds = null;
            if( $itemRow['sku_id'] )
            {
                $itemRow['sku_id'] = explode(',',$itemRow['sku_id']);
                $skuIds = array_intersect($itemRow['sku_id'], $this->promotionRelItems[$itemid]['sku_id']);
            }

            $giftRelationItem = array(
                'gift_id' => $giftId,
                'item_id' => $itemid,
                'sku_ids' => $skuIds ? implode(',', $skuIds) : '',
                'shop_id' => $this->getShopId(),
                'promotion_tag' => $this->getPromotionTag(),
                'leaf_cat_id' => $this->promotionRelItems[$itemid]['cat_id'],
                'brand_id' => $this->promotionRelItems[$itemid]['brand_id'],
                'title' => $this->promotionRelItems[$itemid]['title'],
                'price' => $this->promotionRelItems[$itemid]['price'],
                'image_default_id' => $this->promotionRelItems[$itemid]['image_default_id'],
                'start_time' => $this->promotionData['start_time'],
                'end_time' => $this->promotionData['end_time'],
            );
            $this->getMdlPromotionItem()->save($giftRelationItem);
        }

        return true;
    }

    /**
     * 格式化促销关联商品数据
     */
    public function formatPromotoinRelItem()
    {
        $itemIds = array_column($this->promotionData['gift_rel_item'], 'item_id');
        $this->promotionRelItems = $this->_getItemByItemId($itemIds);

        return $this;
    }

    /**
     * 格式化促销的条件
     */
    public function formatPromotionCondition()
    {
        $this->promotionCondition['gift_item'] = $this->__getGiftData($this->promotionData['gift_item']);
        return $this;
    }

    /*
     * 获取赠品相关数据
     */
    private function __getGiftData($giftItemInfo)
    {
        $skuIds = array_column($giftItemInfo, 'sku_id');
        $searchParams = array(
            'sku_id' => implode(',',$skuIds),
            'shop_id' => $this->getShopId(),
            'fields' => 'item_id,sku_id,title,spec_info,price,shop_id,image_default_id,store.*,item.shop_id,item.sub_stock,item.item_id,bn,status.item_id,status.approve_status',
        );
        $skuList = app::get('syspromotion')->rpcCall('sku.search',$searchParams);
        if(!$skuList)
        {
            throw new \LogicException('作为赠品的商品不存在!');
        }
        $giftItems = $skuList['list'];
        foreach($giftItemInfo as $value)
        {
            $skuId = $value['sku_id'];
            $skuStore = $giftItems[$skuId]['realStore'];
            $quantity = $value['quantity'];
            $withoutReturn = $value['withoutReturn'];
            if($quantity > $skuStore)
            {
                throw new \LogicException('单个赠品的数量不能大于库存总数!');
            }

            unset($giftItems[$skuId]['store'],$giftItems[$skuId]['freez'],$giftItems[$skuId]['realStore']);

            $giftItems[$skuId]['gift_num'] = $quantity;
            $giftItems[$skuId]['withoutReturn'] = $withoutReturn;
        }
        return $giftItems;
    }

    /**
     * 判断赠品中是否包含商品。
     * 为了兼容oms的逻辑。oms禁止商品和赠品是同一sku(其实是oms商品和赠品是同一sku时会产生错误数据，比如同一个订单中出现两条sku相同的数据，并且无法售后)
     *
     * @param $items api提交过来的商品
     * @param $gifts api提交过来的赠品
     * @return bool
     */
    private function __checkIfGiftsInItems($items, $gifts)
    {
        //这里从item.list.get接口一起把所有的item的参数拉过来。
        //fields =  item_id,sku.sku_id
        $itemIdsWithAllSku = [];
        $skuIds = [];

        foreach($items as $item)
        {
            if(!empty($item['sku_id']))
            {
                $skuTmpArr = explode(',',$item['sku_id']);
                $skuIds = array_merge($skuIds, $skuTmpArr);
            }
            else
            {
                $itemIdsWithAllSku[] = $item['item_id'];
            }
        }

        if( count($itemIdsWithAllSku) > 0 )
        {
            $itemIdsWithAllSkuStr = implode(',', $itemIdsWithAllSku);
            $requestParams = array(
                'item_id' => $itemIdsWithAllSkuStr,
                'fields' => 'item_id,sku.sku_id',
            );
            $itemInfos = app::get('syspromotion')->rpcCall('item.list.get',$requestParams);
            foreach($itemInfos as $itemInfo)
            {
                $tmpSkuIds = array_keys($itemInfo['sku']);
                $skuIds = array_merge($skuIds, $tmpSkuIds);
            }
        }

        $item_ids = array_column($items, 'item_id');
        foreach($gifts as $gift)
        {
            if(in_array($gift['sku_id'], $skuIds))
                throw new LogicException('商品和赠品不能是同一SKU');

        }

        return true;
    }

    /**
     * 校验添加促销规则数据
     *
     * @param $data api提交过来的数据
     */
    public function checkAddPromotionData($data)
    {
        $this->__checkIfGiftsInItems($data['gift_rel_item'], $data['gift_item']);
        //校验促销有效期时间
        $this->checkSavePromotionExpire($data['start_time'], $data['end_time']);

        if(count($data['gift_item']) < 1 || count($data['gift_item']) > 4)
        {
            throw new \LogicException('赠品品类必须大于等于1小于等于4');
        }

        $giftRelItemIds = array_column($data['gift_rel_item'],'item_id');
        $itemList = $this->getMdlPromotionItem()->getList('gift_id,title', array('item_id'=>$giftRelItemIds, 'end_time|than'=>$data['start_time'],'status'=>1));
        foreach($itemList as $v)
        {
            if($data['gift_id'] )
            {
                if($v['gift_id'] != $data['gift_id'])
                {
                    throw new \LogicException("商品 {$v['title']} 已经参加别的赠品促销，同一个商品只能应用于一个有效的赠品促销中！");
                }
            }
            else
            {
                throw new \LogicException("商品 {$v['title']} 已经参加别的赠品促销，同一个商品只能应用于一个有效的赠品促销中！");
            }
        }

        return $this;
    }

    public function getGiftSku($giftId)
    {
        return app::get('syspromotion')->model('gift_sku')->getList('*', array('gift_id'=>$giftId));
    }

    /**
     * 删除赠品
     *
     * @param int $gfitId 赠品ID
     */
    public function deletePromotions($giftId, $isDelTag=false)
    {
        parent::deletePromotions($giftId, false);

        //删除赠品sku
        $objMdlgiftSku = app::get('syspromotion')->model('gift_sku');
        if( !$objMdlgiftSku->delete( array('gift_id'=>$giftId, 'shop_id'=>$this->getShopId()) ) )
        {
            throw new \LogicException(app::get('syspromotion')->_('删除赠品促销包含的赠品失败'));
        }

        return true;
    }

    //获取赠品促销的商品
    public function getGiftItemByItemId($itemIds,$filter=array())
    {
        if( !$itemIds ) return array();

	   	$giftItem = array();

        $itemFilter = $filter;
        $itemFilter['item_id'] = explode(',',$itemIds);
        $objMdlItemGift = app::get('syspromotion')->model('gift_item');
        $giftItem = $objMdlItemGift->getList('*', $itemFilter);
        if( !$giftItem ) return array();

        $giftId = array_column($giftItem,'gift_id');
        $giftSkuList = $this->getGiftSku($giftId);

        //获取赠品商品数据
        $skuId = array_column($giftSkuList,'sku_id');
        $searchParams = array(
            'sku_id' => implode(',',$skuId),
            'fields' => 'item_id,sku_id,title,spec_info,shop_id,image_default_id,store.*,item.shop_id,item.sub_stock,item.item_id,bn,status.item_id,status.approve_status',
        );
        $skuList = app::get('syspromotion')->rpcCall('sku.search',$searchParams);
        $skuList = $skuList['list'];
        foreach($giftSkuList as $key=>&$value)
        {
            if(isset($skuList[$value['sku_id']]['approve_status']) && $skuList[$value['sku_id']]['approve_status'] != "onsale" )
            {
                unset($giftSkuList[$key]);
                continue;
            }
            if($skuList[$value['sku_id']])
            {
                $value = array_merge($value,$skuList[$value['sku_id']]);
            }
        }

        if(!$giftSkuList) return array();

        foreach($giftItem as $key=>&$value)
        {
            foreach($giftSkuList as $k=>$val)
            {
                if($value['gift_id'] !=$val['gift_id'])
                {
                    continue;
                }
                $value['gift_item'][] = $val;
            }
        }


        return $giftItem;
    }
}
