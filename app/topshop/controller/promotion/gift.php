<?php
class topshop_ctl_promotion_gift extends topshop_controller{

    //赠品列表
    public function list_gift()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('赠品促销管理');
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }

        $pageSize = 10;
        $params = array(
            'page_no' => $filter['pages'],
            'page_size' => intval($pageSize),
            'fields' =>'*',
            'shop_id'=> $this->shopId,
        );
        $giftListData = app::get('topshop')->rpcCall('promotion.gift.list', $params,'seller');
        $count = $giftListData['count'];
        $pagedata['giftList'] = $giftListData['gifts'];


        $gradeList = app::get('topshop')->rpcCall('user.grade.list');
        // 组织会员等级的key,value的数组，方便取会员等级名称
        $gradeKeyValue = array_bind_key($gradeList, 'grade_id');
        // 增加列表中会员等级名称字段
        foreach($pagedata['giftList'] as &$v)
        {
            $valid_grade = explode(',', $v['valid_grade']);

            $checkedGradeName = array();
            foreach($valid_grade as $gradeId)
            {
                $checkedGradeName[] = $gradeKeyValue[$gradeId]['grade_name'];
            }
            $v['valid_grade_name'] = implode(',', $checkedGradeName);
        }

        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('topshop_ctl_promotion_gift@list_gift', $filter),
            'current'=>$current,
            'total'=>$total,
            'use_app'=>'topshop',
            'token'=>$filter['pages'],
        );
        $pagedata['now'] = time();
        $pagedata['examine_setting'] = app::get('sysconf')->getConf('shop.promotion.examine');
        return $this->page('topshop/promotion/gift/index.html', $pagedata);

    }

    //查看赠品活动促销
    public function show_gift()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('查看赠品促销');
        $apiData['gift_id'] = input::get('gift_id');
        $apiData['gift_itemList'] = true;
        if($apiData['gift_id']){
            $pagedata = app::get('topshop')->rpcCall('promotion.gift.get',$apiData);
            $pagedata['valid_time'] = date('Y/m/d H:i',$pagedata['start_time']).' ~ '.date('Y/m/d H:i',$pagedata['end_time']);
            if($pagedata['shop_id'] != $this->shopId)
            {
                return $this->splash('error','您没有权限查看此赠品促销',true);
            }
            $pagedata['gift_item'] = $pagedata['gift_item'];
        }

        $valid_grade  = explode(',', $pagedata['valid_grade']);
        $pagedata['gradeList'] = app::get('topshop')->rpcCall('user.grade.list');
        $gradeIds = array_column($pagedata['gradeList'],'grade_id');
        if( !array_diff($gradeIds, $valid_grade))
        {
            $gradeStr = ' 所有会员';
        }
        else
        {
            foreach ($pagedata['gradeList'] as $member) {
                if(in_array($member['grade_id'],$valid_grade))
                {
                    $gradeStr .= $member['grade_name'].',';
                }
            }
            $gradeStr = rtrim($gradeStr,',');
        }
        $pagedata['grade_str'] = $gradeStr;
        $shopid = shopAuth::getShopId();
        //$pagedata['shopCatList'] = app::get('topshop')->rpcCall('shop.authorize.cat',array('shop_id'=>$shopid));
        $pagedata['ac'] = input::get('ac', '');
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        return $this->page('topshop/promotion/gift/show.html',$pagedata);

    }

    //增加、编辑赠品活动
    public function edit_gift()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('新添/编辑赠品促销');
        $apiData['gift_id'] = input::get('gift_id');
        $apiData['gift_itemList'] = true;
        $pagedata['valid_time'] = date('Y/m/d H:i', time()+60) . '-' . date('Y/m/d H:i', time()+120); //默认时间
        if($apiData['gift_id'])
        {
            $pagedata = app::get('topshop')->rpcCall('promotion.gift.get', $apiData);
            $pagedata['valid_time'] = date('Y/m/d H:i', $pagedata['start_time']) . '-' . date('Y/m/d H:i', $pagedata['end_time']);
            if($pagedata['shop_id']!=$this->shopId)
            {
                return $this->splash('error','','您没有权限编辑此赠品促销',true);
            }
            $notItems = array_column($pagedata['itemsList'], 'item_id');
            $pagedata['notEndItem'] =  json_encode($notItems,true);
            $pagedata['selectorExtendsData'] = json_encode($pagedata['itemsList'], true);
            $notSkus = array_column($pagedata['gift_item'], 'sku_id');
            $pagedata['notEndSku'] =  json_encode($notSkus,true);
            $pagedata['selectorExtendsDataSku'] = json_encode($pagedata['gift_item'], true);
        }

        $valid_grade = explode(',', $pagedata['valid_grade']);
        $pagedata['gradeList'] = app::get('topshop')->rpcCall('user.grade.list');
        foreach($pagedata['gradeList'] as &$v)
        {
            if( in_array($v['grade_id'], $valid_grade) )
            {
                $v['is_checked'] = true;
            }
        }
        $shopId = shopAuth::getShopId();
        $pagedata['shopCatList'] = app::get('topshop')->rpcCall('shop.authorize.cat',array('shop_id'=>$shopId));
        foreach( $pagedata['itemsList'] as $itemRow )
        {
            $pagedata['item_sku'][$itemRow['item_id']] = $itemRow['sku_ids'];
        }
        return $this->page('topshop/promotion/gift/edit.html', $pagedata);
    }

    //提交审核
    public function submit_approve(){
        $apiData = input::get();
        $xydiscountInfo = app::get('topshop')->rpcCall('promotion.gift.get',$apiData);
        try{
            if($xydiscountInfo['end_time'] <= time()){
                throw new \LogicException('您的活动已过期，无法提交审核!');
            }
            $result = app::get('topshop')->rpcCall('promotion.gift.approve',$apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $this->sellerlog('更新赠品促销。赠品促销ID是 '.$apiData['gift_id']);
        $url = url::action('topshop_ctl_promotion_gift@list_gift');
        $msg = app::get('topshop')->_('提交审核成功');
        return $this->splash('success', $url, $msg, true);
    }

    //保存赠品活动
    public function save_gift()
    {
        $params = input::get();

        $apiData['gift_name'] = $params['gift_name'];
        $apiData['gift_desc'] = $params['gift_desc'];
        $apiData['limit_quantity'] = $params['limit_quantity'];
        $apiData['shop_id'] = $this->shopId;

        $giftItem = null;
        foreach( (array)$params['sku_num'] as $skuId=>$skuNum )
        {
            $giftItem['sku_id'] = $skuId;
            $giftItem['quantity'] = $skuNum;
            $giftItem['withoutReturn'] = in_array($skuId, (array)$params['withoutReturn']) ? true : false;
            $apiData['gift_item'][] = $giftItem;
        }
        $apiData['gift_item'] = $apiData['gift_item'] ? json_encode($apiData['gift_item']) : null;

        // 可使用的有效期
        $canuseTimeArray = explode('-', $params['valid_time']);
        $apiData['start_time']  = strtotime($canuseTimeArray[0]);
        $apiData['end_time'] = strtotime($canuseTimeArray[1]);
        // 可以使用的会员等级
        $apiData['valid_grade'] = implode(',', $params['grade']);
        $giftRelItem = null;
        foreach( (array)$params['item_id'] as $key=>$itemId )
        {
            $itemData['item_id'] = $itemId;
            $itemData['sku_id'] = $params['item_sku'][$key];
            $giftRelItem[] = $itemData;
        }
        $apiData['gift_rel_item'] = $giftRelItem ? json_encode($giftRelItem) : null;

        try
        {
            if($params['gift_id'])
            {
                $apiData['gift_id'] = $params['gift_id'];
                // 修改赠品促销
                $result = app::get('topshop')->rpcCall('promotion.gift.update', $apiData);
            }
            else
            {
                // 新添加赠品促销
                $result = app::get('topshop')->rpcCall('promotion.gift.add', $apiData);
            }
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            $url = url::action('topshop_ctl_promotion_gift@edit_gift', array('gift_id'=>$params['gift_id']));
            return $this->splash('error',$url,$msg,true);
        }
        $this->sellerlog('添加/修改赠品促销。赠品促销名称是 '.$apiData['gift_name']);
        $url = url::action('topshop_ctl_promotion_gift@list_gift');
        $msg = app::get('topshop')->_('保存赠品促销成功');
        return $this->splash('success',$url,$msg,true);
    }

    //删除赠品活动
    public function delete_gift()
    {
        $apiData['shop_id'] = $this->shopId;
        $apiData['gift_id'] = input::get('gift_id');
        $url = url::action('topshop_ctl_promotion_gift@list_gift');
        try
        {
            app::get('topshop')->rpcCall('promotion.gift.delete', $apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $this->sellerlog('删除赠品促销。赠品促销ID是 '.$apiData['gift_id']);
        $msg = app::get('topshop')->_('删除赠品促销成功');
        return $this->splash('success', $url, $msg, true);
    }

    //取消赠品促销
    public function cancel_gift()
    {
        $apiData['shop_id'] = $this->shopId;
        $apiData['gift_id'] = input::get('gift_id');
        $url = url::action('topshop_ctl_promotion_gift@list_gift');
        try
        {
            app::get('topshop')->rpcCall('promotion.gift.cancel', $apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $this->sellerlog('取消赠品促销。赠品促销ID是 '.$apiData['gift_id']);
        $msg = app::get('topshop')->_('取消赠品促销成功');
        return $this->splash('success', $url, $msg, true);
    }
}
