<?php

/**
 * @brief 商品数据处理
 */
class sysitem_data_item {

    /**
     * @brief 商品上下架
     * @author ajx
     * @param $params array item_ids
     * @param $status string onsale(上架、出售中) instock(下架、库中)
     * @param $msg string 处理结果
     *
     * @return bool
     */
    public function batchCloseItem($params,$status,&$msg)
    {
        if($params['item_id'][0] == '_ALL_')  unset($params);
        $ojbMdlItem = app::get('sysitem')->model('item_status');

        $updata['approve_status'] = $status;
        $updata['delist_time'] = time();

        $result = $ojbMdlItem->update($updata,$params);
        if($result)
        {
            $msg = app::get('sysitem')->_('商品下架成功');
            event::fire('update.item', array($params['item_id']));
            return true;
        }
        else
        {
            $msg = app::get('sysitem')->_('商品下架失败');
            return false;
        }
    }

    /**
     * @brief 商品上下架
     * @author Lujy
     * @param $params int itemId
     * @param $status string onsale(上架、出售中) instock(下架、库中)
     * @param $msg bool 处理结果
     *
     * @return bool
     */
    public function setSaleStatus($params)
    {
        $itemId = $params['item_id'];
        $status = $params['approve_status'];
        $ojbMdlItem = app::get('sysitem')->model('item_status');

        $itemRealStore = kernel::single('sysitem_item_redisStore')->getStoreByItemId($itemId, 'realstore');

        if($status=='onsale')
        {
            if( $itemRealStore <= 0 )
            {
                throw new \LogicException('库存为0不能上架');
            }
            $data = array('approve_status' => 'onsale','list_time'=>time());
        }
        if($status=='instock')
        {
            $data = array('approve_status' => 'instock','delist_time'=>time());

            if ($itemId) {
                //判断团购商品是否可以修改
                $activityStatus = app::get('sysitem')->rpcCall('promotion.activity.item.info', ['item_id'=>$itemId, 'valid'=>1]);

                if($activityStatus['status'])
                {
                    $msg = app::get('sysitem')->_('该商品正在活动中不可修改！');
                    throw new \LogicException($msg);
                }
            }
        }

        if ($status=='pending')
        {
            if( $itemRealStore <= 0 )
            {
                throw new \LogicException('库存为0不能提交审核');
            }
            $data = array('approve_status' => 'pending','delist_time'=>time());
        }

        if ($status=='refuse') {
            $data = array('approve_status' => 'refuse','reason'=>$params['reason'],'delist_time'=>time());
        }

        if($params['item_id']){
            $result = $ojbMdlItem->update($data, array('item_id' => intval($itemId) ) );
        }else{
            $result = $ojbMdlItem->update($data, array('approve_status|nohas' => 'onsale' ) );
        }


        if($result)
        {
            return true;
        }
        else
        {
            $status == 'onsale' ? $msg = app::get('sysitem')->_('商品上架失败') : $msg = app::get('sysitem')->_('商品下架失败');
            throw new \LogicException($msg);
        }
    }

