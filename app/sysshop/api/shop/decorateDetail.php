<?php
class sysshop_api_shop_decorateDetail{

    public $apiDescription = "获取店铺装修详情";
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int','valid'=>'required','description'=>'店铺id'],
        );
        return $return;
    }
    public function get($params)
    {
        $shopId = $params['shop_id'];
        $pagedata = $this->__common($shopId);

        //店铺关闭后跳转至关闭页面
        if($pagedata['shopdata']['status'] == "dead")
        {
            return $pagedata;
        }

        // 店铺分类
        $shopcat = app::get('sysshop')->rpcCall('shop.cat.get',array('shop_id'=>$shopId,'parent_id'=>0));
        $pagedata['shopcat'] = array_values($shopcat);//自然索引

        return $pagedata;
    }

    /**
     * 获取店铺模板页面头部共用部分的数据
     *
     * @param int $shopId 店铺ID
     * @return array
     */
    private function __common($shopId)
    {
        $shopId = intval($shopId);

        //店铺信息
        $shopdata = app::get('sysshop')->rpcCall('shop.get',array('shop_id'=>$shopId));
        if($shopdata['status'] == "dead")
        {
            return ['shopdata'=>$shopdata, 'logo_image'=>$commonData['logo_image']];
        }
        // 处理图片链接
        $shopdata['shop_logo'] = base_storager::modifier($shopdata['shop_logo'], 't');
        // h5端链接
        $shopdata['h5href'] = url::action('topwap_ctl_shop@index', ['shop_id'=>$shopId]);
        $commonData['shopdata'] = $shopdata;

        //店铺评分信息
        $params['shop_id'] = $shopId;
        $params['catDsrDiff'] = false;
        $params['countNum'] = false;
        $dsrData = app::get('sysshop')->rpcCall('rate.dsr.get', $params);
        if( !$dsrData )
        {
            $commonData['tally_dsr'] = sprintf('%.1f',5.0);
            $commonData['attitude_dsr'] = sprintf('%.1f',5.0);
            $commonData['delivery_speed_dsr'] = sprintf('%.1f',5.0);
        }
        else
        {
            $commonData['tally_dsr'] = sprintf('%.1f',$dsrData['tally_dsr']);
            $commonData['attitude_dsr'] = sprintf('%.1f',$dsrData['attitude_dsr']);
            $commonData['delivery_speed_dsr'] = sprintf('%.1f',$dsrData['delivery_speed_dsr']);
        }

        //widgets信息
        $apiParams = [
            'shop_id'    => $shopId,
            'page_name'  => 'index',
            'platform'   => 'app',
        ];
        $commonData['widgets'] = app::get('sysshop')->rpcCall('sysdecorate.widgets.get', $apiParams);

        return $commonData;
    }
}
