<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topshop_ctl_shop_appDecorate extends topshop_controller {

    public function index()
    {
        $pagedata['debugStatus'] = config::get('app.debug');
        return $this->page('topshop/shop/new_decorate.html', $pagedata);
    }

    public function edit()
    {
        $platform = input::get('type','app');
        $pageName = input::get('page','index');

        $shopdata = app::get('topshop')->rpcCall('shop.get',array('shop_id'=>shopAuth::getShopId()));
        $pagedata['shop'] = $shopdata;

        $apiParams = [
            'shop_id'    => $this->shopId,
            'page_name'  => $pageName,
            'platform'   => $platform,
        ];

        $data = app::get('topshop')->rpcCall('sysdecorate.widgets.get', $apiParams);
        if( $data )
        {
            $decorate = $this->getDecorateInfo($data);
            $pagedata['decorate'] = json_encode($decorate);
        }
        return view::make('topshop/shop/decorate/'.$platform.'/'.$pageName.'.html', $pagedata);
    }

    public function save()
    {
        $data = json_decode(input::get('decorate'), true);

        $orderSort = 0;
        $pageName = input::get('page','index');
        $platform = input::get('type', 'app');

        $widgetsIds = array_column($data, 'widgets_id');
        if( $widgetsIds )
        {
            //清除已经删除的widgetsId
            app::get('topshop')->rpcCall('sysdecorate.widgets.delete', [
                'shop_id'    => $this->shopId,
                'page_name'  => $pageName,
                'platform'   => $platform,
                'exclude_widgetsIds' => implode(',',$widgetsIds),
            ]);
        }else{
            app::get('topshop')->rpcCall('sysdecorate.widgets.clean', [
                'shop_id'    => $this->shopId,
                'page_name'  => $pageName,
                'platform'   => $platform,
            ]);
        }

        foreach( $data as $widgetsName=>$row )
        {
            $apiParams = [];
            $apiParams = [
                'shop_id'    => $this->shopId,
                'page_name'  => $pageName,
                'platform'   => $platform,
                'order_sort' => $orderSort,
            ];

            if( $row['widgets_id'] )
            {
                $apiParams['widgets_id'] = $row['widgets_id'];
            }

            switch( $row['widget_name'] )
            {
            case 'shopsign':
                $apiParams['imgurl'] = $row['imgurl'];
                try
                {
                    app::get('topshop')->rpcCall('sysdecorate.shopsign.add', $apiParams);
                }
                catch(Exception $e)
                {
                    return $this->splash('error', '', $e->getMessage(),true);
                }
                break;
            case 'nav':
                if( $row['list'] )
                {
                    $list = [];
                    foreach( $row['list'] as $key=>$list )
                    {
                        if( $list['name'] )
                        {
                            $row['list'][$key]['item_ids'] = implode(',', $list['item_ids']);
                        }
                    }
                    $apiParams['list'] = json_encode($row['list']);
                }
                try
                {
                    app::get('topshop')->rpcCall('sysdecorate.nav.add', $apiParams);
                }
                catch(Exception $e)
                {
                    return $this->splash('error', '', $e->getMessage(),true);
                }
                break;
            case 'oneimg':
                $apiParams['imgurl']   = base_storager::modifier($row['imgurl']);
                $apiParams['imglink']  = $row['imglink'];
                try
                {
                    app::get('topshop')->rpcCall('sysdecorate.oneimg.add', $apiParams);
                }
                catch(Exception $e)
                {
                    return $this->splash('error', '', $e->getMessage(),true);
                }
                break;
            case 'goods':
                $apiParams['title']   = $row['title'];
                $itemIds = array_column($row['list'], 'item_id');
                $apiParams['item_id'] = implode(',' ,$itemIds);
                try
                {
                    app::get('topshop')->rpcCall('sysdecorate.goods.add', $apiParams);
                }
                catch(Exception $e)
                {
                    return $this->splash('error', '', $e->getMessage(),true);
                }
                break;
            case 'slider':
                $list = [];
                foreach($row['list'] as $row)
                {
                    $val['imgurl']  = $row['imgurl'];
                    $val['imglink'] = $row['imglink'];
                    $list[] = $val;
                }
                $apiParams['list'] = json_encode($list);
                try
                {
                    app::get('topshop')->rpcCall('sysdecorate.slider.add', $apiParams);
                }
                catch(Exception $e)
                {
                    return $this->splash('error', '', $e->getMessage(),true);
                }
                break;
            default:
                $apiParams['list'] = json_encode($row['list']);
                try
                {
                    $apiName = 'sysdecorate.'.$row['widget_name'].'.add';
                    app::get('topshop')->rpcCall($apiName, $apiParams);
                }
                catch(Exception $e)
                {
                    return $this->splash('error', '', $e->getMessage(),true);
                }
                break;
            }
            $orderSort++;
        }

        //返回前端数据重新填充页面数据
        $apiParams = [
            'shop_id'    => $this->shopId,
            'page_name'  => $pageName,
            'platform'   => $platform,
        ];
        $data = app::get('topshop')->rpcCall('sysdecorate.widgets.get', $apiParams);
        $decorate = $this->getDecorateInfo($data);
        $wData= json_encode($decorate);

        return $this->splash('success', $wData, $msg,true);
    }

    //格式化decorateData
    private function getDecorateInfo($data){
        $decorateKey = 0;
        $decorate = [];
        foreach( $data as $row )
        {
            $wid = $row['widgets_type'].'_'.$row['widgets_id'];
            $decorate[$decorateKey] = [
                'wid'         => $wid,//前端遍历json使用
                'widget_name' => $row['widgets_type'],
                'widgets_id'  => $row['widgets_id'],
            ];
            switch( $row['widgets_type'] )
            {
            case 'nav':
                foreach( $row['params'] as $k =>$v ){
                   if($v['type'] == 'goods'){
                        $row['params'][$k]['item_ids'] = explode(',', $v['item_ids']);
                   }
                }
                $decorate[$decorateKey]['list'] = $row['params'];
                break;
            case 'shopsign':
                unset($decorate[$decorateKey]);
                $wid = $row['widgets_type'];
                $decorate[$decorateKey]['wid']        = $wid;
                $decorate[$decorateKey]['widgets_id'] = $row['widgets_id'];
                $decorate[$decorateKey]['widget_name'] = $row['widgets_type'];
                $decorate[$decorateKey]['imgurl']     = $row['params']['imgurl'];
                $decorate[$decorateKey]['imgsrc']     = base_storager::modifier($row['params']['imgurl']);
                break;
            case 'oneimg':
                $decorate[$decorateKey]['imgurl'] = $row['params']['imgurl'];
                $decorate[$decorateKey]['imgsrc'] = base_storager::modifier($row['params']['imgurl']);
                $decorate[$decorateKey]['imglink'] = $row['params']['imglink'];
                break;
            case 'slider':
                foreach( $row['params'] as $key=>$val )
                {
                    $row['params'][$key]['imgsrc'] = base_storager::modifier($val['imgurl']);
                }
                $decorate[$decorateKey]['list'] = $row['params'];
                break;
            case 'goods':
                $itemData = app::get('topshop')->rpcCall('sysdecorate.goods.data.get', ['shop_id'=>$this->shopId, 'widgets_id'=>$row['widgets_id'], 'showItemNum'=>8]);
                $list = [];
                foreach($itemData as $itemRow )
                {
                    $list[] = [
                        'item_id'    => $itemRow['item_id'],
                        'goodstitle' => $itemRow['title'],
                        'goodslink'  => url::action('topwap_ctl_item_detail@index', ['item_id'=>$itemRow['item_id']]),
                        'imgurl'     => base_storager::modifier($itemRow['image_default_id'],'s'),
                        'price'      => $itemRow['price'],
                        'soldnum'    => 'x',
                    ];
                }
                $decorate[$decorateKey]['title'] = $row['params']['title'];
                $decorate[$decorateKey]['list']  = $list;
                break;
            default:
                $decorate[$decorateKey]['list'] = $row['params'];
                break;
            }
            $decorateKey++;
        }

        return $decorate;
    }
}