    private function __checkPost($postData)
    {
        // 修改情况下的各种判断
        if($postData['item_id'])
        {
            // 判断商品是否属于本店铺
            $objMdlItem = app::get('sysitem')->model('item');
            $itemInfo = $objMdlItem->getList('item_id,cat_id,nospec', [ 'item_id'=>$postData['item_id'], 'shop_id'=>$postData['shop_id'] ]);
            if(!$itemInfo)
            {
                $msg = app::get('sysitem')->_('该商品不属于当前用户，或者已经被删除');
                throw new \LogicException($msg);
            }
            // 判断类目是否被修改
            if($itemInfo[0]['cat_id'] != $postData['cat_id'])
            {
                $msg = app::get('sysitem')->_('不能更换商品类目ID');
                throw new \LogicException($msg);
            }
            if($itemInfo[0]['nospec'] != $postData['nospec'])
            {
                $msg = app::get('sysitem')->_('单品和多规格不能切换');
                throw new \LogicException($msg);
            }
            //团购判断
            $activityStatus = app::get('sysitem')->rpcCall('promotion.activity.item.info', ['item_id'=>$postData['item_id'], 'valid'=>1]);
            if($activityStatus['status'])
            {
                $msg = app::get('sysitem')->_('该商品正在活动中不可修改');
                throw new \LogicException($msg);
            }
        }

        // 判断是否可编辑
        if($postData['item_id'])
        {
            $newSku = array_column($postData['sku'], 'sku_id');
            $objSku = app::get('sysitem')->model('sku');
            $skudata = $objSku->getList('sku_id,spec_info', ['item_id'=>$postData['item_id']] );
            foreach($skudata as $key=>$value)
            {
                if(in_array($value['sku_id'], $newSku))
                {
                    unset($skudata[$key]);
                    continue;
                }
                $oldSku[$value['sku_id']] = $value['spec_info'];
                $oldSkuId[] = $value['sku_id'];
            }

            if($oldSkuId)
            {
                $params['status'] = "WAIT_BUYER_PAY,WAIT_SELLER_SEND_GOODS,WAIT_BUYER_CONFIRM_GOODS";
                $params['sku_id'] = implode(',',$oldSkuId);
                $params['fields'] = 'sku_id' ;
                $trade = app::get('sysitem')->rpcCall('trade.order.list.get', $params);
                if($trade)
                {
                    foreach($trade as $val)
                    {
                        if($oldSku[$val['sku_id']])
                        {
                            $msg .= $oldSku[$val['sku_id']].";";
                        }
                    }
                    throw new \LogicException(app::get('sysitem')->_($msg.'的货品有未处理的订单'));
                }

                //检测该货品是否参加过赠品
                $skuGift = app::get('sysitem')->rpcCall('promotion.gift.sku.get',['sku_id'=>$params['sku_id'],'end_time'=>'than','valid'=>1]);
                if($skuGift)
                {
                    foreach($skuGift as $val)
                    {
                        if($oldSku[$val['sku_id']])
                        {
                            $msg .= $oldSku[$val['sku_id']].";";
                        }
                    }
                    throw new \LogicException(app::get('sysitem')->_($msg.'的货品有参加赠品促销中的赠品'));
                }
            }
        }

        // 添加商品情况下的判断
        if(!$postData['item_id'])
        {
            $objMdlItem = app::get('sysitem')->model('item');
            $apiData['shop_id'] = $postData['shop_id'];
            $apiData['fields'] = 'max_item';
            $maxItem = app::get('sysitem')->rpcCall('shop.type.getinfo',$apiData);
            $itemCount = $objMdlItem->count(array('shop_id'=>$postData['shop_id']));
            if($itemCount >= $maxItem['max_item'])
            {
                $msg = app::get('sysitem')->_("您的店铺最多可以添加".$maxItem['max_item']."个商品");
                throw new \LogicException($msg);
            }
        }

        /* 通用判断--start */
        // 判断是否叶子分类
        $cat = app::get('sysitem')->rpcCall('category.cat.get.info', ['cat_id'=>$postData['cat_id'], 'fields'=>'is_leaf']);
        if(!$cat[$postData['cat_id']]['is_leaf'])
        {
            $msg = app::get('sysitem')->_('商品分类必须为三级分类');
            throw new \LogicException($msg);
        }

        // 判断运费模板
        $dlytmplInfo = app::get('sysitem')->rpcCall('logistics.dlytmpl.get', ['template_id'=>$postData['dlytmpl_id'], 'status'=>'on', 'shop_id'=>$postData['shop_id'], 'fields'=>'name']);
        if(!$dlytmplInfo)
        {
            $msg = app::get('sysitem')->_('运费模板没有启用或添加，并且只能填写本店铺的运费模板!');
            throw new \LogicException($msg);
        }

        // 判断商品图片数量
        $imagescount = count( explode(',', $postData['list_image']) );
        if($imagescount>10)
        {
            $msg = app::get('sysitem')->_('每个商品最多添加10张图片');
            throw new \LogicException($msg);
        }
        // 判断店铺分类是否属于本店铺
        $scparams = ['page_size'=>100,'page_no'=>1, ' fields'=>'cat_id', 'shop_id'=>$postData['shop_id'], 'cat_id'=>$postData['shop_cat_id'], 'is_leaf'=>1];
        $shopCatList = app::get('sysitem')->rpcCall('shop.cat.list', $scparams);
        $postShopCatCount = count( explode(',', trim( $postData['shop_cat_id'], ',') ) );//去除前后逗号后再拆分
        if($postShopCatCount != $shopCatList['count'])
        {
            $msg = app::get('sysitem')->_('店铺自定义分类必须属于本店铺，且必须是叶子节点，多个分类且以半角逗号隔开');
            throw new \LogicException($msg);
        }
        // 检查SKU信息
        if(!$postData['nospec'])
        {
            $postData['sku'] = $postData['sku'];
            $postData['spec'] = json_decode($postData['spec'],1);
            foreach($postData['sku'] as $pk=>$pv)
            {
                if(is_array($postData['sku']) && is_array($postData['spec']))
                {
                    if( count($pv['spec_desc']['spec_value_id']) < count($postData['spec']) )
                    {
                        $msg = app::get('sysitem')->_( '未选定全部规格' );
                        throw new \LogicException($msg);
                    }
                }

                // 多规格价格判断
                $this->__checkPrice($pv['price'], $pv['mkt_price'], $pv['cost_price']);
            }
        }

        return true;

    }

