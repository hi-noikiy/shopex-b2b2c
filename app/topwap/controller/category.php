<?php
class topwap_ctl_category extends topwap_controller{
    public function index()
    {
        $this->setLayoutFlag('category');

        $virtualcatEnable = app::get('syscategory')->getConf('virtualcat.wapenable');
        if($virtualcatEnable =='true'){
            $apiParams = [
                'fields' => 'virtual_cat_id,virtual_cat_name',
                'platform' => 'h5',
            ];
            $virtualCatList = app::get('topwap')->rpcCall('category.virtualcat.get.list', $apiParams);
            $pagedata['data'] = $virtualCatList;
            return $this->page('topwap/category/virtualcatindex.html',$pagedata);
        }else{
            $catList = app::get('topwap')->rpcCall('category.cat.get.list',array('fields'=>'cat_id,cat_name'));
            $pagedata['data'] = $catList;
            return $this->page('topwap/category/index.html',$pagedata);
        }
    }
}
