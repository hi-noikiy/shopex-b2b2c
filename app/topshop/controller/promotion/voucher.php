<?php

class topshop_ctl_promotion_voucher extends topshop_controller {

    public function index()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('购物券管理');
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }

        $pageSize = 10;
        $params = array(
            'shop_id' => $this->shopId,
            'page_no' => intval($filter['pages']),
            'page_size' => intval($pageSize),
            'fields' =>'*',
            'is_apply' => intval(input::get('is_apply', 0)),
        );
        $voucherData = app::get('topshop')->rpcCall('promotion.voucher.shop.list.get', $params);

        $shopId = $this->shopId;
        $shopParams = array(
            'shop_id' => $shopId,
            'fields' =>'cat.cat_name,cat.cat_id',
        );
        $shopdata = app::get('topshop')->rpcCall('shop.get.detail',$shopParams);
        foreach ($shopdata['cat'] as $key => $value)
        {
            $catId[$key] = $value['cat_id'];
        }

        $shoptype = $shopdata['shop']['shop_type'];
        foreach ($voucherData['list'] as $key => $value)
        {
            $value['limit_cat'] = explode(',',$value['limit_cat']);
            if(array_intersect($catId,$value['limit_cat']) && in_array($shoptype,explode(',',$value['shoptype'])))
            {
                $voucherData['list'][$key]['isactivity']=1;
            }
            else
            {
                $voucherData['list'][$key]['isactivity']=0;
            }

            if( $value['apply_end_time'] < time() || !$value['valid_status'])
            {
                $voucherData['list'][$key]['isactivity']=0;
            }
        }

        $pagedata['list'] = $voucherData['list'];

        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($voucherData['pagers']['total']>0) $total = ceil($voucherData['pagers']['total']/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('topshop_ctl_promotion_voucher@index', $filter),
            'current'=>$current,
            'use_app' => 'topshop',
            'total'=>$total,
            'token'=>$filter['pages'],
        );

        $pagedata['now'] = time();
        $pagedata['total'] = $voucherData['pagers']['total'];
        $pagedata['is_apply'] = input::get('is_apply',0);

        return $this->page('topshop/promotion/voucher/index.html', $pagedata);
    }

    public function detail()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('购物券详情');
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topshop_ctl_index@index'),'title' => app::get('topshop')->_('首页')],
            ['url'=> url::action('topshop_ctl_promotion_voucher@index'),'title' => app::get('topshop')->_('购物券管理')],
            ['title' => app::get('topshop')->_('购物券详情')],
        );

        $pagedata = app::get('topshop')->rpcCall('promotion.voucher.get', ['voucher_id'=>input::get('voucher_id')]);

        if( !$pagedata )
        {
            return redirect::action('topshop_ctl_promotion_voucher@index');
        }

        $voucherRegisterInfo = app::get('topshop')->rpcCall('promotion.voucher.register.get', ['voucher_id'=>input::get('voucher_id'),'shop_id'=>$this->shopId]);

        $pagedata['used_platform'] = explode(',',$pagedata['used_platform']);

        $shopId = $this->shopId;
        $shopParams = array(
            'shop_id' => $shopId,
            'fields' =>'cat.cat_name,cat.cat_id',
        );
        $shopdata = app::get('topshop')->rpcCall('shop.get.detail',$shopParams);
        foreach ($shopdata['cat'] as $key => $value)
        {
            $catId[$key] = $value['cat_id'];
        }

        $shoptype = $shopdata['shop']['shop_type'];
        $pagedata['limit_cat'] = explode(',',$pagedata['limit_cat']);
        $shopLimitCat = array_intersect($catId,$pagedata['limit_cat']);

        if( !$voucherRegisterInfo )
        {
            if( $shopLimitCat && in_array($shoptype,explode(',',$pagedata['shoptype'])))
            {
                $pagedata['isactivity'] = 1;
            }
            else
            {
                $pagedata['isactivity'] = 0;
                if( in_array($shoptype,explode(',',$pagedata['shoptype'])) )
                {
                    $pagedata['not_apply_reason'] = '不支持当前店铺类型参加';
                }
                else
                {
                    $pagedata['not_apply_reason'] = '当前店铺签约类目不在可参加类目范围内';
                }
            }

            if( $pagedata['apply_end_time'] < time() )
            {
                $pagedata['isactivity'] = 0;
                $pagedata['not_apply_reason'] = '报名时间已截止';
            }

            if( !$pagedata['valid_status'] )
            {
                $pagedata['isactivity'] = 0;
                $pagedata['not_apply_reason'] = '平台已关闭购物券活动';
            }
        }
        else
        {
            $voucherRegisterInfo['cat_id'] = explode(',', $voucherRegisterInfo['cat_id']);
            $pagedata['register'] = $voucherRegisterInfo;
        }

        $gradeList = app::get('topshop')->rpcCall('user.grade.list');
        $gradeKeyValue = array_bind_key($gradeList, 'grade_id');
        $validGrade = explode(',', $pagedata['valid_grade']);
        foreach( $validGrade as $id )
        {
            $pagedata['valid_grade_list'][] = $gradeKeyValue[$id]['grade_name'];
        }

        // 获取店铺类型
        $shopTypeAll = app::get('syspromotion')->rpcCall('shop.type.get');
        $shopTypeAll['self'] = array(
            'shop_type' => 'self',
            'name' => '运营商自营',
        );
        $pagedata['shoptype'] = explode(',', $pagedata['shoptype']);
        foreach( $pagedata['shoptype'] as $type )
        {
            $pagedata['shoptype_list'][] = $shopTypeAll[$type]['name'];
        }

        //获取类目
        $catlist = app::get('syspromotion')->rpcCall('category.cat.get.info',array('level' =>1));
        foreach( $pagedata['limit_cat'] as $catId )
        {
            $pagedata['cat_list'][] = $catlist[$catId]['cat_name'];

            if( in_array($catId, $shopLimitCat) )
            {
                $pagedata['shop_limit_cat'][] = [
                    'cat_id' => $catId,
                    'cat_name' => $catlist[$catId]['cat_name']
                ];
            }
        }

        //补贴信息
        $subsidyData = app::get('topshop')->rpcCall('clearing.subsidy.voucher.basic.shop',['voucher_id'=>input::get('voucher_id'),'shop_id'=>$this->shopId]);
        $pagedata['subsidyData'] = $subsidyData;

        return $this->page('topshop/promotion/voucher/detail.html', $pagedata);
    }

    /**
     * 申请参加购物券报名 function
     *
     * @return void
     */
    public function apply()
    {
        $apiData['voucher_id'] = input::get('voucher_id');
        $apiData['shop_id'] = $this->shopId;
        $apiData['cat_id'] = implode(',', input::get('cat_id'));
        try
        {
            $result = app::get('topshop')->rpcCall('promotion.voucher.register', $apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error','',$msg,true);
        }

        $this->sellerlog('申请购物券。购物券ID是 '.$apiData['voucher_id']);
        $msg = app::get('topshop')->_('申请购物券保存成功');
        $url = url::action('topshop_ctl_promotion_voucher@detail',['voucher_id'=>$apiData['voucher_id']]);
        return $this->splash('success',$url,$msg,true);
    }
}
