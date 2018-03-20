<?php
/**
 * ShopEx licence
 *
 * 满减促销
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syspromotion_data_promotion_fullminus extends syspromotion_abstract_promotions {

    /**
     * 促销标签
     */
    public function getPromotionTag()
    {
        return '满减';
    }

    /**
     * 促销类型
     */
    public function getPromotionType()
    {
        return 'fullminus';
    }

    /**
    |*促销状态字段名称
     */
    public function getPromotionStatusCol()
    {
        return 'fullminus_status';
    }

    /**
    |*促销生效开始时间字段名称
     */
    public function getPromotionStartTimeCol()
    {
        return 'start_time';
    }

    /**
     * 满减促销model
     */
    private $__objMdlFullminus = null;

    /**
     * 促销基础通用数据
     *
     * 促销名称，促销描述，促销有效期等
     */
    public $common = [];

    /**
     * 促销商品条件
     */
    public $filter = null;

    /**
     * 促销绑定的商品ID，根据促销商品的条件获取
     */
    public $promotionRelItems = [];

    /**
     * 促销规则，满足促销的条件
     */
    public $promotionCondition = [];

    public function __construct()
    {
        $this->__objMdlFullminus = app::get('syspromotion')->model('fullminus');
    }

    //获取促销规则的model
    public function getMdlPromotion()
    {
        return $this->__objMdlFullminus;
    }

    /**
     * 获取促销规则关联商品表的model
     */
    public function getMdlPromotionItem()
    {
        return app::get('syspromotion')->model('fullminus_item');
    }

    /**
     * 保存促销规则
     */
    public function save()
    {
        $data = $this->promotionData;
        $this->__objMdlFullminus->save($data);

        if( $data['fullminus_id'] )
        {
            $promotionId = $this->savePromotions($data['fullminus_id'], $data['fullminus_name'], $data['fullminus_desc']);

            $this->__saveFullminusItem($data['fullminus_id'], $promotionId);
        }
        return $data['fullminus_id'];
    }

    /**
     * 保存满减促销关联的商品信息
     *
     * @param int $fullminusId 满减促销ID
     */
    private function __saveFullminusItem($fullminusId, $promotionId)
    {
        $objMdlFullminusItem = app::get('syspromotion')->model('fullminus_item');
        // 先删除满减关联的商品
        $objMdlFullminusItem->delete(array('fullminus_id'=>$fullminusId));
        foreach($this->promotionData['fullminus_rel_item'] as $itemRow )
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

            $fullminusRelationItem = array(
                'fullminus_id' => $fullminusId,
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
            $objMdlFullminusItem->save($fullminusRelationItem);

            $apiData = array(
                'item_id' => $itemid,
                'sku_id' => $skuIds ? implode(',', $skuIds) : '',
                'promotion_id' => $promotionId,
            );
            // 新的商品及促销关联接口
            app::get('syspromotion')->rpcCall('item.promotion.addTag', $apiData);
        }

        return true;
    }

    /**
     * 格式化促销关联商品数据
     */
    public function formatPromotoinRelItem()
    {
        $itemIds = array_column($this->promotionData['fullminus_rel_item'], 'item_id');
        $this->promotionRelItems = $this->_getItemByItemId($itemIds);
        return $this;
    }

    /**
     * 格式化促销的条件
     */
    public function formatPromotionCondition()
    {
        foreach( $this->promotionData['condition_value'] as $row )
        {
            $conditionValue[] = $row['full'].'|'.$row['minus'];
        }

        $this->promotionData['condition_value'] = implode(',', $conditionValue);

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

        // 满减金额的规则检验
        $ruleArray = $data['condition_value'];
        $ruleLength = count($ruleArray);
        for($i=0; $i<$ruleLength; $i++)
        {
            if( $ruleArray[$i]['full'] <= $ruleArray[$i]['minus'] )
            {
                throw new \LogicException('满减金额的(满足金额)必须大于(减去金额)！');
            }
            if( $i<$ruleLength-1 && $ruleArray[$i]['full'] >= $ruleArray[$i+1]['full'] )
            {
                throw new \LogicException('满减金额的(满足金额)必须依次递增！');
            }
            if( $i<$ruleLength-1 && $ruleArray[$i]['minus'] >= $ruleArray[$i+1]['minus'] )
            {
                throw new \LogicException('满减金额的(减去金额)必须依次递增！');
            }
        }

        return $this;
    }
}