    private function __checkPrice($price, $mktPrice, $costPrice)
    {
        // 价格判断
        if($price <0 || strlen($price) > 13)
        {
            $msg = app::get('sysitem')->_( '销售价格格式错误' );
            throw new \LogicException($msg);
        }

        if($mktPrice <0 || strlen($mktPrice) > 13)
        {
            $msg = app::get('sysitem')->_( '原价格格式错误' );
            throw new \LogicException($msg);
        }

        if($costPrice <0 || strlen($costPrice) > 13)
        {
            $msg = app::get('sysitem')->_( '成本价格格式错误' );
            throw new \LogicException($msg);
        }

        return true;
    }

    // 添加商品（包括单品和多规格）,必须通过添加、修改商品接口调用此方法，因为许多验证是在接口做的，这里省略了
    function add($postData, &$reitemId)
    {
        // 初步参数检查
        $this->__checkPost($postData);

        // 如果是单品，则重置某些值
        if($postData['nospec'])
        {
            unset($postData['sku']);
            unset($postData['spec_value']);
            unset($postData['spec']);
            unset($postData['images']);
        }

        $reitemId = $this->__formatData($postData);

        return true;
    }

    private function __formatData( $postData )
    {
        // 添加单品时(没有规格),构建sku基础信息
        if($postData['nospec'])
        {
            // 单品价格判断
            unset($postData['sku']);
            $this->__checkPrice($postData['price'], $postData['mkt_price'], $postData['cost_price']);
            if($postData['item_id'])
            {
                $singleSku = app::get('sysitem')->model('sku')->getList('sku_id', ['item_id'=>$postData['item_id']]);
                if(count($singleSku) == 1)
                {
                    $postData['sku'][0]['sku_id'] = $singleSku[0]['sku_id'] ;
                }
            }
            $postData['sku'][0]['store']      = $postData['store'];
            $postData['sku'][0]['price']      = $postData['price'];
            $postData['sku'][0]['cost_price'] = $postData['cost_price'] ? $postData['cost_price'] : 0;
            $postData['sku'][0]['mkt_price']  = $postData['mkt_price'] ? $postData['mkt_price'] : 0;
            $postData['sku'][0]['bn']         = $postData['bn'];
            $postData['sku'][0]['barcode']    = $postData['barcode'];
            $postData['sku'][0]['weight']     = $postData['weight'];
        }

        foreach( $postData['sku'] as $prok => $pro )
        {
            if( !$pro['sku_id'] || substr( $pro['sku_id'],0,4 ) == 'new' )
            {
                unset( $postData['sku'][$prok]['sku_id'] );
            }

            $postData['sku'][$prok]['store']      = intval($postData['sku'][$prok]['store']);
            $postData['sku'][$prok]['price']      = trim($postData['sku'][$prok]['price']);
            $postData['sku'][$prok]['cost_price'] = trim($postData['sku'][$prok]['cost_price']) ?  : 0;
            $postData['sku'][$prok]['mkt_price']  = trim($postData['sku'][$prok]['mkt_price']) ? : 0;
            $postData['sku'][$prok]['bn']         = trim($postData['sku'][$prok]['bn']) ? : strtoupper(uniqid('s'));;
            $postData['sku'][$prok]['barcode']    = trim($postData['sku'][$prok]['barcode']);
            $postData['sku'][$prok]['weight']     = trim($postData['weight']);
            $specKey = '';
            if(!$postData['nospec'])
            {
                sort($pro['spec_desc']['spec_value_id']);
                $specKey = implode('_', $pro['spec_desc']['spec_value_id']);
            }
            $postData['sku'][$prok]['spec_key'] = $specKey;

            if (isset($pro['spec_desc']) && $pro['spec_desc'] && is_array($pro['spec_desc']) && isset($pro['spec_desc']['spec_value']) && $pro['spec_desc']['spec_value'])
            {
                $oProps = app::get('syscategory')->model('props');
                $tmpSpecInfo = array();
                foreach( $pro['spec_desc']['spec_value'] as $spec_v_k => $spec_v_v ){
                    $specname = $oProps->getRow( 'prop_name' ,array('prop_id'=>$spec_v_k));
                    $tmpSpecInfo[] = $specname['prop_name'].'：'.$spec_v_v;
                }
                $postData['sku'][$prok]['spec_info'] = implode('、', (array)$tmpSpecInfo);
            }
        }

        //检测原有库存和添加的库存是否合法
        if($postData['item_id'] && $postData['sku'])
        {
            $itemStore = 0;
            $skuIds = array_column($postData['sku'], 'sku_id');
            $skuStore = kernel::single('sysitem_item_redisStore')->getSkuStore($skuIds, 'freez');
            foreach ($postData['sku'] as $key => $value)
            {
                if($skuStore[$value['sku_id']] && $value['store'] < $skuStore[$value['sku_id']]['freez'])
                {
                    $msg = app::get('sysitem')->_($value['spec_info'].'货品库存不能小于冻结库存！');
                    throw new \LogicException($msg);
                }
                $itemStore += $value['store'];
            }
            $postData['store'] = $itemStore;
        }

        $db = app::get('sysitem')->database();
        $db->beginTransaction();
        try{
            // 组织并保存商品主表数据
            $item = $this->__formatItemData($postData);
            // 组织并保存SKU表数据
            $sku = $this->__formatSkuData($postData, $item);
            // 组织并保存商品统计表数据
            $itemCount = $this->__formatItemCountData($item);
            // 组织并保存商品描述数据
            $itemDesc = $this->__formatItemDescData($postData, $item);
            // 组织并保存商品自然属性数据
            $itemNatureProps = $this->__formatItemNaturePorpsData($postData, $item);
            // 组织并保存商品状态数据
            $itemStatus = $this->__formatItemStatusData($postData, $item);
            // 组织并保存商品库存数据
            $itemStore = $this->__formatItemStoreData($postData, $item);
            // 组织并保存SKU库存数据
            $skuStore = $this->__formatSkuStoreData($postData, $sku);
            // 组织并保存SKU规格索引数据
            $specIndex = $this->__formatSpecIndexData($item, $sku);

            $db->commit();
        }catch(Exception $e){
            $db->rollback();
            throw $e;
        }

        // 返回商品ID
        return $item['item_id'];
    }

