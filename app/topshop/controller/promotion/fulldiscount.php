<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author lujunyi@shopex.cn
 */


class topshop_ctl_promotion_fulldiscount extends topshop_controller {

    public function list_fulldiscount()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('满折管理');
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
        $fulldiscountListData = app::get('topshop')->rpcCall('promotion.fulldiscount.list', $params,'seller');
        $count = $fulldiscountListData['total'];
        $pagedata['fulldiscountList'] = $fulldiscountListData['data'];

        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('topshop_ctl_promotion_fulldiscount@list_fulldiscount', $filter),
            'current'=>$current,
            'use_app'=>'topshop',
            'total'=>$total,
            'token'=>$filter['pages'],
        );

        $gradeList = app::get('topshop')->rpcCall('user.grade.list');
        // 组织会员等级的key,value的数组，方便取会员等级名称
        $gradeKeyValue = array_bind_key($gradeList, 'grade_id');

        // 增加列表中会员等级名称字段
        foreach($pagedata['fulldiscountList'] as &$v)
        {
            $valid_grade = explode(',', $v['valid_grade']);

            $checkedGradeName = array();
            foreach($valid_grade as $gradeId)
            {
                $checkedGradeName[] = $gradeKeyValue[$gradeId]['grade_name'];
            }
            $v['valid_grade_name'] = implode(',', $checkedGradeName);
            $v['condition_value'] = $this->condition($v['condition_value']);
        }

        $pagedata['now'] = time();
        $pagedata['total'] = $count;
        $pagedata['examine_setting'] = app::get('sysconf')->getConf('shop.promotion.examine');

        return $this->page('topshop/promotion/fulldiscount/index.html', $pagedata);
    }


    public function edit_fulldiscount()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('添加/编辑满折促销');

        $apiData['fulldiscount_id'] = input::get('fulldiscount_id');
        $apiData['fulldiscount_itemList'] = true;
        $pagedata['valid_time'] = date('Y/m/d H:i', time()+60) . '-' . date('Y/m/d H:i', time()+120); //默认时间
        if($apiData['fulldiscount_id'])
        {
            $pagedata = app::get('topshop')->rpcCall('promotion.fulldiscount.get', $apiData);
            $pagedata['valid_time'] = date('Y/m/d H:i', $pagedata['start_time']) . '-' . date('Y/m/d H:i', $pagedata['end_time']);
            if($pagedata['shop_id']!=$this->shopId)
            {
                return $this->splash('error','','您没有权限编辑此满折促销',true);
            }
            $notItems = array_column($pagedata['itemsList'], 'item_id');
            $pagedata['notEndItem'] =  json_encode($notItems,true);
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

        $pagedata['condition_value'] = $this->condition($pagedata['condition_value']);
        $pagedata['ac'] = input::get('ac', '');
        foreach( $pagedata['itemsList'] as $itemRow )
        {
            if( $itemRow['sku_ids'] )
            {
                $pagedata['item_sku'][$itemRow['item_id']] = $itemRow['sku_ids'];
            }
        }

        return $this->page('topshop/promotion/fulldiscount/edit.html', $pagedata);
    }

    //查看满折
    public function show_fulldiscount()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('查看满折');
        $apiData['fulldiscount_id'] = input::get('fulldiscount_id');
        $apiData['fulldiscount_itemList'] = true;
        if($apiData['fulldiscount_id'])
        {
            $pagedata = app::get('topshop')->rpcCall('promotion.fulldiscount.get',$apiData);
            $pagedata['valid_time'] = date('Y/m/d H:i',$pagedata['start_time']).'  ~  '.date('Y/m/d H:i',$pagedata['end_time']);
            if($pagedata['shop_id'] !== $this->shopId)
            {
                return $this->splash('error','','您没有权限查看此满折促销',true);
            }
            $notItems = array_column($pagedata['itemsList'], 'item_id');
            $pagedata['notEndItem'] = json_encode($notItems,true);

        }
        $valid_grade = explode(',', $pagedata['valid_grade']);
        $pagedata['gradeList'] = app::get('topshop')->rpcCall('user.grade.list');
        $gradeIds = array_column($pagedata['gradeList'],'grade_id');

        if( !array_diff($gradeIds, $valid_grade))
        {
            $gradeStr = "所有会员";
        }
        else
        {
            foreach ($pagedata['gradeList'] as $member) {
                if(in_array($member['grade_id'], $valid_grade))
                {
                    $gradeStr .= $member['grade_name'].',';
                }
            }
            $gradeStr = rtrim($gradeStr,',');

        }
        $pagedata['grade_str'] = $gradeStr;
        $pagedata['condition_value'] = $this->condition($pagedata['condition_value']);
        $pagedata['ac'] = input::get('ac', '');
        return $this->page('topshop/promotion/fulldiscount/show.html',$pagedata);
    }

    public function condition($condition)
    {
        $condList = explode(',',$condition);
        foreach ($condList as $key => $value)
        {
            $condList[$key] = explode('|',$value);
        }
        return $condList;
    }

    public function save_fulldiscount()
    {
        $params = input::get();

        $apiData['fulldiscount_id'] = $params['fulldiscount_id'];
        $apiData['fulldiscount_name'] = $params['fulldiscount_name'];
        $apiData['join_limit'] = intval($params['join_limit']);
        $apiData['used_platform'] = intval($params['used_platform']);
        $apiData['free_postage'] = intval($params['free_postage']);
        //优惠规则描述
        if($params['role_desc'])
        {
            $apiData['fulldiscount_desc'] = htmlspecialchars(strip_tags($params['role_desc']), ENT_QUOTES, 'UTF-8');
        }
        if( !$params['fulldiscount_name'] )
        {
            return $this->splash('error','','满折促销名称不能为空!',true);
        }
        else
        {
            $len = mb_strlen($params['fulldiscount_name'],'utf-8');
            if($len >30)
            {
                return $this->splash('error','','满折促销名称不能超过30个字',true);
            }
        }

        $conditionValue = null;
        foreach((array)$params['full'] as $k=>$v)
        {
            $joinfulldiscount = array();
            $joinfulldiscount['full'] = $v;
            $joinfulldiscount['discount'] = $params['discount'][$k];
            $conditionValue[] = $joinfulldiscount;
        }
        $apiData['condition_value'] = $conditionValue ? json_encode($conditionValue) : null;
        $apiData['shop_id'] = $this->shopId;
        $timeArray = explode('-', $params['valid_time']);
        $apiData['start_time']  = strtotime($timeArray[0]);
        $apiData['end_time'] = strtotime($timeArray[1]);
        $apiData['valid_grade'] = implode(',', $params['grade']);

        $fullDiscountRelItem = null;
        foreach( (array)$params['item_id'] as $key=>$itemId )
        {
            $itemData['item_id'] = $itemId;
            $itemData['sku_id'] = $params['item_sku'][$key] ? $params['item_sku'][$key] : null;
            $fullDiscountRelItem[] = $itemData;
        }

        $apiData['fulldiscount_rel_item'] = $fullDiscountRelItem ? json_encode($fullDiscountRelItem) : null;
        try
        {
            if($params['fulldiscount_id'])
            {
                // 修改满折促销
                $result = app::get('topshop')->rpcCall('promotion.fulldiscount.update', $apiData);
            }
            else
            {
                // 新添满折促销
                $result = app::get('topshop')->rpcCall('promotion.fulldiscount.add', $apiData);
            }
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            if($params['fulldiscount_id'])
            {
                $url = url::action('topshop_ctl_promotion_fulldiscount@edit_fulldiscount', array('fulldiscount_id'=>$params['fulldiscount_id']));
            }
            else{
                $url = url::action('topshop_ctl_promotion_fulldiscount@list_fulldiscount');
            }
            return $this->splash('error',$url,$msg,true);
        }
        $this->sellerlog('添加/修改满折促销。满折促销名称是 '.$apiData['fulldiscount_name']);
        $url = url::action('topshop_ctl_promotion_fulldiscount@list_fulldiscount');
        $msg = app::get('topshop')->_('保存满折促销成功');
        return $this->splash('success',$url,$msg,true);
    }

    //提交审核
    public function submit_approve(){
        $apiData = input::get();
        try{
            $fulldiscountInfo = app::get('topshop')->rpcCall('promotion.fulldiscount.get',$apiData);
            if($fulldiscountInfo['end_time'] <= time()){
                throw new \LogicException('您的活动已过期，无法提交审核!');
            }
            $result = app::get('topshop')->rpcCall('promotion.fulldiscount.approve',$apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $this->sellerlog('更新满折促销。满折促销ID是 '.$apiData['fulldiscount_id']);
        $url = url::action('topshop_ctl_promotion_fulldiscount@list_fulldiscount');
        $msg = app::get('topshop')->_('提交审核成功');
        return $this->splash('success', $url, $msg, true);
    }

    public function delete_fulldiscount()
    {
        $apiData['shop_id'] = $this->shopId;
        $apiData['fulldiscount_id'] = input::get('fulldiscount_id');
        $url = url::action('topshop_ctl_promotion_fulldiscount@list_fulldiscount');
        try
        {
            app::get('topshop')->rpcCall('promotion.fulldiscount.delete', $apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $this->sellerlog('删除满折促销。满折促销ID是 '.$apiData['fulldiscount_id']);
        $msg = app::get('topshop')->_('删除满折促销成功');
        return $this->splash('success', $url, $msg, true);
    }

    public function cancel_fulldiscount()
    {
        $apiData['shop_id'] = $this->shopId;
        $apiData['fulldiscount_id'] = input::get('fulldiscount_id');
        $url = url::action('topshop_ctl_promotion_fulldiscount@list_fulldiscount');
        try
        {
            app::get('topshop')->rpcCall('promotion.fulldiscount.cancel', $apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $this->sellerlog('取消满折促销。满折促销ID是 '.$apiData['fulldiscount_id']);
        $msg = app::get('topshop')->_('取消满折促销成功');
        return $this->splash('success', $url, $msg, true);
    }

    //根据商家id和3级分类id获取商家所经营的所有品牌
    public function getBrandList()
    {
        $shopId = $this->shopId;
        $catId = input::get('catId');
        $params = array(
            'shop_id'=>$shopId,
            'cat_id'=>$catId,
            'fields'=>'brand_id,brand_name,brand_url'
        );
        $brands = app::get('topshop')->rpcCall('category.get.cat.rel.brand',$params);
        return response::json($brands);
    }
}

