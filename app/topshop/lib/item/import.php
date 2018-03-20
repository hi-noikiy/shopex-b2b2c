<?php

/**
 * import.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topshop_item_import {

    protected $shopId;
    protected $allowType = 'xls';
    protected $allowLimit = 30;
    
    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }
    /**
     * 导入文件
     * 
     * @param array $fileInfo 文件信息
     * @return bool
     * */
    public function import($fileInfo)
    {
        $this->checkFile($fileInfo['name']);
        // 读取文件
        $data = $this->readDocument($fileInfo);
        // 验证数据的合法性
        $formatData = $this->checkData($data);
        
        foreach ($formatData as $val)
        {
            $notice = $val['item_notice_msg'];
            unset($val['item_notice_msg']);
            try {
                app::get('topshop')->rpcCall('item.create', $val);
            } catch (Exception $e) {
                throw new LogicException(sprintf($notice, $e->getMessage()));
            }
        }
        
        return true;
    }
    
    /**
     * 验证数据
     * 
     * @param array $data 文档数据
     * @return bool
     * */
    protected function checkData($data)
    {
        $rowsData = $data['list'];
        $rowCount = $data['row'];
        $colCount = $data['col'];
        unset($rowsData[0]);
       
       // 验证商家分类
       $catId = $colsTitle = null;
       list($catId, $colsTitle) = $this->checkCat($rowsData, $colCount);
  
       // 组合商品数据
       $item = [];
       $items = [];
       foreach ($rowsData as $row)
       {
           $keys = array_keys($colsTitle);
           $values = array_values($row);
           $item = array_combine($keys, $values);
           $items[] = $item;
       }
       
       $colsRule = $this->getImportTitle(true);
       foreach ($items as $r=>$item)
       {
           foreach ($item as $hash=>$text)
           {
               if(isset($colsRule[$hash]) && $colsRule[$hash]['required'])
               {
                   $validator = validator::make(
                           ['text' => $text],
                           ['text' => 'required'],
                           ['text' => app::get('topshop')->_('表格第'.($r+2).'行的'.$colsRule[$hash][0].'必填')]
                           );
                   $validator->newFails();
               }
           }
       }
       // 验证商品数据的合法性
       return $this->checkItems($items, $catId, $colsTitle);

    }
    
    // 验证商品数据的合法性
    protected function checkItems($items, $catId, $colsTitle)
    {
        $params['shop_id'] = $this->shopId;
        $params['cat_id'] = $catId;
        $brand = app::get('topshop')->rpcCall('category.get.cat.rel.brand',$params);
        // 获取商家分类
        $shopCatParams['shop_id'] = $this->shopId;
        $shopCatParams['fields'] = 'cat_id,cat_name,parent_id';
        $shopCatList = app::get('topshop')->rpcCall('shop.cat.get', $shopCatParams);
        $shopParentCat = array_bind_key($shopCatList, 'cat_name');
        
        // 快递模板
        $dlyParams['shop_id'] = $this->shopId;
        $dlyParams['fields'] = 'template_id,name';
        $dlyList = [];
        $dlyList = app::get('topshop')->rpcCall('logistics.dlytmpl.get.list', $dlyParams);
        $dlyList = $dlyList['data'];
        // 获取销售属性
        $specProps = app::get('topshop')->rpcCall('category.catprovalue.get',array('cat_id'=>$catId,'type'=>'spec'));
        // 获取自然属性
        $natureProps = app::get('topshop')->rpcCall('category.catprovalue.get',array('cat_id'=>$catId,'type'=>'nature'));
        
        // 根据同一商品标识获取组合数据
        $itemsTmp = [];
        foreach ($items as $key=>$v)
        {
            $same_item = (string)$v['same_item'];
            if($same_item && $same_item !='')
            {
                $itemsTmp[$v['same_item']][] = $v;
            }
            else
            {
                $itemsTmp[$key][] = $v;
            }
             
        }
        
        // 验证同一商品数据的合法性
        $realItems = $realItem = [];
        $notice = '';
        
        foreach ($itemsTmp as $k=>$item)
        {
            // 验证同一商品数据的合法性
            if($k === $item[0]['same_item'])
            {
                $notice = app::get('topshop')->_('商品标识符为'.$k.':%s');
            }
            else
            {
                $notice = app::get('topshop')->_('表格第'.($k+2).'行:%s');
            }
            
            // 验证商品标题
            $itemTitles = array_unique(array_column($item, 'title'));
            if(count($itemTitles) > 1)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('商品标题要相同')));
            }
            $realItem['title'] = mb_substr(implode(',', $itemTitles), 0, 50, 'utf-8');
            
            // 验证商品副标题
            $subTitle = array_unique(array_column($item, 'sub_title'));
            if(count($subTitle) > 1)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('商品副标题要相同')));
            }
            $realItem['sub_title'] = mb_substr(implode(',', $subTitle), 0, 150, 'utf-8');
            
            // 验证商品重量
            $weights = array_unique(array_column($item, 'weight'));
            if(count($weights) > 1)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('商品重量要相同')));
            }
            $weight = implode(',', $weights);
            if(strlen((string)$weight) > 13)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('商品重量格式错误')));
            }
            $realItem['weight'] = implode(',', $weights);
            
            // 验证商品计价单位
            $units = array_unique(array_column($item, 'unit'));
            if(count($units) > 1)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('商品计价单位要相同')));
            }
            $unit = implode(',', $units);
            
            if(mb_strlen($unit, 'utf-8') > 3)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('商品计价单位不能超过三个字')));
            }
            $realItem['unit'] = $unit;
            
            // 验证品牌
            $brandArr = array_unique(array_column($item, 'brand_name'));
            if(count($brandArr) > 1)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('商品品牌要相同')));
            }
            $brandName = implode(',', $brandArr);
            
            $brandNames = array_bind_key($brand, 'brand_name');
            if(array_key_exists($brandName, $brandNames))
            {
                $realItem['brand_id'] = $brandNames[$brandName]['brand_id'];
            }
            else
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('商品品牌填写错误')));
            }
            
            // 验证商家分类
            $shopCat = array_unique(array_column($item, 'shop_CatName'));
            if(count($shopCat) > 1)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('商品店铺分类要相同')));
            }
            $shopName = implode(',', $shopCat);
            $shopName = explode(',', $shopName);
            $realItem['shop_cids'] = [];
            foreach ($shopName as $sv)
            {
                $importShopCat = explode('-', $sv);
                if(count($importShopCat) != 2)
                {
                    throw new LogicException(sprintf($notice, app::get('topshop')->_('店铺分类填写错误')));
                }
                
                $parentCat = $importShopCat[0];
                if(array_key_exists($parentCat, $shopParentCat))
                {
                    $shopCatNames = $shopCatList[$shopParentCat[$parentCat]['cat_id']]['children'];
                    $shopCatNames = array_bind_key($shopCatNames, 'cat_name');
                    if(array_key_exists($importShopCat[1], $shopCatNames))
                    {
                        $realItem['shop_cids'][] = $shopCatNames[$importShopCat[1]]['cat_id'];
                    }
                    else
                    {
                        throw new LogicException(sprintf($notice, app::get('topshop')->_('店铺二级分类填写错误')));
                    }
                }
                else
                {
                    throw new LogicException(sprintf($notice, app::get('topshop')->_('店铺一级分类填写错误')));
                }
            }
            
            // 验证发布平台
            if(count(array_unique(array_column($item, 'platfrom'))) > 1)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('发布端填写错误')));
            }
            $platform = '0,1,2';
            $import_form = implode(',', array_unique(array_column($item, 'platfrom')));
            if(strpos($platform, $import_form) === false)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('发布端填写错误')));
            }
            $realItem['use_platform'] = $import_form;
            
            $zeroOne = '0,1';
            $sub_stock_store = array_unique(array_column($item, 'sub_stock_store'));
            // 验证库存计数
            if(count($sub_stock_store) > 1)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('库存计数不一致')));
            }
            
            if(strpos($zeroOne, implode(',', $sub_stock_store)) === false)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('库存计数填写错误')));
            }
            $realItem['sub_stock'] = implode(',', $sub_stock_store);
            
            // 验证是否显示原价
            $is_show_mkt_price = array_unique(array_column($item, 'is_show_mkt_price'));
            if(count($is_show_mkt_price) > 1)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('是否显示原价填写不一致')));
            }
            
            if(strpos($zeroOne, implode(',', $is_show_mkt_price)) === false)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('是否显示原价填写错误')));
            }
            $realItem['show_mkt_price'] = implode(',', $is_show_mkt_price);
            
            // 验证邮费模板
            $dlyName = array_unique(array_column($item, 'dlytmpl_name'));
            if(count($dlyName) > 1)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('运费模板名称不一致')));
            }
            $dlyName = implode(',', $dlyName);
            $dlyNames = array_bind_key($dlyList, 'name');
            if(array_key_exists($dlyName, $dlyNames))
            {
                $realItem['dlytmpl_id'] = $dlyNames[$dlyName]['template_id'];
            }
            else
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('运费模板填写错误')));
            }
            
            // 验证自然属性
            if($natureProps)
            {
                $natCount = count($natureProps);
                $natPropArr = [];
                foreach ($natureProps as $natval)
                {
                    $natPropArr[] = array_bind_key($natval['prop_value'], 'prop_value');
                }
                
                $i = 0;
                foreach ($natureProps as $t)
                {
                    $natValue = array_unique(array_column($item, $i));
                    if(count($natValue) > 1)
                    {
                        throw new LogicException(sprintf($notice, app::get('topshop')->_($colsTitle[$i].'不一致')));
                    }
                    $text = implode(',', $natValue);
                    if(array_key_exists($text, $natPropArr[$i]))
                    {
                        $realItem['nature_props'][$natPropArr[$i][$text]['prop_id']] = $natPropArr[$i][$text]['prop_value_id'];
                    }
                    else
                    {
                        throw new LogicException(sprintf($notice, app::get('topshop')->_($colsTitle[$i].'不一致')));
                    }
                    $i++;
                }
            }
            $realItem['cat_id'] = $catId;
            $realItem['shop_id'] = $this->shopId;
            $realItem['approve_status'] = 'instock';
            $realItem['shop_cat_id'] = ','.implode(',', $realItem['shop_cids']).',';
            $realItem['order_sort'] = 1;
            
            //$realItem['item_id'] = '';
            $result = $this->prosku($specProps, $item, $notice, $realItem);
            // $result['cat_id'] = $catId;
            $result['item_notice_msg'] = $notice;
            $realItems[] = $result;
        }
        
        return $realItems;
    }
    
    // 处理sku
    protected function prosku($specProps, $item, $notice, $realItem)
    {
        $specTmps = [];
        foreach ($specProps as $val)
        {
            $specTmps[] = array_bind_key($val['prop_value'], 'prop_value');
        }
        $skuTmps = array_unique(array_column($item, 'spec_info'));
        $realItem['nospec'] = count($skuTmps[0])>0 ? 0 : 1;
        $postdata = [];
        if(count($item) != count($skuTmps))
        {
            throw new LogicException(sprintf($notice, app::get('topshop')->_('商品规格值错误')));
        }


        $spec_desc = [];
        $skus = $sku = [];
        $spec = [];
        foreach ($item as $itv)
        {
            // 验证价格和库存
            $this->__checkPrice($itv['price'], $itv['mkt_price'], $itv['cost_price'], $notice);
            $this->__checkStore($itv['realStore'], $notice);
            
            $sk = substr(md5(rand(10, 1000)), 0, 10);
            if(!$itv['spec_info'] && count($item) > 1)
            {
                throw new LogicException(sprintf($notice, app::get('topshop')->_('商品规格值错误')));
            }
            
            if ($itv['spec_info'] && $specProps)
            {
                $skuInput = explode('-', $itv['spec_info']);
                if(count($specProps) != count($skuInput))
                {
                    throw new LogicException(sprintf($notice, app::get('topshop')->_('商品规格值错误')));
                }
                
                // 检查导入的规格是否合法
                foreach ($skuInput as $k=>$text)
                {
                    if(array_key_exists($text, $specTmps[$k]))
                    {
                        $spec_desc['spec_value'][$specTmps[$k][$text]['prop_id']] = $text;
                        $spec_desc['spec_value_id'][$specTmps[$k][$text]['prop_id']] = $specTmps[$k][$text]['prop_value_id'];
                
                        $spec[$specTmps[$k][$text]['prop_id']]['spec_name'] = $specProps[$specTmps[$k][$text]['prop_id']]['prop_name'];
                        $spec[$specTmps[$k][$text]['prop_id']]['spec_id'] = $specProps[$specTmps[$k][$text]['prop_id']]['prop_id'];
                        $spec[$specTmps[$k][$text]['prop_id']]['show_type'] = $specProps[$specTmps[$k][$text]['prop_id']]['show_type'];
                        $spec[$specTmps[$k][$text]['prop_id']]['option'][$specTmps[$k][$text]['prop_value_id']]['private_spec_value_id'] = '';
                        $spec[$specTmps[$k][$text]['prop_id']]['option'][$specTmps[$k][$text]['prop_value_id']]['spec_value'] = $text;
                        $spec[$specTmps[$k][$text]['prop_id']]['option'][$specTmps[$k][$text]['prop_value_id']]['spec_value_id'] = $specTmps[$k][$text]['prop_value_id'];
                        if($specProps[$specTmps[$k][$text]['prop_id']]['show_type'] == 'image')
                        {
                            $spec[$specTmps[$k][$text]['prop_id']]['option'][$specTmps[$k][$text]['prop_value_id']]['spec_image'] = $specTmps[$k][$text]['prop_image'];
                            $exten = pathinfo($specTmps[$k][$text]['prop_image'], PATHINFO_EXTENSION);
                            $spec[$specTmps[$k][$text]['prop_id']]['option'][$specTmps[$k][$text]['prop_value_id']]['spec_image_url'] = $specTmps[$k][$text]['prop_image'].'_t.'.$exten;
                            $postdata['images'][$specTmps[$k][$text]['prop_id'].'_'.$specTmps[$k][$text]['prop_value_id']] = $specTmps[$k][$text]['prop_image'];
                        }
                    }
                    else
                    {
                        throw new LogicException(sprintf($notice, app::get('topshop')->_('此分类下没有'.$text.'这个规格')));
                    }
                
                }
                
                $sku['sku_id'] = 'new';
                $sku['spec_desc'] = $spec_desc;
                $sku['price'] = $itv['price'];
                $sku['mkt_price'] = $itv['mkt_price'];
                $sku['cost_price'] = $itv['cost_price'];
                $sku['store'] = $itv['realStore'];
                $sku['bn'] = '';
                if($itv['bn'])
                {
                   $sku['bn'] = mb_substr($itv['bn'], 0, 30, 'utf-8');
                }
                
                $sku['barcode'] = '';
                if($itv['barcode'])
                {
                    $sku['barcode'] = mb_substr($itv['barcode'], 0, 30, 'utf-8');
                }
                $skus[$sk] = $sku;
                

            }
            else
            {
                $skus = [""=>[]];
                $spec = [];
                $realItem['price'] = $itv['price'];
                $realItem['mkt_price'] = $itv['mkt_price'];
                $realItem['cost_price'] = $itv['cost_price'];
                $realItem['store'] = $itv['realStore'];
                $realItem['bn'] = mb_substr($itv['bn'], 0, 30, 'utf-8');
                $realItem['barcode'] = mb_substr($itv['barcode'], 0, 30, 'utf-8');
            }
        }

        // 设置商品sku
        $realItem['sku'] = json_encode($skus);
        $realItem['spec'] = json_encode($spec);
        
        if($spec)
        {
            $price = array_column($skus, 'price');
            sort($price);
            $mtprice = array_column($skus, 'mkt_price');
            sort($mtprice);
            $costprice = array_column($skus, 'cost_price');
            sort($costprice);
            
            $realItem['price'] = $price[0];
            $realItem['mkt_price'] = $mtprice[0];
            $realItem['cost_price'] = $costprice[0];
            $realItem['store'] = array_sum(array_column($skus, 'store'));
        }
        
        // $postdata['item'] = $realItem;
        $postdata = array_merge($postdata, $realItem);
        $specValue = [];
        foreach ($specProps as $specVal)
        {
            foreach ($specVal['prop_value'] as $prop_value)
            {
                $specValue[$prop_value['prop_id'].'_'.$prop_value['prop_value_id']] = $prop_value['prop_value'];
            }
        
        }
        $postdata['spec_value'] = $specValue;
        
        return $postdata;
    }

    private function __checkPrice($price, $mktPrice, $costPrice, $notice)
    {
        // 价格判断
        if($price <0 || strlen($price) > 13)
        {
            $msg = app::get('topshop')->_( '销售价格格式错误' );
            throw new \LogicException(sprintf($notice, $msg));
        }
    
        if($mktPrice <0 || strlen($mktPrice) > 13)
        {
            $msg = app::get('topshop')->_( '原价格格式错误' );
            throw new \LogicException(sprintf($notice, $msg));
        }
    
        if($costPrice <0 || strlen($costPrice) > 13)
        {
            $msg = app::get('topshop')->_( '成本价格格式错误' );
            throw new \LogicException(sprintf($notice, $msg));
        }
    
        return true;
    }
    
    private function __checkStore($store, $notice)
    {
        $msg = app::get('topshop')->_( '库存格式错误' );
        if(strlen((string)$store) > 6)
        {
            throw new \LogicException(sprintf($notice, $msg));
        }
        if((!empty($store) && !is_numeric($store)) || intval($store) < 0)
        {
            throw new \LogicException(sprintf($notice, $msg));
        }
        
        return true;
    }
    
    // 验证类目
    protected function checkCat($data, $colCount)
    {
        // 验证分类的合法性
        $tmp = [];
        foreach ($data as $row)
        {
            $tmp['cat_name_l1'][] = $row[0];
            $tmp['cat_name_l2'][] = $row[1];
            $tmp['cat_name_l3'][] = $row[2];
        }
         
        if(count(array_unique($tmp['cat_name_l1'])) !=1)
        {
            throw new LogicException(app::get('topshop')->_('商品一级类目必须相同'));
        }
         
        if(count(array_unique($tmp['cat_name_l2'])) !=1)
        {
            throw new LogicException(app::get('topshop')->_('商品二级类目必须相同'));
        }
         
        if(count(array_unique($tmp['cat_name_l3'])) !=1)
        {
            throw new LogicException(app::get('topshop')->_('商品三级类目必须相同'));
        }
         
        // 获取三级分类catid
        $lv3CatName = array_unique($tmp['cat_name_l3']);
        $lv3CatName = $lv3CatName[0];
        $lv3Catlist = $this->getLv3Cat();
        $catId = null;
        foreach ($lv3Catlist as $cat)
        {
            if(md5($lv3CatName) == md5($cat['cat_name']))
            {
                $catId = $cat['cat_id'];
            }
        }
        
        if(!$catId)
        {
            throw new LogicException(app::get('topshop')->_('商品三级分类错误'));
        }
         
        // 获取此分类的列
        $colsTitle = $this->getColsTitle($catId);
        $catCols = count($colsTitle[0]);
        if($catCols != $colCount)
        {
            throw new LogicException(app::get('topshop')->_('请确认三级分类模板是否正确'));
        }
        
        return [$catId, $colsTitle[0]];
    }
    /**
     * 读取文件
     * 
     * @param array $fileInfo 文件信息
     * */
    protected function readDocument($fileInfo)
    {
        $params['file'] = $fileInfo['tmp_name'];
        $params['type'] = $this->allowType;
        $objReader = kernel::single('importexport_type_reader', $params);
        
        // 判断上传商品的大小
        $row = $objReader->getRow();
        if($row > ($this->allowLimit+1))
        {
            throw new LogicException(app::get('topshop')->_('最多上传'.$this->allowLimit.'条商品信息'));
        }
        
        if(!$row || ($row-1) <= 0)
        {
            throw new LogicException(app::get('topshop')->_('请填写商品信息'));
        }
        
        $result['row'] = $row;
        $result['col'] = $objReader->getCol();
        $result['list'] = $objReader->readDocument();
        // 返回文档内容
        return $result;
    }
    
    protected function checkFile($file)
    {
        $exten = pathinfo($file, PATHINFO_EXTENSION);
        
        if($exten != $this->allowType)
        {
            throw new LogicException(app::get('topshop')->_('文件类型错误'));
        }
        
        return true;
    }
    /**
     * 根据三级分类下载上传模板
     * @param int $catId 三级分类
     * 
     * @return void
     * */
    public function downLoadTmpl($catId)
    {
        $data = $this->getColsTitle($catId);
        // 开始导出
        $objWriter = kernel::single('importexport_type_writer');
        $objWriter->writeDocument($data);
        $objWriter->saveDocument();
        $fileName = 'import-catId-'.$catId.'-'.time();
        $objWriter->downDocument($fileName, $this->allowType);
        
        return true;
    }
    
    // 获取列头
    protected function getColsTitle($catId)
    {
        // 获取分类关联的属性和参数
        $PropsAndParams = $this->getPropsAndParams($catId, true);
        $colsTitle = array_merge($this->getImportTitle(), $PropsAndParams['propNames']);
        $colsTitle['same_item'] = app::get('topshop')->_('同一商品标识');
        $data[0] = $colsTitle;
        
        return $data;
    }
    
    // 获取分类关联的属性和参数
    protected function getPropsAndParams($catId, $isSpec = false)
    {
        // 获取销售属性
        $specProps = app::get('topshop')->rpcCall('category.catprovalue.get',array('cat_id'=>$catId,'type'=>'spec'));
        // 获取自然属性
        $natureProps = app::get('topshop')->rpcCall('category.catprovalue.get',array('cat_id'=>$catId,'type'=>'nature'));
        $Props = array_merge($specProps, $natureProps);
        if($isSpec)
        {
            $Props = $natureProps;
        }
        $PropsName = [];
        foreach ($Props as $val)
        {
            $PropsName[] = $val['prop_name'];
        }
        
        $result['props'] = $Props;
        $result['propNames'] = $PropsName;
        //$result['paramNames'] = $catParamsName;
        
        return $result;
    }
    
    // 获取分类
    protected function getCatList()
    {
        $shopId = $this->shopId;
        $shopAuthorize = app::get('topshop')->rpcCall('shop.authorize.catbrandids.get',array('shop_id'=>$shopId));
        $catId = $shopAuthorize[$shopId]['cat'];
        $shopType = $shopAuthorize[$shopId]['shop_type'];
        if(!$catId && $shopType == "self")
        {
            $catList = app::get('topshop')->rpcCall('category.cat.get.list');
        }
        elseif($catId)
        {
            $catList = app::get('topshop')->rpcCall('category.cat.get',array('cat_id'=>implode(',',$catId)));
        }
    
        return $catList;
    }
    
    // 获取三级分类
    public function getLv3Cat()
    {
        $catList = $this->getCatList();
        $lv3data = [];
        foreach ($catList as $value)
        {
            foreach ($value['lv2'] as $val)
            {
                $lv3data = array_merge($lv3data, $val['lv3']);
            }
        }
    
        return $lv3data;
    }
    
    // 获取带有二级分类的三级分类
    public function getLv3CatWithLv2()
    {
        $catList = $this->getCatList();
        $data = [];
        foreach ($catList as $value)
        {
            foreach ($value['lv2'] as $val)
            {
                sort($val['lv3']);
                $data[$val['cat_id']] = $val;
            }
        }
        
        return $data;
    }
    
    /**
     * 获取导入列头
     * */
    protected function getImportTitle($isAll = false)
    {
        $cols = [
                'cat_name_l1' => [app::get('topshop')->_('一级类目'), 'required'=>true],
                'cat_name_l2' => [app::get('topshop')->_('二级类目'), 'required'=>true],
                'cat_name_l3' => [app::get('topshop')->_('三级类目'), 'required'=>true],
                'shop_CatName'=> [app::get('topshop')->_('店铺分类'), 'required'=>true],
                'title' => [app::get('topshop')->_('商品标题'), 'required'=>true],
                'sub_title' => [app::get('topshop')->_('商品副标题'), 'required'=>false],
                'brand_name' => [app::get('topshop')->_('品牌'), 'required'=>true],
                'bn' => [app::get('topshop')->_('商品货号'), 'required'=>false],
                'barcode' => [app::get('topshop')->_('条形码'), 'required'=>false],
                'platfrom' => [app::get('topshop')->_('发布平台'), 'required'=>true],
                'price' => [app::get('topshop')->_('销售价'), 'required'=>true],
                'realStore' => [app::get('topshop')->_('库存'), 'required'=>true],
                'sub_stock_store' => [app::get('topshop')->_('库存计数'), 'required'=>true],
                'mkt_price' => [app::get('topshop')->_('原价'), 'required'=>true],
                'is_show_mkt_price' => [app::get('topshop')->_('是否显示原价'), 'required'=>true],
                'cost_price' => [app::get('topshop')->_('成本价'), 'required'=>false],
                'weight' => [app::get('topshop')->_('重量'), 'required'=>true],
                'unit' => [app::get('topshop')->_('计价单位'), 'required'=>true],
                'dlytmpl_name' => [app::get('topshop')->_('运费模板'), 'required'=>true],
                'spec_info' => [app::get('topshop')->_('规格值'), 'required'=>false],
        ];
        
        if($isAll)
        {
            return $cols;
        }
        
        $title = [];
        foreach($cols as $k=>$v)
        {
            $title[$k] = $v[0];
        }
        
        return $title;
    }
    
}
 