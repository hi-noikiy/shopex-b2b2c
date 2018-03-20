<?php

/**
 * importexport.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topshop_ctl_item_importexport extends topshop_controller {
    
    public function __construct($app)
    {
        parent::__construct($app);
        
    }
    
    // 商品导出
    public function export()
    {
        try {
            $objExport = kernel::single('topshop_item_export', $this->shopId);
            $exportFilter = $this->__exportFilter();
            $this->sellerlog(app::get('topshop')->_('导出商品'));
            // 导出
            $objExport->export($exportFilter);
        } catch (Exception $e) {
            
            return $this->splash('error', null, $e->getMessage());
        }
    }
    
    public function importView()
    {
        $pagedata = [];
        // 获取当前店铺可用的三级分类
        try {
            $objImport = kernel::single('topshop_item_import', $this->shopId);
            $pagedata['lv3Cat'] = $objImport->getLv3CatWithLv2();
        } catch (Exception $e) {
            return $this->splash('error', null, $e->getMessage());
        }
        
        $data['html'] = view::make('topshop/item/import.html', $pagedata)->render();
        $data['success'] = true;
        return response::json($data);
        
    }
    
    public function downLoadImportTmpl()
    {
        // 根据分类下载上传模板
        try {
            $catId = input::get('cat_id',false);
            if(!$catId)
            {
                throw new LogicException(app::get('topshop')->_('参数错误'));
            }
            $objImport = kernel::single('topshop_item_import', $this->shopId);
            $objImport->downLoadTmpl($catId);
            
        } catch (Exception $e) {
            return $this->splash('error', null, $e->getMessage());
        }
        
    }
    // 商品导入
    public function import()
    {
        try {
            $fileInfo = $_FILES['import_file'];
            if(!$fileInfo)
            {
                throw new LogicException(app::get('topshop')->_('请上传文件'));
            }
            
            $objImport = kernel::single('topshop_item_import', $this->shopId);
            $objImport->import($fileInfo);
        } catch (Exception $e) {
            return $this->splash('error', null, $e->getMessage());
        }
        $this->sellerlog(app::get('topshop')->_('导入商品'));
        $url = request::server('HTTP_REFERER');
        return $this->splash('success', $url, app::get('topshop')->_('商品导入成功'));
    }
    
    // 整理导出条件
    private function __exportFilter()
    {
        $exportLimit = input::get('exportlimit', false);
        $exportPage = input::get('exportpage', false);
        
        if(!$exportLimit || !$exportPage)
        {
            throw new LogicException(app::get('topshop')->_('导出参数错误'));
        }
        $filter = input::get();
        $params = [];
        
        if(intval($exportLimit) > 100)
        {
            $exportLimit = 100;
        }
        
        if($filter['min_price']&&$filter['max_price'])
        {
            if($filter['min_price']>$filter['max_price'])
            {
                $msg = app::get('topshop')->_('最大值不能小于最小值！');
                throw new LogicException(app::get('topshop')->_($msg));
            }
        }
        
        $params = array(
                'shop_id' => $this->shopId,
                'search_keywords' => $filter['item_title'],
                'min_price' => $filter['min_price'],
                'max_price' => $filter['max_price'],
                'page_no' =>intval($exportPage),
                'page_size' => intval($exportLimit),
        );
        
        if($filter['use_platform'] >= 0)
        {
            $params['use_platform'] = $filter['use_platform'];
        }
        if($filter['item_cat'] && $filter['item_cat'] > 0)
        {
            $params['search_shop_cat_id'] = (int)$filter['item_cat'];
        }
        if($filter['item_no'])
        {
            $params['bn'] = $filter['item_no'];
        }
        if(isset($filter['status']) && $filter['status'])
        {
            $params['approve_status'] = $filter['status'];
        }
        
        if($filter['dlytmpl_id']&&$filter['dlytmpl_id']>0)
        {
            $params['dlytmpl_id'] = $filter['dlytmpl_id'];
        }
        
        $params['is_search'] = $filter['is_search'];
        
        return $params;
    }
}
 