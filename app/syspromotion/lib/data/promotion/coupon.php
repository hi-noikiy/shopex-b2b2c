<?php

class syspromotion_data_promotion_coupon extends syspromotion_abstract_promotions {

    /**
     * 促销标签
     */
    public function getPromotionTag()
    {
        return '优惠券';
    }

    /**
     * 促销类型
     */
    public function getPromotionType()
    {
        return 'coupon';
    }

    /**
    |*促销状态字段名称
     */
    public function getPromotionStatusCol()
    {
        return 'coupon_status';
    }

    /**
    |*促销生效开始时间字段名称
     */
    public function getPromotionStartTimeCol()
    {
        return 'canuse_start_time';
    }

    //获取促销规则的model
    public function getMdlPromotion()
    {
        return app::get('syspromotion')->model('coupon');
    }

    /**
     * 获取促销规则关联商品表的model
     */
    public function getMdlPromotionItem()
    {
        return app::get('syspromotion')->model('coupon_item');
    }

    /**
     * 保存优惠券
     */
    public function save()
    {
        $data = $this->promotionData;
        $this->getMdlPromotion()->save($data);

        if( $data['coupon_id'] )
        {
            $this->__saveCouponItem($data['coupon_id']);
        }

        return $data['coupon_id'];
    }

    /**
     * 保存满减促销关联的商品信息
     */
    private function __saveCouponItem($couponId)
    {
        $this->getMdlPromotionItem()->delete(array('coupon_id'=>$couponId));
        foreach($this->promotionData['coupon_rel_item'] as $itemRow )
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

            $couponRelationItem = array(
                'coupon_id' => $couponId,
                'item_id' => $itemid,
                'sku_ids' => $skuIds ? implode(',', $skuIds) : '',
                'shop_id' =>$this->getShopId(),
                'promotion_tag' =>$this->getPromotionTag(),
                'leaf_cat_id' => $this->promotionRelItems[$itemid]['cat_id'],
                'brand_id' => $this->promotionRelItems[$itemid]['brand_id'],
                'title' => $this->promotionRelItems[$itemid]['title'],
                'price' => $this->promotionRelItems[$itemid]['price'],
                'image_default_id' => $this->promotionRelItems[$itemid]['image_default_id'],
                'canuse_start_time' => $this->promotionData['canuse_start_time'],
                'canuse_end_time' => $this->promotionData['canuse_end_time'],
            );
            $this->getMdlPromotionItem()->save($couponRelationItem);
        }
        return true;
    }

    /**
     * 格式化促销关联商品数据
     */
    public function formatPromotoinRelItem()
    {
        $itemIds = array_column($this->promotionData['coupon_rel_item'], 'item_id');
        $this->promotionRelItems = $this->_getItemByItemId($itemIds);

        return $this;
    }

    /**
     * 格式化促销的条件
     */
    public function formatPromotionCondition()
    {
        if( !$this->promotionData['coupon_id'] )
        {
            $this->promotionData['coupon_prefix'] = $this->__makePrefixKey();
        }

        $this->promotionData['coupon_key'] = substr( base64_encode(serialize($this->promotionData)), rand(0,10),10 );

        return $this;
    }

    private function __makePrefixKey($length=4, $prefixFlag='B')
    {
        $returnStr='';
        $pattern = '1234567890ABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        for($i = 0; $i < $length; $i ++)
        {
            $returnStr .= $pattern {mt_rand ( 0, 35 )};
        }
        return $prefixFlag.$returnStr;
    }

    /**
     * 校验添加促销规则数据
     *
     * @param $data api提交过来的数据
     */
    public function checkAddPromotionData($data)
    {
        //校验促销有效期时间
        $this->checkSavePromotionExpire($data['canuse_start_time'], $data['canuse_end_time']);

        if( $data['deduct_money'] >= $data['limit_money'] )
        {
            throw new \LogicException('满足条件金额必须大于优惠金额!');
        }

        if( $data['max_gen_quantity'] < $data['userlimit_quantity'])
        {
            throw new \LogicException('优惠券用户可领取数量不能大于优惠券生成总量！');
        }

        if( $data['cansend_start_time'] >= $data['cansend_end_time'] )
        {
            throw new \LogicException('优惠券可领取的开始时间不能大于等于优惠券可领取的结束时间！');
        }

        if( $data['cansend_start_time'] >= $data['canuse_start_time'] )
        {
            throw new \LogicException('优惠券可领取的开始时间不能大于等于优惠券的生效时间！ ');
        }

        if( $data['cansend_end_time'] > $data['canuse_end_time'] )
        {
            throw new \LogicException('优惠券可领取的结束时间不能小于优惠券的生效结束时间！');
        }

        return $this;
    }

    // 生成优惠券号码
    public function _makeCouponCode($params, $genQuantity)
    {
        $couponInfo = app::get('syspromotion')->rpcCall('promotion.coupon.get', array('coupon_id'=>$params['coupon_id']));

        if(!$couponInfo)
        {
            throw new \LogicException('无此优惠券！');
        }
        if($couponInfo['cansend_start_time'] > time())
        {
            throw new \LogicException('优惠券领取时间尚未开始，不能领取！');
        }
        if($couponInfo['cansend_end_time'] < time())
        {
            throw new \LogicException('优惠券领取时间已过，不能领取！');
        }
        if($couponInfo['canuse_end_time'] < time())
        {
            throw new \LogicException('优惠券已过期，无法领取！');
        }
        // 已领优惠券和总领次数顺序不要颠倒
        if( ecmath::number_plus(array(intval($genQuantity), $couponInfo['send_couponcode_quantity']) ) > $couponInfo['max_gen_quantity'] )
        {
            throw new \LogicException('优惠券已经领完！');
        }
        if($couponInfo['userlimit_quantity'] <= $params['old_quantity'])
        {
            throw new \LogicException('您的领用次数已过！');
        }
        $valid_grade = explode(',', $couponInfo['valid_grade']);
        if(!in_array($params['grade_id'], $valid_grade))
        {
            throw new \LogicException('您的会员等级不可以领取此优惠券！');
        }

        $prefix = $couponInfo['coupon_prefix'];
        $key = $couponInfo['coupon_key'];
        $iNo = bcadd(intval($genQuantity),$couponInfo['send_couponcode_quantity'],0);
        $coupon_code_count_len = 5;
        $coupon_code_encrypt_len = 5;
        if ($coupon_code_count_len >= strlen(strval($iNo)))
        {
            $iNo = str_pad($this->dec2b36($iNo), $coupon_code_count_len, '0', STR_PAD_LEFT);
            $checkCode = md5($key.$iNo.$prefix);
            $checkCode = strtoupper(substr($checkCode, 0, $coupon_code_encrypt_len));
            $memberCouponCode = $couponInfo['coupon_code']= $prefix.$checkCode.$iNo;

            $db = app::get('syspromotion')->model('coupon')->database();
            $sqlStr = "UPDATE syspromotion_coupon SET send_couponcode_quantity=ifnull(send_couponcode_quantity,0)+? WHERE coupon_id=? ";
            if ($db->executeUpdate($sqlStr, [$genQuantity, $params['coupon_id']]))
            {
                return $couponInfo;
            }
            else
            {
                return false;
            }
        }
        else
        {
            throw new \LogicException('优惠券已领完！');
            return false;
        }
    }

    private function dec2b36($int)
    {
        $b36 = array(0=>"0",1=>"1",2=>"2",3=>"3",4=>"4",5=>"5",6=>"6",7=>"7",8=>"8",9=>"9",10=>"A",11=>"B",12=>"C",13=>"D",14=>"E",15=>"F",16=>"G",17=>"H",18=>"I",19=>"J",20=>"K",21=>"L",22=>"M",23=>"N",24=>"O",25=>"P",26=>"Q",27=>"R",28=>"S",29=>"T",30=>"U",31=>"V",32=>"W",33=>"X",34=>"Y",35=>"Z");
        $retstr = "";
        if($int>0)
        {
            while($int>0)
            {
                $retstr = $b36[($int % 36)].$retstr;
                $int = floor($int/36);
            }
        }
        else
        {
            $retstr = "0";
        }

        return $retstr;
    }

    public function expireCoupon()
    {
        $filter['canuse_end_time|sthan'] = time();
        $coupon = app::get('syspromotion')->model('coupon')->getList('coupon_id', $filter);
        $couponIds = array_column($coupon,'coupon_id');
        if(!$couponIds)return false;
        return  app::get('syspromotion')->rpcCall('user.coupon.expire',['coupon_id'=>implode(',',$couponIds)]);
    }
}