    // 格式化商品表信息
    private function __formatItemData( $postData )
    {
        // 商品编号检查,没有编码则生成一个
        $data['bn'] = trim($postData['bn']) ? : strtoupper(uniqid('g'));
        if( $this->__checkProductBn($data['bn'], $postData['item_id'], $postData['shop_id'], 'item') )
        {
            $msg = app::get('sysitem')->_('您所填写的货号已被使用，请检查！');
            throw new \LogicException($msg);
        }

        // 商品默认图片
        if($postData['list_image'])
        {
            $listimages = explode(',', $postData['list_image']);
            $data['image_default_id'] = $listimages[0];
        }

        if( $postData['params'] )
        {
            $itemParams = array();
            foreach( $postData['params'] as $gpk => $gpv )
            {
                $itemParams[$postData['itemParams']['group'][$gpk]][$postData['itemParams']['item'][$gpk]] = $gpv;
            }
            $data['params'] = $itemParams;
        }

        if(!$postData['item_id'])
        {
            $data['created_time'] = time();
        }

        // 销售属性数组
        $postData['spec'] = json_decode($postData['spec'], 1);
        if( $postData['spec'] )
        {
            foreach( $postData['spec'] as $specId=>&$specValue )
            {
                if( $specValue['show_type'] != 'image' || empty($specValue['option']) ) continue;
                foreach($specValue['option'] as $specValueId=>&$specValueData)
                {
                    $specValueData['spec_image_url'] = $postData['images'][$specId.'_'.$specValueId];
                    $specValueData['spec_image'] = $postData['images'][$specId.'_'.$specValueId];
                }
            }
        }
        else
        {
            $item['spec'] = null;
        }

        $data['spec_desc'] = [];
        if( !$postData['nospec'] && is_array($postData['spec']) )
        {
            foreach( $postData['spec'] as $gSpecId => $gSpecOption )
            {
                $data['spec_desc'][$gSpecId] = $gSpecOption['option'];
            }
        }


        if($postData['item_id'])
        {
            $data['item_id'] = $postData['item_id'];
        }

         // 判断店铺是不是自营店铺
        $selfShopType = app::get('sysitem')->rpcCall('shop.get', ['shop_id'=>$postData['shop_id']]);
        if($selfShopType['shop_type'] == 'self')
        {
            $postData['is_selfshop'] = 1;
        }

        $data['shop_id'] = $postData['shop_id'];
        $data['cat_id'] = $postData['cat_id'];
        $data['brand_id'] = $postData['brand_id'];
        $data['shop_cat_id'] = ',' . $postData['shop_cat_id'] . ',';
        $data['title'] = trim($postData['title']);
        $data['sub_title'] = strip_tags(str_replace(array("\r\n", "\r", "\n"), "", $postData['sub_title'])); // 去除子标题特殊字符
        // $data['bn'] = $postData['bn'];
        $data['price'] = $postData['price'];
        $data['cost_price'] = $postData['cost_price'] ? : 0;
        $data['mkt_price'] = $postData['mkt_price'] ? : 0;
        $data['show_mkt_price'] = $postData['show_mkt_price'] ? : 0;
        $data['weight'] = $postData['weight'];
        $data['unit'] = $postData['unit'];
        $data['list_image'] = $postData['list_image'];
        $data['order_sort'] = $postData['order_sort'] ? : 0;
        // $data['created_time'] = $postData['created_time'];
        $data['modified_time'] = time();
        // $data['has_discount'] = $postData['has_discount'];
        // $data['is_virtual'] = $postData['is_virtual'];
        // $data['is_timing'] = $postData['is_timing'];
        // $data['violation'] = $postData['violation'];
        $data['is_selfshop'] = $postData['is_selfshop'];
        $data['nospec'] = $postData['nospec'];
        // $data['spec_desc'] = $postData['spec_desc'];
        $data['props_name'] = $postData['props_name'];
        // $data['params'] = $postData['params'];
        $data['sub_stock'] = $postData['sub_stock'];
        $data['outer_id'] = $postData['outer_id'];
        // $data['is_offline'] = $postData['is_offline'];
        $data['barcode'] = $postData['barcode'];
        $data['use_platform'] = $postData['use_platform'];
        $data['dlytmpl_id'] = $postData['dlytmpl_id'];

        app::get('sysitem')->model('item')->save($data);

        return $data;
    }

