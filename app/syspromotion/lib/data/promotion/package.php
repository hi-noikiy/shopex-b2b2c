<?php

class syspromotion_data_promotion_package extends syspromotion_abstract_promotions {

    /**
     * 促销标签
     */
    public function getPromotionTag()
    {
        return '组合促销';
    }

    /**
     * 促销类型
     */
    public function getPromotionType()
    {
        return 'package';
    }

    /**
    |*促销状态字段名称
     */
    public function getPromotionStatusCol()
    {
        return 'package_status';
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
        return app::get('syspromotion')->model('package');
    }

    /**
     * 获取促销规则关联商品表的model
     */
    public function getMdlPromotionItem()
    {
        return app::get('syspromotion')->model('package_item');
    }

    /**
     * 保存促销规则
     */
    public function save()
    {
        $data = $this->promotionData;
        $this->getMdlPromotion()->save($data);

        if( $data['package_id'] )
        {
            $this->__savePackageItem($data['package_id']);
        }

        return $data['package_id'];
    }

    /**
     * 保存组合促销关联的商品信息
     *
     * @param int $packageId 促销规则ID
     */
    private function __savePackageItem($packageId)
    {
        $this->getMdlPromotionItem()->delete(array('package_id'=>$packageId));
        foreach($this->promotionData['package_rel_item'] as $itemRow)
        {
            $itemid = $itemRow['item_id'];
            if( !$this->promotionRelItems[$itemid] )
            {
                continue;
            }

            $skuIds = null;
            if( $itemRow['sku_id'] )
            {
                $itemRow['sku_id'] = explode(',',$itemRow['sku_id']);
                $skuIds = array_intersect($itemRow['sku_id'], $this->promotionRelItems[$itemid]['sku_id']);
            }

            $packageRelationItem = array(
                'package_id' => $packageId,
                'item_id' => $itemid,
                'sku_ids' => $skuIds ? implode(',', $skuIds) : '',
                'shop_id' => $this->getShopId(),
                'promotion_tag' => $this->getPromotionTag(),
                'leaf_cat_id' => $this->promotionRelItems[$itemid]['cat_id'],
                'brand_id' => $this->promotionRelItems[$itemid]['brand_id'],
                'package_price' => $itemRow['package_price'],
                'title' => $this->promotionRelItems[$itemid]['title'],
                'price' => $this->promotionRelItems[$itemid]['price'],
                'image_default_id' => $this->promotionRelItems[$itemid]['image_default_id'],
                'start_time' => $this->promotionData['start_time'],
                'end_time' => $this->promotionData['end_time'],
            );

            $this->getMdlPromotionItem()->save($packageRelationItem);
        }

        return true;
    }

    /**
     * 格式化促销关联商品数据
     *
     * @param string|array $filter 参数促销商品的条件
     */
    public function formatPromotoinRelItem()
    {
        $itemIds = array_column($this->promotionData['package_rel_item'], 'item_id');
        $this->promotionRelItems = $this->_getItemByItemId($itemIds);
        return $this;
    }

    /**
     * 格式化促销的条件
     */
    public function formatPromotionCondition()
    {
        $priceArray = array_column($this->promotionData['package_rel_item'], 'package_price');
        $this->promotionData['package_total_price'] = ecmath::number_plus($priceArray);
        return $this;
    }

    /**
     * 校验添加促销规则数据
     *
     * @param $data api提交过来的数据
     */
    public function checkAddPromotionData($data)
    {
        //校验促销有效期时间
        $this->checkSavePromotionExpire($data['start_time'], $data['end_time']);

        $countAresult = count($data['package_rel_item']);
        if($countAresult<2)
        {
            throw new \LogicException("最少添加2个商品!");
        }

        if($countAresult>10)
        {
            throw new \LogicException("最多添加10个商品!");
        }

        return $this;
    }
}
