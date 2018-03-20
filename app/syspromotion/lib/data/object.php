<?php /**
 * ShopEx licence
 *
 * 促销规则处理统一入口
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syspromotion_data_object {

    protected $_objPromotion = null;

    /**
     * 设置支持的促销类型
     */
    public function setPromotion($type, $shopId=null)
    {
        $class = 'syspromotion_data_promotion_'.$type;
        if (class_exists($class) )
        {
            $this->_objPromotion = kernel::single($class);
            $this->_objPromotion->setShopId($shopId);
        }
        else
        {
            throw new \LogicException('促销规则['.$class.']不支持');
        }

        return $this;
    }

    public function getPromotion()
    {
        if( ! $this->_objPromotion )
        {
            throw new \LogicException('请调用setPromotion方法设置促销类型');
        }

        return $this->_objPromotion;
    }

    /**
     * 添加促销规则
     *
     * @param $data
     */
    public function savePromotion($data)
    {
        $idColumn = $this->getPromotion()->getMdlPromotion()->idColumn;
        if( $data[$idColumn] )
        {
            $this->getPromotion()->checkUpdatePromotionData($data);
        }
        else
        {
            //校验添加促销的数据
            $this->getPromotion()->checkAddPromotionData($data);
            $this->getPromotion()->addPromotionInitData();
        }

        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            //保存具体促销规则的数据  满减 满折等促销
            $relPromotionId = $this->getPromotion()->prePromotionData($data)
                ->formatPromotionCondition()
                ->formatPromotoinRelItem()
                ->save();

            if( $relPromotionId ) $db->commit();
        }
        catch(\Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * 取消促销规则
     */
    public function cancelPromotion($relPromotionId, $isItemTag=true)
    {
        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            $this->getPromotion()->cancelPromotions($relPromotionId, $isItemTag);
            $db->commit();
        }
        catch(\Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * 删除促销规则
     */
    public function deletePromotion($relPromotionId, $isItemTag=true)
    {
        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            $this->getPromotion()->deletePromotions($relPromotionId, $isItemTag);
            $db->commit();
        }
        catch(\Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        return true;
    }

    public function __call($action, $arguments)
    {
        if( is_object($this->_objPromotion) && method_exists($this->_objPromotion, $action) )
        {
            return call_user_func_array(array($this->_objPromotion, $action), $arguments);
        }
        else
        {
            throw new \LogicException('未找到调用方法'.$action);
        }
    }

    /**
     * 生成优惠规则
     * @param array $promotionInfo
     * @param string $type
     * @return string
     * */

    public function promotionRule($promotionInfo, $type)
    {
        $ruleStr = '';
        //处理优惠规则
        if($promotionInfo['condition_value'])
        {
            $conditionValue = $this->__getConditionValue($promotionInfo['condition_value']);
        }
        switch ($type)
        {
            //处理满减优惠
        case 'fullminus':
            foreach ($conditionValue as $role)
            {
                $ruleStr .= sprintf('满%d元减%d元，', $role[0], $role[1]);
            }

            if($promotionInfo['canjoin_repeat'] == 1)
            {
                $ruleStr .= '上不封顶。';
            }
            break;

            //处理满折优惠
        case 'fulldiscount':
            foreach ($conditionValue as $role)
            {
                //处理折扣
                $role[1] = $role[1] / 10;
                $ruleStr .= '满'. $role[0] .'元给予'. $role[1] .'折优惠，';
            }
            break;

            //处理XY折优惠
        case 'xydiscount':
            foreach ($conditionValue as $role)
            {
                $role[1] = $role[1] / 10;
                $ruleStr .= '满'. $role[0] .'件给予'. $role[1] .'折优惠，';
            }
            break;
        }

        //处理会员
        $gradeArr = explode(',',$promotionInfo['valid_grade']);
        $gradeStr = $this->__getGradeStr($gradeArr);

        $ruleStr = sprintf('%s%s可参加，可参加次数为%d次。', $ruleStr, $gradeStr, $promotionInfo['join_limit']);

        return $ruleStr;
    }

    /**
     * 处理会员等级
     *  @param array $gradeArr
     *  @return string
     * */
    private function __getGradeStr($gradeArr)
    {
        //生成会员优惠规则
        $gradeStr = '';
        //获取会员列表
        $gradeList = app::get('syspromotion')->rpcCall('user.grade.list');
        $gradeIds = array_column($gradeList, 'grade_id');
        //查看是否所有的会有都可以参加
        if(!array_diff($gradeIds, $gradeArr))
        {
            $gradeStr = '所有会员都';
        }else
        {
            foreach ($gradeList as $mem)
            {
                if(in_array($mem['grade_id'], $gradeArr))
                {
                    $gradeStr .= $mem['grade_name'].'，';
                }
            }
            $gradeStr = rtrim($gradeStr, '，');
        }

        return $gradeStr;
    }

    /**
     * 处理优惠数据
     * @param string $data
     * @return array
     * */
    private function __getConditionValue($data)
    {
        $conditionValue = explode(",",$data);
        foreach ($conditionValue as $key => $value)
        {
            $fmt[$key] = explode("|",$value);
        }
        return $fmt;
    }
}
