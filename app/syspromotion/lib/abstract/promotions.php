<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

abstract class syspromotion_abstract_promotions {

    //促销类型
    abstract public function getPromotionType();
    //促销标签
    abstract public function getPromotionTag();
    //促销状态字段名称
    abstract public function getPromotionStatusCol();
    //促销生效时间
    abstract public function getPromotionStartTimeCol();
    //促销规则model
    abstract public function getMdlPromotion();
    //促销规则关联商品model
    abstract public function getMdlPromotionItem();

    //保存促销
    abstract public function save();
    //根据促销关联商品条件获取商品数据
    abstract public function formatPromotoinRelItem();
    //格式化促销条件
    abstract public function formatPromotionCondition();
    //验证新增促销数据
    abstract public function checkAddPromotionData($data);

    /**
     * 添加促销的时候需要初始化的数据
     */
    public function addPromotionInitData()
    {
        $this->promotionData['created_time'] = time();
        return $this;
    }

    /**
     * 保存促销的时候设置，当前促销的状态
     *
     */
    public function setPromotionStatus($status)
    {
        if( $status )
        {
            $this->status = $status;
        }
        else
        {
            if(app::get('sysconf')->getConf('shop.promotion.examine'))
            {
                $this->status = 'non-reviewed';
            }
            else
            {
                $this->status = 'agree';
            }
        }
    }

    /**
     * 获取当前保存促销规则的状态
     */
    public function getPromotionStatus()
    {
        return $this->status;
    }

    /**
     * 处理保存促销规则数据
     */
    public function prePromotionData($data)
    {
        //合并新增促销初始的数据 addPromotionInitData 方法中定义的参数
        $this->promotionData = $this->promotionData ? array_merge($this->promotionData, $data) : $data;

        $this->setPromotionStatus();

        $this->promotionData['shop_id']                      = $this->getShopId();
        $this->promotionData['promotion_tag']                = $this->getPromotionTag();
        $this->promotionData[$this->getPromotionStatusCol()] = $this->getPromotionStatus();

        return $this;
    }