    // 格式化商品统计相关信息表
    private function __formatItemCountData( $postData )
    {
        $data['item_id'] = $postData['item_id'];
        app::get('sysitem')->model('item_count')->save($data);

        return $data;
    }

    // 格式化商品描述表
    private function __formatItemDescData( $postData, $item )
    {
        $data = [
            'item_id' => $item['item_id'],
            'pc_desc' => addslashes($postData['desc']),
            'wap_desc' => addslashes($postData['wap_desc']),
        ];

        app::get('sysitem')->model('item_desc')->save($data);

        return $data;
    }

    // 格式化商品自然属性表
    private function __formatItemNaturePorpsData( $postData, $item )
    {
        $objMdlItemNatureProps = app::get('sysitem')->model('item_nature_props');
        foreach($postData['nature_props'] as $k=>$v)
        {
            $data = array(
                'item_id' => $item['item_id'],
                'prop_id' => $k,
                'prop_value_id' => $v,
                'pv_number' => $v,
                'pv_str' => '',
                'modified_time' => time(),
            );
            $objMdlItemNatureProps->save($data);
        }

        return true;
    }

    // 格式化商品状态表
    private function __formatItemStatusData( $postData, $item )
    {
        $data = [];
        if($postData['approve_status'] == 'instock')
        {
            $data['approve_status'] = 'instock';
        }
        elseif($postData['approve_status'] == 'onsale')
        {
            $data['approve_status'] = 'onsale';
            $data['list_time'] = time();
        }
        else
        {
            $data['approve_status'] = 'instock';
        }

        $data['item_id'] = $item['item_id'];
        $data['shop_id'] = $postData['shop_id'];
        // $data['approve_status'] = $postData['approve_status'];
        // $data['reason'] = $postData['reason'];
        // $data['list_time'] = $postData['list_time'];
        // $data['delist_time'] = $postData['delist_time'];

        app::get('sysitem')->model('item_status')->save($data);

        return $data;
    }

