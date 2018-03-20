<?php

/**
 * export.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topshop_item_export {

    protected $allowType = 'xls';
    protected $shopId;
    
    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }
    
    // 导出主方法
    public function export($params)
    {
        $params['fields'] = 'item_id,shop_id,sub_title,bn,barcode,use_platform,show_mkt_price,sub_stock,dlytmpl_id,unit';
        $itemsList = [];
        $isSearch = $params['is_search'];
        unset($params['is_search']);
        // 根据条件调用不同的api
        // 库存报警
        if(isset($params['approve_status']) && $params['approve_status'] == 'oversku')
        {
            // 搜索模式下的
            if($isSearch)
            {
                //$params['shop_id'] = $this->shopId;
                $storeParams['shop_id'] = $params['shop_id'];
                $storeParams['fields'] = 'policevalue';
                $storePolice = app::get('topshop')->rpcCall('item.store.info',$storeParams);
                $params['store'] = $storePolice['policevalue']?$storePolice['policevalue']:0;
                $itemsList = app::get('topshop')->rpcCall('search.item.oversku',$params);
            }
            else
            {
                $storeParams['fields'] = 'policevalue';
                $storeParams['shop_id'] = $params['shop_id'];
                $storePolice = app::get('topshop')->rpcCall('item.store.info',$params);
                $params['store'] = $storePolice['policevalue']?$storePolice['policevalue']:0;
                $itemsList = app::get('topshop')->rpcCall('item.store.police', $params);
            }
            
            
        }
        else
        {
            $itemsList = app::get('topshop')->rpcCall('item.search',$params);
        }
        
        $itemsList = $this->formatExportItemList($itemsList['list']);
       
        // 开始导出
        $objWriter = kernel::single('importexport_type_writer');
        $objWriter->writeDocument($itemsList);
        $objWriter->saveDocument();
        $fileName = 'export-item-'.$params['page_no'].'-'.time();
        $objWriter->downDocument($fileName, $this->allowType);
        
        return true;
    }
    
    /**
     * 获取导出列头
     * */
    protected function getExportTitle()
    {
        return [
                'cat_name_l1' => app::get('topshop')->_('一级类目'),
                'cat_name_l2' => app::get('topshop')->_('二级类目'),
                'cat_name_l3' => app::get('topshop')->_('三级类目'),
                'shop_CatName'=> app::get('topshop')->_('店铺分类'),
                'title' => app::get('topshop')->_('商品标题'),
                'sub_title' => app::get('topshop')->_('商品副标题'),
                'brand_name' => app::get('topshop')->_('品牌'),
                'bn' => app::get('topshop')->_('商品货号'),
                'barcode' => app::get('topshop')->_('条形码'),
                'platfrom' => app::get('topshop')->_('发布平台'),
                'price' => app::get('topshop')->_('销售价'),
                'realStore' => app::get('topshop')->_('库存'),
                'sub_stock_store' => app::get('topshop')->_('库存计数'),
                'mkt_price' => app::get('topshop')->_('原价'),
                'is_show_mkt_price' => app::get('topshop')->_('是否显示原价'),
                'cost_price' => app::get('topshop')->_('成本价'),
                'weight' => app::get('topshop')->_('重量'),
                'unit' => app::get('topshop')->_('计价单位'),
                'dlytmpl_name' => app::get('topshop')->_('运费模板'),
                'spec_info' => app::get('topshop')->_('规格值'),
        ];
    }
    
    /**
     * 处理要导出的商品数据
     * 
     * @param array $itemList 商品数据
     * @return array $result 整理好的数据
     * */
    protected function formatExportItemList($itemList)
    {
        
        $result = [];
        $tmp = [];
        
        // 获取商品规格，导出时同一商品的不同规格独占一行
        $itemIds = array_column($itemList, 'item_id');
        $tmp = array_bind_key($itemList, 'item_id');
        $skuParams['item_id'] = implode(',', $itemIds);
        $skuParams['fields'] = 'sku_id,item_id,title,bn,price,cost_price,mkt_price,barcode,weight,spec_info,cat_id,brand_id,shop_cat_id,store';
        $skuParams['page_size'] = 1000;
        $itemsSku = app::get('topshop')->rpcCall('sku.search', $skuParams);
       
        $itemTmps = [];
        foreach ($itemsSku['list'] as $sku)
        {
            unset($sku['sku_id']);
            $item = $tmp[$sku['item_id']];
            foreach ($item as $k=>$v)
            {
                if(! array_key_exists($k, $sku))
                {
                    $sku[$k] = $v;
                }
            }
            
            $itemTmps[] = $sku;
        }
        
       // 获取商品的所属分类和品牌及快递模板
        $itemTmps = $this->compositeData($itemTmps, $blendData);
        
        return $itemTmps;
    }
    
    /**
     * 组合数据
     * 
     * @param array $itemList
     * @param array $blendData
     * @return array
     * */
    protected function compositeData($itemTmps, $blendData)
    {
        // 获取商品的所属分类和品牌及快递模板
        $blendData = $this->getItemCatAndBrand($itemTmps);
        $notice = app::get('topshop')->_('无');
        foreach ($itemTmps as &$val)
        {
            // 获取快递模板
            $val['dlytmpl_name'] = $notice;
            if($blendData['dly'][$val['dlytmpl_id']]['name'])
            {
                $val['dlytmpl_name'] = $blendData['dly'][$val['dlytmpl_id']]['name'];
            }
            unset($val['dlytmpl_id']);
        
            // 获取品牌
            $val['brand_name'] = $notice;
            if($blendData['brand'][$val['brand_id']]['brand_name'])
            {
                $val['brand_name'] = $blendData['brand'][$val['brand_id']]['brand_name'];
            }
            unset($val['brand_id']);
        
            // 获取商家分类
            $val['shop_CatName'] = '';
            $shopCatid = explode(',', trim($val['shop_cat_id'],','));
            foreach ($shopCatid as $shopCatVal) {
                if($blendData['shopCat'][$shopCatVal]['cat_name'])
                {
                    $val['shop_CatName'] .= $blendData['shopCat'][$shopCatVal]['cat_name'].'|';
                }
            }
            $val['shop_CatName'] = trim($val['shop_CatName'],'|');
            
            unset($val['shop_cat_id']);
        
            // 获取商品分类
            $val['cat_name_l1'] = $val['cat_name_l2'] = $val['cat_name_l3'] = $notice;
            foreach ($blendData['cat'] as $catval)
            {
                $catval  = array_bind_key($catval, 'cat_id');
                if(array_key_exists($val['cat_id'], $catval))
                {
                    foreach ($catval as $cv)
                    {
                        if($cv['level'] == 1) $val['cat_name_l1'] = $cv['cat_name'];
                        if($cv['level'] == 2) $val['cat_name_l2'] = $cv['cat_name'];
                        if($cv['level'] == 3) $val['cat_name_l3'] = $cv['cat_name'];
                    }
                }
            }
            unset($val['cat_id']);
        
            // 处理发布端
            $use_platform = [
                0 => app::get('topshop')->_('全部'),
                1 => app::get('topshop')->_('pc端'),
                2 => app::get('topshop')->_('wap端'),
            ];
            $val['platfrom'] = $use_platform[$val['use_platform']];
            unset($val['use_platform']);
        
            // 是否显示原价
            $val['is_show_mkt_price'] = app::get('topshop')->_('否');
            if($val['show_mkt_price'])
            {
                $val['is_show_mkt_price'] = app::get('topshop')->_('是');
            }
            unset($val['show_mkt_price']);
        
            // 减库存类型
            $sub_stock = [
                0 => app::get('sysitem')->_('付款减库存'),
                1 => app::get('sysitem')->_('下单减库存'),
            ];
            $val['sub_stock_store'] = $sub_stock[$val['sub_stock']];
            unset($val['sub_stock']);
            unset($val['item_id']);
            unset($val['shop_id']);
            unset($val['store']);
            unset($val['freez']);
        
        }
        
        // 加入表头
        $tableHeader = $this->getExportTitle();
        $result = [];
        $row = [];
         foreach ($itemTmps as $itemTmp)
        {
            foreach ($tableHeader as $col=>$text)
            {
                $row[$col] = $itemTmp[$col];
            }
            
            $result[] = $row;
        } 
        
        array_unshift($result, $tableHeader);
        
        return $result;
    }
    /**
     * 获取商品的所属分类和品牌
     * 
     * @param array $itemList 商品列表
     * @return array $result 返回结果
     * */
    protected function getItemCatAndBrand($itemList)
    {
        // 查询条件
        $catIds = array_unique(array_column($itemList, 'cat_id'));
        $brandIds = array_unique(array_column($itemList, 'brand_id'));
        $shopCatIds = array_unique(array_column($itemList, 'shop_cat_id'));
        $shopCatIdsTmp = [];
        foreach ($shopCatIds as $val)
        {
            $shopCatId = explode(',', trim($val, ','));
            $shopCatIdsTmp[] = $shopCatId[count($shopCatId)-1]; 
        }
        
        // 获取品牌列表
        $brandParams['brand_id'] = implode(',', $brandIds);
        $brandParams['fields'] = '	brand_id,brand_name';
        $brandList = app::get('topshop')->rpcCall('category.brand.get.list', $brandParams);
        $result['brand'] = $brandList;
        
        // 获取商家分类
        $shopCatParams['shop_id'] = $this->shopId;
        $shopCatParams['cat_id'] = implode(',', $shopCatIdsTmp);
        $shopCatParams['fields'] = 'cat_id,cat_name';
        $shopCatList = app::get('topshop')->rpcCall('shop.cat.get', $shopCatParams);
        $result['shopCat'] = $shopCatList;
        
        // 获取商品分类
        $catParams['cat_id'] = implode(',', $catIds);
        $catParams['fields'] = 'cat_id,cat_name';
        $catList = app::get('topshop')->rpcCall('category.cat.get.data', $catParams);
        $result['cat'] = $catList;
        if(count($catIds) < 2)
        {
            $result['cat'][] = $catList;
        }
        
        // 快递模板
        $dlytmpl_ids = array_column($itemList, 'dlytmpl_id');
        $dlyParams['dlytmpl_ids'] = implode(',', array_unique($dlytmpl_ids));
        $dlyParams['shop_id'] = $this->shopId;
        $dlyList = [];
        $dlyList = app::get('topshop')->rpcCall('logistics.dlytmpl.get.list', $dlyParams);
        if($dlyList)
        {
            $dlyList = array_bind_key($dlyList['data'], 'template_id');
        }
        $result['dly'] = $dlyList;
        
        return $result;
    }
    
}
 