    /**
     * 根据商品ID集合获取商品数据
     *
     * @param array $itemIds
     */
    protected function _getItemByItemId($itemIds)
    {
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
                'shop_id' => $this->shopId,
                'fields' => 'item_id,title,image_default_id,cat_id,brand_id,price,sku_id',
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

    /**
     * 设置操作店铺的ID
     *
     * @param int $shopId
     */
    public function setShopId($shopId=null)
    {
        $this->shopId = $shopId;
    }

    /**
     * 获取店铺ID
     */
    public function getShopId()
    {
        if( !$this->shopId  )
        {
            throw new LogicException('shop_id cannot be empty');
        }

        return $this->shopId;
    }

    /**
     * 校验更新促销规则数据
     */
    public function checkUpdatePromotionData($data)
    {
        $rows = $this->getPromotionStatusCol().','.$this->getPromotionStartTimeCol().','.'shop_id';
        $idColumn = $this->getMdlPromotion()->idColumn;
        $promotionInfo = $this->getMdlPromotion()->getRow($rows, array($idColumn=>$data[$idColumn]));

        $this->checkUpdateData($promotionInfo);

        //校验保存的数据
        $this->checkAddPromotionData($data);

        return $this;
    }

    /**
     *更新促销校验
     *
     * @param array $promotionData 促销规则数据
     */
    public function checkUpdateData($promotionData)
    {
        if( !$promotionData )
        {
            throw new \LogicException('编辑促销不存在');
        }

        if( $promotionData['shop_id'] != $this->getShopId() )
        {
            throw new \LogicException('编辑促销数据无效');
        }

        if( !app::get('sysconf')->getConf('shop.promotion.examine') )
        {
            if( time() >= $promotionData[$this->getPromotionStartTimeCol()] )
            {
                throw new \LogicException('促销生效时间内不可进行编辑!');
            }
        }
        else
        {
            if( in_array($promotionData[$this->getPromotionStatus()],['pending','agree','cancel']) )
            {
                throw new \LogicException('促销当前状态不可进行编辑！');
            }
        }

        return true;
    }

    public function checkSavePromotionExpire($startTime, $endTime)
    {
        if( $startTime <= time() )
        {
            throw new \LogicException('促销生效时间不能小于当前时间！');
        }

        if( $endTime <= $startTime )
        {
            throw new \LogicException('促销结束时间不能小于开始时间！');
        }

        return true;
    }

    /**
     * 将添加的促销存入促销关联表
     *
     * @param int $relPromotionId
     */
    public function savePromotions($relPromotionId, $promotionName, $promotionDesc)
    {
        $objMdlPromotions = app::get('syspromotion')->model('promotions');
        // 如果原来此促销已经存在则更新原数据而不是新添加
        $filter = array(
            'promotion_type' => $this->getPromotionType(),
            'rel_promotion_id' => $relPromotionId,
        );

        if( $row = $objMdlPromotions->getRow('promotion_id', $filter) )
        {
            $proData['promotion_id'] = $row['promotion_id'];
        }
        $proData['rel_promotion_id'] = $relPromotionId;
        $proData['shop_id']          = $this->getShopId();
        $proData['promotion_type']   = $this->getPromotionType();
        $proData['promotion_name']   = $promotionName;
        $proData['promotion_desc']   = $promotionDesc;
        $proData['promotion_tag']    = $this->getPromotionTag();
        $proData['used_platform']    = $this->promotionData['used_platform'];
        $proData['start_time']       = $this->promotionData[$this->getPromotionStartTimeCol()];
        $proData['end_time']         = $this->promotionData['end_time'];
        $proData['created_time']     = $this->promotionData['created_time'];
        $proData['check_status']     = $this->getPromotionStatus();

        $objMdlPromotions->save($proData);

        return $proData['promotion_id'];
    }

    /**
     * 取消促销
     *
     * @param int $relPromotionId 具体促销类型的促销ID
     * @param boolean $isDelTag 是否需要删除促销关联商品表数据
     */
    public function cancelPromotions($relPromotionId, $isDelTag=true)
    {
        $relPromotionIdCol = $this->getMdlPromotion()->idColumn;

        // 修改满减促销状态
        if( !$this->getMdlPromotion()->update( array($this->getPromotionStatusCol()=>'cancel'), array($relPromotionIdCol=>$relPromotionId, 'shop_id'=>$this->getShopId() )))
        {
            throw new \LogicException(app::get('syspromotion')->_('取消促销失败'));
        }

        $objMdlPromotions = app::get('syspromotion')->model('promotions');
        // 删除商品系统的商品关联的促销
        $promotionInfo = $objMdlPromotions->getRow('promotion_id', array('rel_promotion_id'=>$relPromotionId, 'promotion_type'=>$this->getPromotionType()));
        if( $promotionInfo )
        {
            $promotionId = $promotionInfo['promotion_id'];
            if( $isDelTag )
            {
                $flag = app::get('syspromotion')->rpcCall('item.promotion.deleteTag',array('promotion_id'=>$promotionId));
                if( !$flag )
                {
                    throw new \LogicException(app::get('syspromotion')->_('取消促销失败'));
                }
            }

            // 修改促销关联表的促销状态
            if( !$objMdlPromotions->update(array('check_status'=>'cancel'), array('promotion_id'=>$promotionId)) )
            {
                throw new \LogicException(app::get('syspromotion')->_('修改促销状态失败'));
            }
        }

        return true;
    }

    /**
     * 校验要删除的促销
     *
     * @param array $promotion 促销数据
     */
    public function checkDeletePromotion($promotionData)
    {
        if( !$promotionData )
        {
            throw new \LogicException('删除促销不存在');
        }

        if( !app::get('sysconf')->getConf('shop.promotion.examine') )
        {
            if( time() > $promotionData[$this->getPromotionStartTimeCol()] )
            {
                throw new \LogicException('促销生效后则不可删除');
            }
        }

        return true;
    }

    /**
     * 删除促销
     *
     * @param int $relPromotionId 促销规则ID
     * @param boolean $isDelTag 是否需要删除关联商品促销Tag
     */
    public function deletePromotions($relPromotionId, $isDelTag=true)
    {
        $relPromotionIdCol = $this->getMdlPromotion()->idColumn;
        $row = 'shop_id,'.$this->getPromotionStartTimeCol();
        $relPromotionInfo = $this->getMdlPromotion()->getRow($row, array($relPromotionIdCol=>$relPromotionId,'shop_id'=>$this->getShopId()));

        $this->checkDeletePromotion($relPromotionInfo);

        // 删除主表数据
        if( !$this->getMdlPromotion()->delete( array($relPromotionIdCol=>$relPromotionId) ) )
        {
            throw new \LogicException(app::get('syspromotion')->_('删除促销失败'));
        }

        //删除关联的商品
        if( !$this->getMdlPromotionItem()->delete( array($relPromotionIdCol=>$relPromotionId) ) )
        {
            throw new \LogicException(app::get('syspromotion')->_('删除促销失败'));
        }

        $objMdlPromotions = app::get('syspromotion')->model('promotions');
        $promotionInfo = $objMdlPromotions->getRow('promotion_id', array('rel_promotion_id'=>$relPromotionId, 'promotion_type'=>$this->getPromotionType()));
        if( $promotionInfo['promotion_id'] )
        {
            $promotionId = $promotionInfo['promotion_id'];
            if( !$objMdlPromotions->delete(array('promotion_id'=>$promotionId)) )
            {
                throw new \LogicException(app::get('syspromotion')->_('删除促销失败'));
            }

            if( $isDelTag )
            {
                $flag = app::get('syspromotion')->rpcCall('item.promotion.deleteTag',array('promotion_id'=>$promotionId));
                if(!$flag)
                {
                    throw new \LogicException(app::get('syspromotion')->_('删除促销失败'));
                }
            }
        }
        return true;
    }

    public function getPromotionList($filter)
    {
        $filter['shop_id'] = $this->getShopId();
        $orderBy = $this->getMdlPromotion()->idColumn.' DESC';
        return $this->getMdlPromotion()->getList('*', $filter, '0', '-1', $orderBy);
    }

    public function getPromoitonInfo($relPromotionId)
    {
        $idColumn = $this->getMdlPromotion()->idColumn;
        return $this->getMdlPromotion()->getRow('*', array($idColumn=>$relPromotionId));
    }

    public function getPromtionItems($relPromotionId)
    {
        $idColumn = $this->getMdlPromotion()->idColumn;
        return $this->getMdlPromotionItem()->getList('*', array($idColumn=>$relPromotionId));
    }
}
