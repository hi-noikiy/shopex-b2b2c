<?php

class systrade_data_cart {

    public $objects = array();

	/**
	 * user Id
	 *
	 * @var int
	 */
    protected $userId = null;

    public function __construct($userId)
    {
        if (!$userId) throw new \InvalidArgumentException('user id cannot null.');
        $this->userId = $userId;
        $this->gradeInfo = app::get('systrade')->rpcCall('user.grade.basicinfo', ['user_id'=>$userId]);
        $this->objMdlCart = app::get('systrade')->model('cart');
        $this->objLibItemInfo = kernel::single('sysitem_item_info');
        $this->__instance();
    }

    //初始化加入购物车的处理类
    private function __instance()
    {
        if( !$this->objects )
        {
            $objs = kernel::servicelist('cart_object_apps');
            foreach( $objs as $obj )
            {
                //购物车类型
                $type = $obj->getObjType();
                //排序
                $index = $obj->getCheckSort();
                if( isset($tmp[$index]) ) $index++;

                $tmpIndex[$index] = $type;
                $tmpObjects[$type] = $obj;
            }
            ksort($tmpIndex);
            foreach( $tmpIndex as $type )
            {
                $this->objects[$type] = $tmpObjects[$type];
            }
        }
        return $this->objects;
    }

    /**
     * @brief 检查是否可以购买
     *
     * @param array $params 加入购物车参数
     * @param array $itemData 加入购物车的基本商品数据
     * @param array $skuData 加入购物车的基本SKU数据
     *
     * @return bool
     */
    private function __check($checkParams, $itemData, $skuData)
    {
        foreach( $this->objects as $obj )
        {
            $obj->check($checkParams, $itemData, $skuData);
        }
    }

    /**
     * 检查加入购物车的商品是否有效
     *
     * @param array $itemsData 加入购物车的基本商品数据集合
     * @param array $skuData 加入购物车的基本SKU数据集合
     *
     * @return bool
     */
    private function __checkItemValid($itemsData, $skuData)
    {
        if( empty($itemsData) || empty($skuData) ) return false;

        //违规商品
        if( $itemsData['violation'] ) return false;

        //未启商品
        if( $itemsData['disabled'] ) return false;

        //未上架商品
        if($itemsData['approve_status'] != 'onsale' ) return false;

        //已删除SKU
        if( $skuData['status'] == 'delete' )
        {
            return false;
        }

        if( $skuData['store'] <= 0 )
        {
            return false;
        }

        return true;
    }

    /**
     * 如果加入购物车的商品在购物车中已存在，则进行合并
     *
     * @param $cartBasicData 根据加入购物车的的参数，获取到的基本的购物车数据
     * @param $params  加入购物车的的参数
     *
     * @return array
     */
    private function __mergeAddCartData($cartBasicData, $params)
    {
        //购买方式分为加入购物车模式，和立即购买模式
        //加入购物车模式需要判断购物车是否已经有该次购买的商品
        if( $cartBasicData && (empty($params['mode']) || $params['mode'] == 'cart') )
        {
            //总购买数量
            $params['totalQuantity'] = $params['quantity'];
            if( $params['obj_type'] && $cartBasicData['obj_type'] == $cartBasicData['obj_type'] )
            {
                $params['totalQuantity'] += intval($cartBasicData['quantity']);
                $params['cart_id'] = $cartBasicData['cart_id'];
            }
        }
        else
        {
            $params['totalQuantity'] = intval($params['quantity']);
        }

        return $params;
    }