    // 格式化商品库存表
    private function __formatItemStoreData( $postData, $item )
    {
        $data['item_id'] = $item['item_id'];
        $data['store'] = $postData['store'];

        app::get('sysitem')->model('item_store')->save($data);

        kernel::single('sysitem_item_redisStore')->initItemStore($item['item_id'], $postData['store']);

        return $data;
    }

    // 格式化SKU表
    private function __formatSkuData( $postData, $item)
    {
        $objMdlSku = app::get('sysitem')->model('sku');
        // sku表新老数据对比，判断哪些SKU记录要被删除
        if($postData['item_id'] && !$postData['nospec'])
        {
            $oldSpecKeys = $objMdlSku->getList('spec_key,sku_id', ['item_id'=>$postData['item_id']]);
            $newSpecKeys = array_column($postData['sku'], 'spec_key');
            foreach ($oldSpecKeys as $value) {
                if( !in_array($value['spec_key'], $newSpecKeys) ){
                    $objMdlSku->delete(['item_id'=>$postData['item_id'], 'spec_key'=>$value['spec_key']]);
                }
            }
        }
        $skus = [];
        foreach ($postData['sku'] as $val) {
            $data = [];
            if($val['sku_id'])
            {
                $data['sku_id'] = $val['sku_id'];
            }
            $data['item_id'] = $item['item_id'];
            $data['title'] = $postData['title'];
            // 商品编号检查,没有编码则生成一个
            if( $this->__checkProductBn($val['bn'], $val['sku_id'], $postData['shop_id'], 'sku') )
            {
                $msg = app::get('sysitem')->_('您所填写的货号已被使用，请检查！');
                throw new \LogicException($msg);
            }
            $data['bn'] = $val['bn'];
            $data['price'] = $val['price'];
            $data['cost_price'] = $val['cost_price'];
            $data['mkt_price'] = $val['mkt_price'];
            $data['barcode'] = $val['barcode'];
            $data['weight'] = $val['weight'];
            if(!$val['sku_id'])
            {
                $data['created_time'] = time();
            }
            $data['modified_time'] = time();
            $data['spec_key'] = $val['spec_key'];
            $data['spec_info'] = $val['spec_info'] ? : '';
            $data['spec_desc'] = $val['spec_desc'];
            $data['status'] = 'normal';
            $data['outer_id'] = $val['outer_id'];
            $data['shop_id'] = $postData['shop_id'];
            $data['image_default_id'] = $item['image_default_id'];
            $data['cat_id'] = $postData['cat_id'];
            $data['brand_id'] = $postData['brand_id'];
            $data['shop_cat_id'] = $postData['shop_cat_id'];
            $data['store'] = $val['store']; //sku表不用，给别的方法用的，不能删
            $objMdlSku->save($data);
            $skus[] = $data;
        }

        return $skus;
    }

