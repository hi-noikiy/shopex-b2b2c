<?php

/**
 * @brief 商家商品管理
 */
class topshop_ctl_open extends topshop_controller {

    public function index()
    {
        $shopId = $this->shopId;
        $this->contentHeaderTitle = app::get('topshop')->_('开发者中心');

        $pagedata['isOpen'] = app::get('topshop')->rpcCall('open.shop.isopen', []);
        if( $pagedata['isOpen']['shopexmatrixEnable'] )
        {
            $pagedata['show_bind_url'] = app::get('topshop')->rpcCall('open.shop.show.bind', ['shop_id'=>$this->shopId]);

            $pagedata['node'] = app::get('topshop')->rpcCall('open.shop.node.get', ['shop_id'=>$this->shopId]);
            $pagedata['bind_list'] = app::get('topshop')->rpcCall('open.shop.shopex.bind.list', ['shop_id'=>$this->shopId]);
            $pagedata['bind_status'] = [
                'unbind'=> '未绑定',
                'wait' => '等待对方同意',
                'bind' => '已绑定',
                'binded'=> '拒绝绑定',
            ];
        }

        $requestParams = ['shop_id'=>$shopId];
        $openInfo = app::get('topshop')->rpcCall('open.shop.develop.info', $requestParams);
        $shopConf = app::get('topshop')->rpcCall('open.shop.develop.conf', $requestParams);
        $pagedata['openInfo'] = $openInfo;
        $pagedata['shopConf'] = $shopConf;

        return $this->page('topshop/open/index.html', $pagedata);
    }

    public function createShopexNode()
    {
        try
        {
            app::get('topshop')->rpcCall('open.shop.apply.node', ['shop_id'=>$this->shopId]);
        }
        catch( Exception $e )
        {
        }
        return redirect::action('topshop_ctl_open@index')->send();
    }

    //申请绑定shopex产品
    public function applyBindShopexProduct()
    {
        try
        {
            $filter['shop_name']  = input::get('shop_name');
            $filter['to_node_id'] = input::get('to_node_id');
            $filter['node_type']  = input::get('shopex_product_type');
            $filter['shop_id']    = $this->shopId;
            app::get('topshop')->rpcCall('open.shop.apply.bind',$filter);
        }
        catch( Exception $e )
        {
            return $this->splash('error','',$e->getMessage(),true);
        }

        $url = url::action('topshop_ctl_open@index');

        return $this->splash('success',$url,'申请绑定成功，等待同意',true);
    }

    public function applyForOpen()
    {
        $url = url::action('topshop_ctl_open@index');
        $shopId = $this->shopId;
        $requestParams = [
            'shop_id'=>$shopId,
            'key' => input::get('key'),
            'secret' => input::get('secret'),
        ];
        try
        {
            $res = app::get('topshop')->rpcCall('open.shop.develop.apply', $requestParams);
        }
        catch( Exception $e )
        {
            return $this->splash('error',$url, $e->getMessage(),true);
        }

        $this->sellerlog('申请绑定开发者');
        return $this->splash('success',$url,'申请成功，等待审核',true);
    }

    public function setConf()
    {
        $shopId = $this->shopId;
        $confs = input::get();

        try
        {
            $requestParams = [
                'shop_id' => $shopId,
                'developMode' => $confs['developer'] ? $confs['developer'] : 'PRODUCT',
                ];
            app::get('topshop')->rpcCall('open.shop.develop.setConf', $requestParams);
        }
        catch(Exception $e)
        {
            return $this->splash('error',$url,$e->getMessage(),true);
        }
        $this->sellerlog('开发者中心商家参数配置保存');
        return $this->splash('success',$url,'修改成功',true);
    }

}


