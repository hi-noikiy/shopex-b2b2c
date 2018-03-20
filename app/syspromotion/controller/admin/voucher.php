<?php

class syspromotion_ctl_admin_voucher extends desktop_controller {

    public function index()
    {
        return $this->finder('syspromotion_mdl_voucher',array(
            'actions'=>array(
                array(
                    'label'=>app::get('syspromotion')->_('新增购物券'),
                    'target'=>'dialog::{ title:\''.app::get('syspromotion')->_('新增购物券').'\', width:920, height:600}',
                    'href'=>'?app=syspromotion&ctl=admin_voucher&act=edit',
                ),
            ),
            'title' => app::get('syspromotion')->_('购物券列表'),
            'use_buildin_delete' => false,
        ));
    }

    public function edit()
    {
        $pagedata = $this->__publicData();

        if( input::get('voucher_id') )
        {
            $apiParams['voucher_id'] = input::get('voucher_id');
            $apiParams['fields'] = '*';
            $ruleInfo = app::get('syspromotion')->rpcCall('promotion.voucher.get', $apiParams);
            $pagedata['ruleInfo'] = $ruleInfo;

            $shopType = explode(',',$ruleInfo['used_platform']);
            foreach($shopType as $v)
            {
                if($pagedata['platform'][$v])
                {
                    $pagedata['platform'][$v]['checked'] = true;
                }
            }

            $shopType = explode(',',$ruleInfo['shoptype']);
            foreach($shopType as $v)
            {
                if($pagedata['shoptype'][$v])
                {
                    $pagedata['shoptype'][$v]['checked'] = true;
                }
            }

            $catData = explode(',',$ruleInfo['limit_cat']);
            foreach($catData as $id)
            {
                if($pagedata['cat'][$id])
                {
                    $pagedata['cat'][$id]['checked'] = true;
                }
            }

            $validGrade = explode(',',$ruleInfo['valid_grade']);
            $pagedata['valid_grade'] = array_bind_key($pagedata['valid_grade'],'grade_id');
            foreach($validGrade as $id)
            {
                if($pagedata['valid_grade'][$id])
                {
                    $pagedata['valid_grade'][$id]['checked'] = true;
                }
            }

            if( input::get('stop') && $ruleInfo['valid_status'] )
            {
                return $this->page('syspromotion/voucher/stop.html', $pagedata);
            }
            elseif( $ruleInfo['apply_begin_time'] <= time() )
            {
                return $this->page('syspromotion/voucher/info.html', $pagedata);
            }
        }

        return $this->page('syspromotion/voucher/edit.html',$pagedata);
    }

    private function __publicData()
    {
        // 获取店铺类型
        $shopType = app::get('syspromotion')->rpcCall('shop.type.get');
        $shopType['self'] = array(
            'shop_type' => 'self',
            'name' => '运营商自营',
        );
        $pagedata['shoptype'] = $shopType;

        //获取类目
        $cat = app::get('syspromotion')->rpcCall('category.cat.get.info',array('level' =>1));
        $pagedata['cat'] = $cat;

        //获取会员等级
        $pagedata['valid_grade'] = app::get('syspromotion')->rpcCall('user.grade.list');

        $pagedata['platform'] = array(
            'pc'  => ['name' => '电脑端'],
            'wap' => ['name' => 'H5端'],
            'app' => ['name' => 'APP端'],
        );

        return $pagedata;
    }

    public function save()
    {
        $this->begin();
        $data = input::get();

        if( $data['ruledata']['valid_status'] )
        {
            try
            {
                app::get('syspromotion')->rpcCall('promotion.voucher.stop', ['voucher_id'=>$data['ruledata']['voucher_id']]);
            }
            catch(Exception $e)
            {
                $this->end(false,$e->getMessage());
            }
        }
        else
        {
            $apiParams = $data['ruledata'];

            //处理时间参数
            $H = $data['_DTIME_']['H'];
            $M = $data['_DTIME_']['M'];
            foreach($H as $key=>$val)
            {
                $apiParams[$key] = strtotime($data[$key]." ".$val.":".$M[$key]);
            }

            $apiParams['valid_grade'] = implode(',',$apiParams['valid_grade']);
            $apiParams['limit_cat']   = implode(',',$apiParams['limit_cat']);
            $apiParams['shoptype']    = implode(',',$apiParams['shoptype']);
            $apiParams['used_platform'] = implode(',',$apiParams['used_platform']);

            try
            {
                if( $apiParams['voucher_id'] )
                {
                    app::get('syspromotion')->rpcCall('promotion.voucher.update', $apiParams);
                }
                else
                {
                    app::get('syspromotion')->rpcCall('promotion.voucher.add', $apiParams);
                }
            }
            catch(Exception $e)
            {
                $this->end(false,$e->getMessage());
            }
        }

        $this->end(true);
    }
}