    // 格式化SKU库存表
    private function __formatSkuStoreData( $postData, $sku )
    {
        $objMdlSkuStore = app::get('sysitem')->model('sku_store');
        // 新老数据对比,看哪些SKU库存记录需要被删除
        if($postData['item_id'] && !$postData['nospec'])
        {
            $oldSku = $objMdlSkuStore->getList('*', ['item_id'=>$postData['item_id']]);
            $newSku = array_column($sku, 'sku_id');
            $deleteStoreSkuId = [];
            foreach ($oldSku as $value)
            {
                if(!in_array($value['sku_id'], $newSku))
                {
                    $objMdlSkuStore->delete(['sku_id'=>$value['sku_id'], 'item_id'=>$postData['item_id']]);
                    $deleteStoreSkuId[] = $value['sku_id'];
                }
            }

            if( $deleteStoreSkuId )
            {
                kernel::single('sysitem_item_redisStore')->deleteSkuStore($deleteStoreSkuId);
            }
        }

        $redisStore = [];
        foreach ($sku as $val)
        {
            $data = [
                'item_id' => $val['item_id'],
                'sku_id' => $val['sku_id'],
                'store' => $val['store'],
            ];

            $objMdlSkuStore->save($data);

            $redisStore[] = $data;
        }

        if( $redisStore )
        {
            kernel::single('sysitem_item_redisStore')->initSkuStore($redisStore);
        }

        return true;
    }

    // 格式化商品销售属性关联表
    private function __formatSpecIndexData( $item, $sku )
    {
        if(!$item['item_id'])
        {
            $msg = app::get('sysitem')->_('保存商品出错(specindex)');
            throw new \LogicException($msg);
        }
        $objMdlSpecIndex = app::get('sysitem')->model('spec_index');
        $objMdlSpecIndex->delete( array('item_id'=>$item['item_id']) );
        foreach( $sku as $pro )
        {
            if( $pro['spec_desc'] )
            {
                foreach( $pro['spec_desc']['spec_value_id'] as $specId => $specValueId )
                {
                    $data = array(
                        'cat_id'        => $item['cat_id'],
                        'prop_id'       => $specId,
                        'prop_value_id' => $specValueId,
                        'item_id'       => $item['item_id'],
                        'sku_id'        => $pro['sku_id'],
                        'modified_time' => time(),
                    );
                    $objMdlSpecIndex->save($data);
                }
            }
        }

        return true;
    }

    private function __checkProductBn($bn, $primaryid=0, $shopId=0, $type=""){
        if(empty($bn)){
            return false;
        }
        $ojbMdlItem = app::get('sysitem')->model('item');
        $ojbMdlSku = app::get('sysitem')->model('sku');
        if($type == "item")
        {
            $data = $ojbMdlItem->getRow("item_id",['bn'=>$bn,'shop_id'=>$shopId,'item_id|noequal'=>$primaryid]);
            if($data) return true;
        }
        if($type == "sku")
        {
            $data = $ojbMdlSku->getRow("item_id",['bn'=>$bn,'shop_id'=>$shopId,'sku_id|noequal'=>$primaryid]);
            if($data) return true;
        }
        return false;
    }
    //根据规格id获取相关规格的商品
    public function hasPropItem($propId)
    {
        $specdescModel = app::get('sysitem')->model('spec_index');
        $itemList = $specdescModel->getList('item_id',array('prop_id'=>$propId));
        return $itemList;
    }

}