    /**
     * @brief 加入购物车
     *
     * @param array $params 加入购物车参数
     *
     * @return bool
     */
    public function addCart($params)
    {
        if (!$this->objects[$params['obj_type']])
        {
            $msg = app::get('systrade')->_('商品类型错误！');
            throw new \LogicException($msg);
        }

        // 不同的cart  objects查询参数各自定义
        $filter = $this->objects[$params['obj_type']]->basicFilter($params);
        $cartBasicData = $this->getBasicCart($filter);
        // 合并购物车，再判断
        $mergeParams = $this->__mergeAddCartData($cartBasicData[0], $params);
        // 进行各自的特殊校验
        $this->objects[$params['obj_type']]->checkObject($mergeParams, $cartBasicData);
        // 判断合并后的购物车信息
        $this->__check($mergeParams);
        // 格式化加入购物车数据
        $data = $this->objects[$params['obj_type']]->__preAddCartData($mergeParams, $this->userId, $cartBasicData);

        if( $params['mode'] == 'fastbuy' )
        {
            return $this->fastBuyStore($data);
        }
        $db = app::get('systrade')->database();
        $db->beginTransaction();
        try
        {
            $result = $this->objMdlCart->save($data);
            // $this->objects[$params['obj_type']]->__afterSaveCart($data);
            $db->commit();
        }
        catch(\Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        return $result ? $data : false;
    }

    public function countCart()
    {
        $filter['user_ident'] = $this->objMdlCart->getUserIdentMd5($this->userId);
        $number = 0;
        $data = $this->objMdlCart->getList('quantity',$filter);
        $cartNumber['variety'] = count($data);
        foreach($data as $val)
        {
            $number += $val['quantity'];
        }
        $cartNumber['number'] = $number;
        return $cartNumber;
    }

    public function setCartCookieNum($cartNumber)
    {
        $cookie_path = kernel::base_url().'/';
        $expire = time()+315360000;
        setcookie('CARTNUMBER',$cartNumber,$expire,$cookie_path);
        return true;
    }

    /**
     * @brief 立即购买流程存储购物数据
     *
     * @param array $params
     *
     * @return bool
     */
    public function fastBuyStore($params)
    {
        $userIdent = $this->objMdlCart->getUserIdentMd5($this->userId);
        $params['is_checked'] = '1';

        $key = md5($userIdent.'cart_objects_fastbuy');

        cache::store('session')->put($key, $params, 3600);
        return true;
    }

    public function fastBuyFetch()
    {
        $userIdent = $this->objMdlCart->getUserIdentMd5($this->userId);
        $key = md5($userIdent.'cart_objects_fastbuy');
        return cache::store('session')->get($key);
    }

    /**
     * @brief 更新购物车信息
     *
     * @param array $params
     *
     * @return bool
     */
    public function updateCart($params)
    {
        if( $params['mode'] == 'fastbuy' ) return false;

        if( !$params['cart_id'] )
        {
            throw new \LogicException(app::get('systrade')->_("购物车发生变化，请刷新页面！"));
        }

        // 进行各自的特殊校验
        $filter['cart_id'] = $params['cart_id'];
        $filter['user_id'] = $this->userId;
        $basicCartData = $this->getBasicCart($filter);
        $params['obj_type'] = $basicCartData['0']['obj_type'];
        if( !$basicCartData )
        {
            throw new \LogicException(app::get('systrade')->_("购物车发生变化，请刷新页面！"));
        }
        //检查商品是否能加入购物车
        $this->objects[$params['obj_type']]->checkObject($params, $basicCartData);
        //这里是格式化购物车数据（__preAddCartData方法）
        $data = $this->objects[$params['obj_type']]->__preAddCartData($params, $this->userId, $basicCartData);

        $db = app::get('systrade')->database();
        $db->beginTransaction();
        try
        {
            $result = $this->objMdlCart->save($data);
            // $this->objects[$params['obj_type']]->__afterSaveCart($data);
            $db->commit();
        }
        catch(\Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        return $result;
    }

    /**
     * @brief 根据条件删除购物车信息，如果条件为空，则清空购物车
     *
     * @param array $params
     *
     * @return bool
     */
    public function removeCart($params, $mode='')
    {
        if($mode=='fastbuy')
        {
            $userIdent = $this->objMdlCart->getUserIdentMd5($this->userId);
            $key = md5($userIdent.'cart_objects_fastbuy');

            cache::store('session')->forever($key,null);
            return true;
        }

        if( $params )
        {
            $filter['cart_id'] = $params['cart_id'];
        }
        $filter['user_ident'] = $this->objMdlCart->getUserIdentMd5($this->userId);

        $result = $this->objMdlCart->delete($filter);

        $this->countCart();

        return $result;
    }

    /**
     * @brief 根据条件获取购物车信息
     *
     * @param array $filter
     *
     * @return array
     */
    public function getCartInfo($filter=array(), $needInvalid=true, $platform='pc')
    {
        $aCart = array();
        if(!$needInvalid) $filter['is_checked'] = '1';
        $cartData = $this->getBasicCart($filter);
        if( empty($cartData) ) return array();

        $result = $this->__preCartInfo($cartData, $needInvalid, $platform);

        $aCart['resultCartData']   = $result['resultCartData'];
        $aCart['totalCart']        = $result['totalCart'];
        $aCart['catItemPrice']     = $this->catItemPrice;

        $aCart['voucher']['usedVoucherCode']  = $this->usedVoucherCode;
        $aCart['voucher']['voucher_id']  = $this->usedVoucherId;
        if( empty($aCart['resultCartData']) ) $aCart = array();

        return $aCart;
    }

    /**
     * @brief 加载购物车显示数据结构
     *
     * @param array $data  加入购物车数据参数
     * @param array $shopNameArr 加入购物车商品的店铺名称集合
     * @param array $itemsData 加入购物车的基本商品数据集合
     * @param array $skusData 加入购物车的基本SKU数据集合
     *
     * @return array
     */
    private function __preCartInfo($cartData, $needInvalid, $platform)
    {
        $newCartData = [];
        $shopIds = [];
        $this->catItemPrice = [];
        foreach( $cartData as $row)
        {
            $shopIds[] = $row['shop_id'];
            $newCartData[$row['shop_id']][] = $row;
        }

        $shopIds = implode(',', array_unique($shopIds));
        $shopNameArr = app::get('systrade')->rpcCall('shop.get.list',array('shop_id'=>$shopIds,'fields'=>'shop_id,shop_type,shop_name'));
        $shopNameArr = array_bind_key($shopNameArr,'shop_id');

        foreach( $newCartData as $shopId=>$shopCartData )
        {
            //如果不存在则表示该店铺，已关闭，那么就不必要再查下该店铺的已加入购物车商品信息
            if( !$shopNameArr[$shopId] ) continue;
            $shopObjectData = $this->__preShopCartInfo($shopCartData, $needInvalid, $platform);

            if( $shopObjectData )
            {
                $resultCartData[$shopId]['shop_id'] = $shopId;
                $resultCartData[$shopId]['shop_name'] = $shopNameArr[$shopId]['shopname'];
                $resultCartData[$shopId]['shop_type'] = $shopNameArr[$shopId]['shop_type'];
                // 统计购物车的总数量，总价格，促销价格等综合信息
                $cartTotalInfo = $this->__cartTotal($shopObjectData, $shopId, $needInvalid, $platform);

                $resultCartData[$shopId]['cartCount'] = $cartTotalInfo['cartCount'];
                $resultCartData[$shopId]['basicPromotionListInfo'] = $cartTotalInfo['basicPromotionListInfo'];
                $resultCartData[$shopId]['usedCartPromotion'] = $cartTotalInfo['usedCartPromotion'];
                $resultCartData[$shopId]['usedCartPromotionWeight'] = $cartTotalInfo['usedCartPromotionWeight'];
                $resultCartData[$shopId]['cartByPromotion'] = $cartTotalInfo['cartByPromotion'];
                $resultCartData[$shopId]['cartByDlytmpl'] = $cartTotalInfo['cartByDlytmpl'];
                $resultCartData[$shopId]['object'] = $shopObjectData;
            }
        }

        // 应用购物券(结算页，购物车页不应用)
        $voucherDicountPrice = 0;
        if( !$needInvalid && $this->catItemPrice )
        {
            $voucherDicountPrice = $this->applyVoucher($this->catItemPrice, $platform, $resultCartData);
        }

        $return['resultCartData'] = $resultCartData;

        // 统计购物车所有勾选商品的总重量，总数量，总价格，总促销价格
        $totalWeight   = 0;
        $totalNumber   = 0;
        $totalPrice    = 0;
        $totalDiscount = 0;
        foreach($resultCartData as $v)
        {
            $totalWeight   += $v['cartCount']['total_weight'];
            $totalNumber   += $v['cartCount']['itemnum'];
            $totalPrice    += $v['cartCount']['total_fee'];
            $totalDiscount += $v['cartCount']['total_discount'];
        }
        $return['catItemPrice'] = $this->catItemPrice;
        $return['totalCart'] = array(
            'voucherDicountPrice'=> $voucherDicountPrice,
            'totalWeight'        => $totalWeight,
            'number'             => $totalNumber,
            'totalPrice'         => $totalPrice,
            'totalAfterDiscount' => ecmath::number_minus( array($totalPrice, $totalDiscount) ),
            'totalDiscount'      => $totalDiscount,
        );
        return $return;
    }

    /**
     * 但店铺的购物车的信息统计，及应用促销规则
     * 购物车的cartid根据促销进行分组，索引为0的为不应用促销规则的分组
     * @param  array &$shopObjectData 购物车元数据
     * @return array 应用促销规则后的购物车统计信息
     */
    private function __cartTotal(&$shopObjectData, $shop_id, $needInvalid, $platform)
    {
        // 统一购物车内所有商品关联的促销唯一标识，方便通过促销排列购物车商品
        $basicPromotionListInfo = array();
        $cartByPromotion = array(); //购物车id按照购物车级别促销进行分组，索引为0的代表是不应用促销规则的购物车id分组
        foreach ($shopObjectData as $basicCartInfo)
        {
            if( $basicCartInfo['promotions'] )
            {
                foreach($basicCartInfo['promotions'] as $k => $v)
                {
                    $basicPromotionListInfo[$k] = $v;
                }
            }
            // 根据促销分类购物车数据(包括所有勾选及未勾选的，只要选择了促销规则的商品都分组)
            if( $needInvalid || $basicCartInfo['valid'])
            {
                $cartByPromotion[$basicCartInfo['selected_promotion']]['cart_ids'][] = $basicCartInfo['cart_id'];
            }
        }

        // 应用促销规则，只包括购物车勾选了商品及有效的商品才应用
        $shop_discount_fee = 0; //店铺总的优惠金额
        $shop_promotion_totalWeight = 0; //用于免邮，符合促销的商品的总重量
        $usedCartPromotion = array(); //使用的促销集合
        foreach($cartByPromotion as $kPomotionId=>&$vInfo)
        {
            // 应用满减
            if($basicPromotionListInfo[$kPomotionId]['promotion_type'] == 'fullminus')
            {
                $fullminus_dicount_price = $this->applyFullminus($shopObjectData, $vInfo['cart_ids'], $basicPromotionListInfo[$kPomotionId]);
                $shop_discount_fee += $fullminus_dicount_price;
                $fullminus_dicount_price ? $usedCartPromotion[] = $kPomotionId : null;
                $fullminus_dicount_price ? $vInfo['discount_price'] = $fullminus_dicount_price : 2;
            }
            // 应用满折
            if($basicPromotionListInfo[$kPomotionId]['promotion_type'] == 'fulldiscount')
            {
                $fulldiscount_dicount_price = $this->applyFulldiscount($shopObjectData, $vInfo['cart_ids'], $basicPromotionListInfo[$kPomotionId]);
                $shop_discount_fee += $fulldiscount_dicount_price;
                $fulldiscount_dicount_price ? $usedCartPromotion[] = $kPomotionId : null;
                $fulldiscount_dicount_price ? $vInfo['discount_price'] = $fulldiscount_dicount_price : 0;
            }
            // 应用xy折扣
            if($basicPromotionListInfo[$kPomotionId]['promotion_type'] == 'xydiscount')
            {
                $xydiscount_dicount_price = $this->applyXydiscount($shopObjectData, $vInfo['cart_ids'], $basicPromotionListInfo[$kPomotionId]);
                $shop_discount_fee += $xydiscount_dicount_price;
                $xydiscount_dicount_price ? $usedCartPromotion[] = $kPomotionId : null;
                $xydiscount_dicount_price ? $vInfo['discount_price'] = $xydiscount_dicount_price : 0;
            }
        }

        // 优惠券(结算页，购物车页不应用)
        if(!$needInvalid && ($couponCode = $this->getCouponCode($shop_id)))
        {
            $coupon_dicount_price = $this->applyCoupon($shopObjectData, $couponCode);
            $shop_discount_fee += $coupon_dicount_price;
        }

        $cartCount = array();
        $total_fee = $itemnum = $total_weight = 0;

        //商品的一级类目ID
        $catIds = array_column($shopObjectData, 'cat_id');
        if( $catIds )
        {
            $catPath = app::get('systrade')->rpcCall('category.cat.get.info', ['cat_id'=>implode(',',$catIds),'fields'=>'cat_path']);
            foreach( $catPath as $catId => $catPathRow )
            {
                //一级类目Id
                $catLv1 = explode(',',$catPathRow['cat_path'])[1];
                $catPathData[$catId] = $catLv1;
            }
        }

        foreach ($shopObjectData as $k1=>$v1)
        {
            //统计购物车价格，数量，价格等
            if( $v1['valid'] && $v1['is_checked']=='1' )
            {
                $total_weight += $v1['weight'];
                $itemnum += $v1['quantity'];
                $total_fee += $v1['price']['total_price'];

                //统计一级类目商品的金额 用于购物券使用
                if( $catPathData[$v1['cat_id']] && !$needInvalid )
                {
                    $catLv1Id = $catPathData[$v1['cat_id']];
                    $this->catItemPrice[$catLv1Id][$shop_id]['price'] = ecmath::number_plus(array($this->catItemPrice[$catLv1Id][$shop_id]['price'], ecmath::number_minus(array($v1['price']['total_price'],$v1['price']['discount_price']))));

                    $shopObjectData[$k1]['cat_lv1_id'] = $catLv1Id;
                }
            }
        }

        // 计算运费需要的信息
        $itemByDlytmpl = [];
        foreach($shopObjectData as $kCartid=>$vCartinfo)
        {
            if( $vCartinfo['valid'] && $vCartinfo['is_checked']=='1' )
            {
                $itemByDlytmpl = array_merge($itemByDlytmpl,$this->objects[$vCartinfo['obj_type']]->getInfoForPost($vCartinfo));
            }
        }
        $cartByDlytmpl = [];
        foreach($itemByDlytmpl as $v)
        {
            $cartByDlytmpl[$v['dlytmpl_id']]['total_quantity'] += $v['total_quantity'];
            $cartByDlytmpl[$v['dlytmpl_id']]['total_weight']    = ecmath::number_plus( array($v['total_weight'],$cartByDlytmpl[$v['dlytmpl_id']]['total_weight']));
            $cartByDlytmpl[$v['dlytmpl_id']]['total_price']     = ecmath::number_plus( array($v['total_price'],$cartByDlytmpl[$v['dlytmpl_id']]['total_price']));
        }

        $return['basicPromotionListInfo'] = $basicPromotionListInfo;
        $return['usedCartPromotion'] = $usedCartPromotion;
        $return['usedCartPromotionWeight'] = $shop_promotion_totalWeight;
        $return['cartByPromotion'] = $cartByPromotion;
        $return['cartByDlytmpl'] = $cartByDlytmpl;
        $return['cartCount'] = array(
            'total_weight' => $total_weight,
            'itemnum' => $itemnum,
            'total_fee' => $total_fee,
            'total_discount' => $shop_discount_fee,
            'total_coupon_discount' => $coupon_dicount_price,
        );

        return $return;

    }

    /**
     * @brief 加载每个店铺的商品数据信息
     *
     * @param array $shopCartData 店铺中加入购物车数据的
     * @param array $itemsData 加入购物车的基本商品数据集合
     * @param array $skusData 加入购物车的基本SKU数据集合
     *
     * @return array
     */
    private function __preShopCartInfo($shopCartData, $needInvalid, $platform)
    {
        $itemIds = $skuIds = $itemsku = [];
        foreach( $shopCartData as $row)
        {
            if($row['obj_type']=='item')
            {
                $itemIds[] = $row['item_id'];
                $skuIds[]  = $row['sku_id'];
                $itemsku[] = ['item_id'=>$row['item_id'], 'sku_id'=>$row['sku_id'],'user_id'=>$row['user_id'],'quantity' => intval($row['quantity'])];
            }
        }
        if($itemIds && $skuIds)
        {
            $itemRows = 'item_id,cat_id,title,weight,image_default_id,sub_stock,violation,disabled,dlytmpl_id';
            $skuRows = 'sku_id,bn,item_id,spec_info,price,weight,status';
            $itemFields['status'] = 'approve_status';
            // $itemFields['promotion'] = 'promotion';

            $itemsData = $this->objLibItemInfo->getItemList($itemIds, $itemRows, $itemFields);
            $skusData = $this->objLibItemInfo->getSkusList($skuIds,$skuRows);

            // 批量获取操作，减少SQL查询
            $skuPromotionList = $this->getItemPromotionList($itemsku, $platform);
            $itemActivityList = $this->getItemActivityList($itemsku);
            $itemGiftList = $this->getItemGiftList($itemsku);
        }

        //现在只有普通商品购买流程，因此临时将商品结构写到此
        //如果有其他商品购买类型，则到各类型中进行商品获取
        foreach( $shopCartData as $row )
        {
            $k = $row['cart_id'];
            $row['grade_id'] = $this->gradeInfo['grade_id']; //会员等级id
            $shopObjectData[$k] = $this->objects[$row['obj_type']]->processCartObject($row,$itemsData,$skusData,$itemActivityList);

            $itemId = $row['item_id'];

            // 获取商品关联的促销信息
            if($row['obj_type']=='item')
            {
                // 原写法
                // $shopObjectData[$k]['promotions'] = $this->getItemPromotionInfo($itemId, $row['sku_id'], $platform);
                // 新写法，减少SQL数量
                $shopObjectData[$k]['promotions'] = $skuPromotionList[$row['sku_id']];

                // 根据促销的创建时间进行倒序排序，则最新的默认选为商品的促销规则
                if($shopObjectData[$k]['promotions'])
                {
                    // 取商品对应的最后（最新）一个促销(已排序)，默认促销就用这个
                    $latestPromotion = end($shopObjectData[$k]['promotions']);
                    $priorityPromotionId = $latestPromotion['promotion_id'];
                    if($priorityPromotionId && !$row['selected_promotion'] && $row['selected_promotion']!=='0' )
                    {
                        $shopObjectData[$k]['selected_promotion'] = $priorityPromotionId;
                    }
                }

                //或商品享受的赠品信息
                if($itemGiftList[$row['item_id']])
                {
                    $shopObjectData[$k]['gift'] = $this->getItemGiftInfo($itemGiftList[$row['item_id']], $row['sku_id'], $shopObjectData[$k]['quantity']);
                }
            }
            if( !$needInvalid && (!$shopObjectData[$k]['valid'] || !$row['is_checked']) )
            {
                unset($shopObjectData[$k]);
            }
        }

        return $shopObjectData;
    }

    /**
     * 根据商品返回其相关的促销信息
     * @param  int $itemId 商品id
     * @return array         促销信息数组
     */
    public function getItemPromotionList($itemsku, $platform='pc')
    {
        $itemIds = array_column($itemsku, 'item_id');
        $itemPromotionIdList = app::get('systrade')->rpcCall('item.promotion.list', ['item_ids'=>implode(',', $itemIds)]);
        if(!$itemPromotionIdList) return [];

        $promotionsIds = array_column($itemPromotionIdList, 'promotion_id');
        $promotions = app::get('systrade')->rpcCall('promotion.promotion.list.tag', ['promotion_id'=>implode(',', $promotionsIds), 'platform'=>$platform]);

        $tmp = [];
        foreach ($itemPromotionIdList as $v1)
        {
            $tmp[$v1['item_id']][] = $v1;
        }
        $skuRefPro = [];
        foreach ($itemsku as $v2)
        {
            if( !$tmp[$v2['item_id']] ) continue;
            foreach ($tmp[$v2['item_id']] as $v3)
            {
                $skuids = explode(',', $v3['sku_id']);
                if($v2['item_id'] == $v3['item_id'] )
                {
                    if($promotions[$v3['promotion_id']])
                    {
                        $promotions[$v3['promotion_id']]['valid'] = 1;
                        $skuRefPro[$v2['sku_id']][$v3['promotion_id']] = $promotions[$v3['promotion_id']];
                    }
                }else{
                    continue;
                }
            }
        }
        return $skuRefPro;
    }

    /**
     * 组织商品关联的活动信息，批量，减少SQL查询
     * @param  array $itemsku item_id和sku_id组成的数组
     * @return array  返回组织好的活动和促销关联相关信息，用于活动的价格的修改和限购的数量的修改
     */
    private function getItemActivityList($itemsku)
    {

        foreach($itemsku as $value)
        {
            $cartItemSku[$value['item_id']][] = $value['sku_id'];
            $userId[] = $value['user_id'];
            $cartItemNum[$value['item_id']][$value['sku_id']] = intval($value['quantity']);
        }

        $params = array(
            'item_id'       => implode(',', array_keys($cartItemSku)),
            'status'        => 'agree',
            'end_time'      => 'bthan',
            'start_time'    => 'sthan',
            'verify_status' => 'agree',
            'page_no'       => 1,
            'page_size'     => 100,
            'order_by'      => 'id Desc',
            // 'fields'        => 'activity_id,item_id,activity_price,activity_tag',
            'fields'        => '*',
        );
        // 获取商品的正在进行的活动,一对一的
        $activityItemList = app::get('systrade')->rpcCall('promotion.activity.item.list', $params);
        if(!$activityItemList['list']) return [];

        $activityIds = array_column($activityItemList['list'], 'activity_id');
        $params = array(
            'activity_id' => implode(',', $activityIds),
            'order_by' => 'mainpush desc',
            'fields' => '*',
        );
        // 根据活动id获取活动详情的列表
        $activityList = app::get('systrade')->rpcCall('promotion.activity.list', $params);
        if(!$activityList['data']) return [];

        $activityList = array_bind_key($activityList['data'], 'activity_id');

        $objMdlPromDetail = app::get('systrade')->model('promotion_detail');
        $objMdlOrder = app::get('systrade')->model('order');
        $tmp = [];
        foreach($activityItemList['list'] as $v)
        {
            $cartSkuId = $cartItemSku[$v['item_id']];
            foreach($cartSkuId as $skuId){
                if($v['sku_activity_price'] && !in_array($skuId,array_keys($v['sku_activity_price'])))
                {
                    continue;
                }

                //活动限制的购买件数
                $buyLimit = $activityList[$v['activity_id']]['buy_limit'];

                //参加活动的sku加入购物车的数量
                $cartItemAddNum = $cartItemNum[$v['item_id']][$skuId];

                if($cartItemAddNum > $buyLimit)
                {
                    continue;
                }

                $tmp[$v['item_id']][$skuId]['activityInfo'] = $v;
                $tmp[$v['item_id']][$skuId]['activityInfo']['status'] = 1;
                $tmp[$v['item_id']][$skuId]['activityInfo']['activity_info'] = $activityList[$v['activity_id']];

                $filter = array(
                    'promotion_id'   => $v['activity_id'],
                    'promotion_type' => 'activity',
                    'user_id'        => $userId,
                    'item_id'        => $v['item_id'],
                    'sku_id'         => $skuId,
                );

                $oids = $objMdlPromDetail->getList('oid,item_id,user_id,sku_id', $filter);
                if($oids) {
                    $oids = array_column($oids, 'oid');
                    $dbquery = app::get('systrade')->database()->createQueryBuilder();
                    $dbquery->select('sum(num) AS buyed_num')
                       ->from('systrade_order')
                       ->andwhere('status <> "TRADE_CLOSED_BY_SYSTEM"')
                       ->andwhere('status <> "TRADE_CLOSED"')
                       ->where('oid IN('.implode(',', $oids).')');
                    $buyednum = $dbquery->execute()->fetch();

                    //已参加活动的件数
                    $cartItemAddNum = $buyLimit - $buyednum['buyed_num'];
                    if($cartItemAddNum > $restActivityNum || $cartItemAddNum <= 0)
                    {
                        unset($tmp[$v['item_id']][$skuId]);
                    }
                }
            }
        }
        return $tmp;
    }

    /**
     * 根据商品返回其相关的促销信息(废弃)
     * @param  int $itemId 商品id
     * @return array         促销信息数组
     */
    public function getItemPromotionInfo($itemId, $skuId, $platform='pc')
    {
        $itemPromotionTagInfo = app::get('systrade')->rpcCall('item.promotion.get', array('item_id'=>$itemId,'sku_id'=>$skuId));
        if(!$itemPromotionTagInfo)
        {
            return false;
        }
        $allPromotion = array();
        foreach($itemPromotionTagInfo as $v)
        {
            $basicPromotionInfo = app::get('systrade')->rpcCall('promotion.promotion.get', array('promotion_id'=>$v['promotion_id'], 'platform'=>$platform), 'buyer');
            if($basicPromotionInfo['valid']===true)
            {
                $allPromotion[$v['promotion_id']] = $basicPromotionInfo;
            }
        }
        // 倒序排序，购物车的默认促销规则选择最新添加的促销适用
        ksort($allPromotion);
        return $allPromotion;
    }

    /**
     * 根据商品返回其单品促销活动，如团购价
     * @param  int $itemId 商品id
     * @return array         活动信息数组
     */
    public function getItemActivityInfo($itemId, $platform='pc')
    {
        $promotionDetail = app::get('systrade')->rpcCall('promotion.activity.item.info',array('item_id'=>$itemId, 'platform'=>$platform, 'valid'=>1), 'buyer');
        if(!$promotionDetail)
        {
            return false;
        }
        return $promotionDetail;
    }

    /**
     * 应用满减促销
     * @param  array &$shopObjectData 购物车商品数据
     * @param  array $vCartIds        本促销对应购物车id
     * @param  array $promotionDetail 本促销的详情
     * @return float                  返回折扣金额
     */
    private function applyFullminus(&$shopObjectData, $vCartIds, $promotionDetail)
    {
        $forPromotionTotalPrice = 0; // 对应促销商品的总价
        foreach ($vCartIds as $cartId)
        {
            if($shopObjectData[$cartId]['valid'] && $shopObjectData[$cartId]['is_checked']=='1')
            {
                $forPromotionTotalPrice = ecmath::number_plus( array($forPromotionTotalPrice, $shopObjectData[$cartId]['price']['total_price']) );
            }
        }

        $applyData = array(
            'user_id' => $this->userId,
            'grade_id' => $this->gradeInfo['grade_id'],
            'promotion_id' => $promotionDetail['promotion_id'],
            'fullminus_id' => $promotionDetail['rel_promotion_id'],
            'forPromotionTotalPrice' => $forPromotionTotalPrice,
        );
        $discount_price = app::get('systrade')->rpcCall('promotion.fullminus.apply', $applyData, 'buyer');
        if($discount_price>0)
        {
            // 优惠分摊
            foreach ($vCartIds as $cartId)
            {
                if($shopObjectData[$cartId]['valid'] && $shopObjectData[$cartId]['is_checked']=='1')
                {
                    $percent = ecmath::number_div(array($shopObjectData[$cartId]['price']['total_price'], $forPromotionTotalPrice) );
                    $divide_order_price = ecmath::number_multiple(array($discount_price, $percent) );
                    $shopObjectData[$cartId]['price']['discount_price'] = ecmath::number_plus( array( $shopObjectData[$cartId]['price']['discount_price'], $divide_order_price) );
                    $shopObjectData[$cartId]['promotion_type'] = $promotionDetail['promotion_type'];
                    $shopObjectData[$cartId]['promotion_id'] = $promotionDetail['promotion_id'];
                }
            }
        }
        return $discount_price;
    }

    /**
     * 应用满折促销
     * @param  array &$shopObjectData 购物车商品数据
     * @param  array $vCartIds        本促销对应购物车id
     * @param  array $promotionDetail 本促销的详情
     * @return float                  返回折扣金额
     */
    private function applyFulldiscount(&$shopObjectData, $vCartIds, $promotionDetail)
    {
        $forPromotionTotalPrice = 0; // 对应促销商品的总价
        foreach ($vCartIds as $cartId)
        {
            if($shopObjectData[$cartId]['valid'] && $shopObjectData[$cartId]['is_checked']=='1')
            {
                $forPromotionTotalPrice = ecmath::number_plus( array($forPromotionTotalPrice, $shopObjectData[$cartId]['price']['total_price']) );
            }
        }

        $applyData = array(
            'user_id' => $this->userId,
            'grade_id' => $this->gradeInfo['grade_id'],
            'promotion_id' => $promotionDetail['promotion_id'],
            'fulldiscount_id' => $promotionDetail['rel_promotion_id'],
            'forPromotionTotalPrice' => $forPromotionTotalPrice,

        );
        $discount_price = app::get('systrade')->rpcCall('promotion.fulldiscount.apply', $applyData, 'buyer');
        if($discount_price>0)
        {
            // 优惠分摊
            foreach ($vCartIds as $cartId)
            {
                if($shopObjectData[$cartId]['valid'] && $shopObjectData[$cartId]['is_checked']=='1')
                {
                    $percent = ecmath::number_div(array($shopObjectData[$cartId]['price']['total_price'], $forPromotionTotalPrice) );
                    $divide_order_price = ecmath::number_multiple(array($discount_price, $percent) );
                    $shopObjectData[$cartId]['price']['discount_price'] = ecmath::number_plus( array( $shopObjectData[$cartId]['price']['discount_price'], $divide_order_price) );
                    $shopObjectData[$cartId]['promotion_type'] = $promotionDetail['promotion_type'];
                    $shopObjectData[$cartId]['promotion_id'] = $promotionDetail['promotion_id'];
                }
            }
        }
        return $discount_price;
    }

    /**
     * 应用XY折促销
     * @param  array &$shopObjectData 购物车商品数据
     * @param  array $vCartIds        本促销对应购物车id
     * @param  array $promotionDetail 本促销的详情
     * @return float                  返回折扣金额
     */
    private function applyXydiscount(&$shopObjectData, $vCartIds, $promotionDetail)
    {
        $forPromotionTotalPrice = 0; // 对应促销商品的总价
        $forPromotionTotalQuantity = 0; // 对应促销商品的总数量
        foreach ($vCartIds as $cartId)
        {
            if($shopObjectData[$cartId]['valid'] && $shopObjectData[$cartId]['is_checked']=='1')
            {
                $forPromotionTotalPrice = ecmath::number_plus( array($forPromotionTotalPrice, $shopObjectData[$cartId]['price']['total_price']) );
                $forPromotionTotalQuantity = ecmath::number_plus( array($forPromotionTotalQuantity, $shopObjectData[$cartId]['quantity']) );
            }
        }

        $applyData = array(
            'user_id' => $this->userId,
            'grade_id' => $this->gradeInfo['grade_id'],
            'promotion_id' => $promotionDetail['promotion_id'],
            'xydiscount_id' => $promotionDetail['rel_promotion_id'],
            'forPromotionTotalPrice' => $forPromotionTotalPrice,
            'forPromotionTotalQuantity' => $forPromotionTotalQuantity,

        );
        $discount_price = app::get('systrade')->rpcCall('promotion.xydiscount.apply', $applyData, 'buyer');
        if($discount_price>0)
        {
            // 优惠分摊
            foreach ($vCartIds as $cartId)
            {
                if($shopObjectData[$cartId]['valid'] && $shopObjectData[$cartId]['is_checked']=='1')
                {
                    $percent = ecmath::number_div(array($shopObjectData[$cartId]['price']['total_price'], $forPromotionTotalPrice) );
                    $divide_order_price = ecmath::number_multiple(array($discount_price, $percent) );
                    $shopObjectData[$cartId]['price']['discount_price'] = ecmath::number_plus( array( $shopObjectData[$cartId]['price']['discount_price'], $divide_order_price) );
                    $shopObjectData[$cartId]['promotion_type'] = $promotionDetail['promotion_type'];
                    $shopObjectData[$cartId]['promotion_id'] = $promotionDetail['promotion_id'];
                }
            }
        }
        return $discount_price;
    }

    private function applyVoucher($catItemPrice, $platform, &$resultCartData)
    {
        try
        {
            $voucherInfo = kernel::single('systrade_cart_voucher')->getUseVoucher($this->userId, $platform);
            if( !$voucherInfo ) return 0;
        }
        catch( Exception $e )
        {
            kernel::single('systrade_cart_voucher')->cancelVoucherCart($this->userId, $platform);
            return 0;
        }

        //判断使用条件
        $limitCat = explode(',', $voucherInfo['limit_cat']);//限制类目
        $limitMoney = $voucherInfo['limit_money'];//限制金额
        $deductMoney = 0;
        $$voucherTotalPrice = 0;
        foreach( $catItemPrice as $lv1CatId=>$row )
        {
            if( !in_array($lv1CatId, $limitCat)  )
            {
                continue;
            }

            foreach( $row as $shopId => $shopPriceTotal )
            {
                $params = [
                    'shop_id'   =>$shopId,
                    'voucher_id'=>$voucherInfo['voucher_id'],
                    'fields'    => 'verify_status,valid_status,cat_id',
                ];
                $voucherRegisterInfo = app::get('topc')->rpcCall('promotion.voucher.register.get', $params);
                if( !$voucherRegisterInfo || $voucherRegisterInfo['verify_status'] != 'agree' || $voucherRegisterInfo['valid_status'] !=1 )
                {
                    continue;
                }

                $shopCatId = explode(',', $voucherRegisterInfo['cat_id']);
                if( in_array($lv1CatId, $shopCatId)  )
                {
                    $voucherShop[$shopId]['shop_limit_cat_id'] = $shopCatId;
                    //使用购物券的商品金额
                    $voucherTotalPrice = ecmath::number_plus(array($voucherTotalPrice, $shopPriceTotal['price']));
                }
            }
        }

        if( $voucherTotalPrice >= $limitMoney  )
        {
            //购物券金额
            $deductMoney = $voucherInfo['deduct_money'];
        }
        else
        {
            return 0;
        }

        $shopCartData = [];
        foreach( $resultCartData as $shopId=>&$shopCartData )
        {
            $shopVoucherDiscount = 0;
            foreach( $shopCartData['object'] as $itemId=>&$itemData )
            {
                //当前商品类目在购物券限制类目中 并且店铺参加
                if( $voucherShop[$shopId] && in_array($itemData['cat_lv1_id'],$voucherShop[$shopId]['shop_limit_cat_id']) )
                {
                    $itemPrice = ecmath::number_minus([$itemData['price']['total_price'], $itemData['price']['discount_price']]);

                    //商品金额占总金额比例
                    $percent = ecmath::number_div(array($itemPrice, $voucherTotalPrice) );
                    $divide_order_price = ecmath::number_multiple(array($deductMoney, $percent) );
                    $itemData['price']['discount_price'] = ecmath::number_plus( array($itemData['price']['discount_price'], $divide_order_price) );
                    $itemData['price']['voucher_discount'] = $divide_order_price;
                    $itemData['voucher_subsidy_proportion'] = $voucherInfo['subsidy_proportion'];

                    $shopVoucherDiscount = ecmath::number_plus([$shopVoucherDiscount, $divide_order_price]);
                }
            }
            $shopCartData['cartCount']['total_discount'] = floatval(ecmath::number_plus([$shopCartData['cartCount']['total_discount'], $shopVoucherDiscount]));
            $this->usedVoucherCode = $voucherInfo['voucher_code'];
            $this->usedVoucherId = $voucherInfo['voucher_id'];
        }

        return $deductMoney;
    }

    /**
     * 应用免邮促销
     * @param  array $shopObjectData  购物车商品数据
     * @param  array $couponCode      优惠券码
     * @return float                  返回应用优惠券的金额
     */
    private function applyCoupon(&$shopObjectData, $couponCode)
    {
        $itemWithTotalPriceArr = array();
        foreach ($shopObjectData as $k1=>$v1)
        {
            //统计购物车价格，数量，价格等
            if( $v1['valid'] && $v1['is_checked']=='1' )
            {
                $itemWithTotalPriceArr[$k1] = $v1['item_id'].'_'.$v1['price']['total_price'];
            }
        }

        if( !$itemWithTotalPriceArr ) return 0;

        // 应用优惠券
        $apiParams = array(
            'coupon_code' => $couponCode,
            'cartItemsInfo' => implode('|', $itemWithTotalPriceArr),
            'user_id' => $this->userId,
        );

        $couponDiscountData = app::get('systrade')->rpcCall('promotion.coupon.apply', $apiParams,'buyer');
        if( $couponDiscountData['deduct_money'] )
        {
            //统计使用优惠券的商品总金额
            $forPromotionTotalPrice = 0;
            foreach( $itemWithTotalPriceArr as $cartId=>$itemsInfo )
            {
                $itemInfoArr = explode('_', $itemsInfo);
                $itemId = $itemInfoArr[0];
                if( in_array($itemId,$couponDiscountData['use_item_id']) )
                {
                    $forPromotionTotalPrice = ecmath::number_plus( array($forPromotionTotalPrice, $itemInfoArr[1]) );
                }
            }

            //优惠分摊
            foreach( $itemWithTotalPriceArr as $cartId=>$itemsInfo )
            {
                $itemInfoArr = explode('_', $itemsInfo);
                $itemId = $itemInfoArr[0];
                if( in_array($itemId,$couponDiscountData['use_item_id']) )
                {
                    $totalPrice = $itemInfoArr[1];
                    $percent = ecmath::number_div(array($totalPrice, $forPromotionTotalPrice) );
                    $divide_order_price = ecmath::number_multiple(array($couponDiscountData['deduct_money'], $percent) );
                    $shopObjectData[$cartId]['price']['discount_price'] = ecmath::number_plus( array( $shopObjectData[$cartId]['price']['discount_price'], $divide_order_price) );
                }
            }

            return $couponDiscountData['deduct_money'];
        }
        else
        {
            return 0;
        }
    }

    /**
     * @brief 根据条件查询到基本的购物车数据
     *
     * @param array $filter
     *
     * @return array
     */
    public function getBasicCart($filter)
    {
        $filter['user_id'] = $this->userId;

        if( empty($filter['mode']) || $filter['mode'] == 'cart' )
        {
            unset($filter['mode']);
            $params = $this->objMdlCart->getList('*', $filter);
        }
        else
        {
            $data = $this->fastBuyFetch();

            if( $data )
            {
                $params[0] = $data;
            }
        }
        return $params;
    }

    // 获取结算页使用的某店铺优惠券
    public function getCouponCode($shop_id)
    {
        return kernel::single('systrade_cart_coupon_redis')->get($this->userId, $shop_id);
    }

    /**
     * @brief 选择的优惠券放入购物车优惠券表
     *
     * @param array $data
     *
     * @return array
     */
    public function addCouponCart($coupon_code, $shop_id)
    {
        return kernel::single('systrade_cart_coupon_redis')->set($this->userId, $shop_id, $coupon_code);
    }

    /**
     * @brief 取消优惠券
     *
     * @param array $data
     *
     * @return array
     */
    public function cancelCouponCart($coupon_code, $shop_id)
    {
        if($coupon_code == '-1')
        {
            return kernel::single('systrade_cart_coupon_redis')->del($this->userId, $shop_id);
        }
        else
        {
            return false;
        }

    }

    // 处理商品的赠品的条件等
    private function getItemGiftInfo($giftItemInfo, $skuId, $quantity)
    {
        if(!$giftItemInfo) return [];
        if( $giftItemInfo['sku_ids'] )
        {
            $giftItemInfoSkuIds = explode(',', $giftItemInfo['sku_ids']);
            if( !in_array($skuId, $giftItemInfoSkuIds) )
            {
                return array();
            }
        }

        if($giftItemInfo && $quantity >= $giftItemInfo['limit_quantity'])
        {
            $skuIds = array_column($giftItemInfo['gift_item'],'sku_id');
            $skuRows = "sku_id";
            $skusData = $this->objLibItemInfo->getSkusList($skuIds,$skuRows);
            $quotient =floor($quantity/$giftItemInfo['limit_quantity']);
            foreach($giftItemInfo['gift_item'] as $key=>&$value)
            {
                if($value['approve_status'] == "instock")
                {
                    unset($giftItemInfo['gift_item'][$key]);
                    continue;
                }

                //当赠品库存量少于赠与量时，最后赠予量为最大库存
                $value['gift_num'] = $value['gift_num']*$quotient;
                if($value['gift_num'] > $skusData[$value['sku_id']]['realStore'])
                {
                    $value['gift_num'] = $skusData[$value['sku_id']]['realStore'];
                }

                $value['realStore'] = $skusData[$value['sku_id']]['realStore'];
            }

            $valid_grade = explode(',', $giftItemInfo['valid_grade']);
            // $gradeInfo = app::get('syspromotion')->rpcCall('user.grade.basicinfo');
            if( !in_array($this->gradeInfo['grade_id'], $valid_grade) )
            {
                return array();
            }

            return $giftItemInfo;
        }
        return array();
    }

    // 批量获取商品关联的赠品信息
    private function getItemGiftList($itemsku)
    {
        $itemIds = implode(',', array_column($itemsku, 'item_id'));
        $giftItemList = app::get('systrade')->rpcCall('promotion.gift.item.info',array('item_id'=>$itemIds, 'valid'=>1));
        $giftItemList = array_bind_key($giftItemList, 'item_id');
        $itemWithGiftList = [];
        foreach ($itemsku as $v)
        {
            if(!$giftItemList[$v['item_id']]) continue;
            $itemWithGiftList[$v['item_id']] = $giftItemList[$v['item_id']];
        }

        return $itemWithGiftList;
    }

}

