<?php
/**
 * ShopEx licence
 * 计算商品的搜索权重
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysitem_tasks_searchweight extends base_task_abstract implements base_interface_task{

    // 各个大权重项的比例配置
    var $rule;

    function __construct(){
        $this->objItem = app::get('sysitem')->model('item');
        $this->objSkuStore = app::get('sysitem')->model('sku_store');
        $this->objItemCount = app::get('sysitem')->model('item_count');
        $this->objItemStatus = app::get('sysitem')->model('item_status');
        $this->objItemSearchWeight = app::get('sysitem')->model('item_search_weight');
        $this->objItemSearchShopWeight = app::get('sysitem')->model('item_search_shopweight');

        $this->setRule();
    }

    /**
     * 初始化权重比例
     */
    public function setRule()
    {
        $defaultRuleId = app::get('sysitem')->getConf('search_weight_rule');
        $ruleInfo = app::get('sysitem')->model('item_search_rule')->getRow('*', ['rule_id'=>$defaultRuleId]);
        $this->rule = json_decode($ruleInfo['rule'], 1);
    }


    // 每个队列执行100条订单信息
    var $limit = 100;
    public function exec($params=null)
    {
        logger::info('执行商品搜索权重计算任务开始！');
        $offset = 0;
        while( $data = $this->__itemIdsList($offset) ){
            $offset++;
            // 把分页得到的Id加入相关队列
            $this->__calcItemSearchWeight($data);
        }
        logger::info('执行商品搜索权重计算任务结束！');
    }

    /**
     * 分页获取商品ID
     * @param  int $offset 页数
     * @return bool        返回商品ID
     */
    private function __itemIdsList($offset)
    {
        $itemIds = app::get('sysitem')->model('item')->getList('item_id', '', $offset*$this->limit, $this->limit, ' item_id ASC ');
        return $itemIds;
    }

    //执行
    private function __calcItemSearchWeight($data)
    {
        foreach ($data as $v)
        {
            $weight = 0;
            $itemId = $v['item_id'];

            $item = app::get('sysitem')->model('item')->simpleGetList('list_image, shop_id, created_time', ['item_id'=>$itemId]);
            $goods_quality     = $this->__goods_quality($itemId, $item['0']);
            $shop              = $this->__shop($item['0']['shop_id']);
            $goods_updown      = $this->__goods_updown($itemId, $item['0']);
            $goods_maintenance = $this->__goods_maintenance($itemId);

            $weight = ($goods_quality * $this->rule['goods_quality']
                    + $shop * $this->rule['shop']
                    + $goods_updown * $this->rule['goods_updown']
                    + $goods_maintenance * $this->rule['goods_maintenance'])/100;

            $data = ['item_id'=>$itemId, 'default_weight'=>$weight];
            $flag = $this->objItemSearchWeight->save($data);
        }
        return true;
    }

    /**
     * 商品质量权重
     * 
     * 图片5张以内，每张X分，超过5张算5张
     * 销量600以内，每个X分，超过600算600个销量
     * 一个好评5分，一个中评3分，一个差评1分，好中差评加起来不超过200分
     * @param  int $itemId 商品ID
     * @param  array &$item  商品信息
     * @return int         权重
     */
    public function __goods_quality($itemId, &$item)
    {
        $weight = 0;

        // 图片权重
        $picnum = count(explode(',', $item['list_image']));
        $picnum = $picnum > 5 ? 5 : $picnum;
        $weight += $picnum * config::get('searchweight.goods_quality.every_pic');

        // 销量权重
        $item_count   = $this->objItemCount->getRow('sold_quantity, rate_count', ['item_id'=>$itemId]);
        $soldQuantity = $item_count['sold_quantity'] > 600 ? 600 : $item_count['sold_quantity'];
        $weight += ( $soldQuantity * config::get('searchweight.goods_quality.every_sold') );

        // 评分权重
        $countRateData = app::get('topc')->rpcCall('item.get.count',array('item_id'=>$itemId, 'fields'=>'item_id,rate_count,rate_good_count,rate_neutral_count,rate_bad_count'));
        if( $countRateData[$itemId]['rate_count'] )
        {
            $good      = $countRateData[$itemId]['rate_good_count'];
            $neutral   = $countRateData[$itemId]['rate_neutral_count'];
            $bad       = $countRateData[$itemId]['rate_bad_count'];
            $tmpWeight = $good    * config::get('searchweight.goods_quality.every_rate_good')
                       + $neutral * config::get('searchweight.goods_quality.every_rate_neutral')
                       + $bad     * config::get('searchweight.goods_quality.every_rate_bad');
            $weight += $tmpWeight;
        }
        $weight = ($weight < 0) ? 0 : $weight;
        return $weight;
    }

    /**
     * 商品上下架权重
     * 
     * 30天内新添加的商品给予1000分
     * 30天前新加，30天内重新上架的500分
     * @param  int $itemId 商品id
     * @param  array &$item  部分商品信息
     * @return int         权重分
     */
    public function __goods_updown($itemId, &$item)
    {
        $weight = 0;
        $statusInfo = $this->objItemStatus->getRow('list_time', ['item_id'=>$itemId]);
        $isnew = (time() - $item['created_time']) < 30*3600*24;
        if($isnew)
        {
            $weight = config::get('searchweight.goods_updown.new');
        }
        else
        {
            if( time() - $statusInfo['list_time'] < 30*3600*24 )
            {
                $weight = config::get('searchweight.goods_updown.relist');
            }
        }
        $weight = ($weight < 0) ? 0 : $weight;
        return $weight;
    }

    /**
     * 商品维护权重
     *
     * 缺货一个SKU减X分，直到为0
     * 30天内，售后次数超过5次后，每个售后扣X分，直到为0
     * @param  int $itemId 商品ID
     * @return int         权重分
     */
    private function __goods_maintenance($itemId)
    {
        // 缺货权重
        $weight = 0;
        $noSkuNum = 0;
        $skuStore = $this->objSkuStore->getList('*', ['item_id'=>$itemId]);
        foreach ($skuStore as $v) {
            ($v['store'] == $v['freez']) ? $noSkuNum++ : 0;
        }
        $weight += $noSkuNum * config::get('searchweight.goods_maintenance.every_nosku');

        // 售后权重
        $item_count   = $this->objItemCount->getRow('aftersales_month_count', ['item_id'=>$itemId]);
        $afterSalesQuantity = $item_count['aftersales_month_count'] <= 5 ? 0 : $item_count['aftersales_month_count']-5;
        $weight += $afterSalesQuantity * config::get('searchweight.goods_maintenance.every_artersales_num');

        $weight = (1000 + $weight < 0) ? 0 : (1000 + $weight);
        return $weight;
    }

    /**
     * 店铺权重
     * 
     * 权重有两个，店铺类型有固定基础分，店铺也可以有平台自定义调的加分
     * @param  int $shopId 店铺ID
     * @return int         权重
     */
    public function __shop($shopId)
    {
        $weight = $shopCustomWeight = 0;
        $shopTypeInfo = rpc::call('shop.get', ['shop_id'=>$shopId, 'fields'=>'shop_type']);
        $shopCustomWeightInfo = $this->objItemSearchShopWeight->getRow('*', ['shop_id'=>$shopId]);
        if($shopCustomWeightInfo)
        {
            $shopCustomWeight = $shopCustomWeightInfo['custom_weight'];
        }

        $shopTypeWeight = config::get('searchweight.shop.shoptype_'.$shopTypeInfo['shop_type']);
        $weight += $shopTypeWeight + $shopCustomWeight;
        $weight = ($weight < 0) ? 0 : $weight;
        return $weight;
    }

}