<?php
class sysitem_mdl_item extends dbeav_model{
	/**
	* @var bool 启用标签
	*/
    var $has_tag = true;

    function __construct(&$app){
        parent::__construct($app);
    }

    public $defaultOrder = array('modified_time','DESC');

    function dump($filter,$field = '*',$subSdf = null){
        $dumpData = parent::dump($filter,$field,$subSdf);

        $oSpec = app::get('syscategory')->model('props');
        if( $dumpData['spec_desc'] && is_array( $dumpData['spec_desc'] ) ){
            foreach( $dumpData['spec_desc'] as $specId => $spec ){
                // $dumpData['spec'][$specId] = $oSpec->dump($specId,'*');
                $dumpData['spec'][$specId] = $oSpec->getRow('*',array('prop_id'=>$specId));
                foreach( $spec as $pSpecId => $specValue ){
                    $dumpData['spec'][$specId]['option'][$pSpecId] = array_merge( array('private_spec_value_id'=>$pSpecId), $specValue );
                }
            }
        }

        unset($dumpData['spec_desc']);
        return $dumpData;
    }

    public function getList($cols='*', $filter=array(), $offset=0, $limit=200, $orderBy=null)
    {
        if(!$orderBy){
            $orderBy = 'item_id DESC';
        }
        if($filter['status'])
        {
            $filter['approve_status'] = $filter['status'];
            unset($filter['status']);
        }

        $data = kernel::single('search_object')->instance('item')
            ->page($offset, $limit)
            ->orderBy($orderBy)
            ->search($cols.',item_id',$filter);

        return $data['list'];
    }

    //获取商品列表的简单的数据
    //只获取item表本身的数据
    public function simpleGetList($cols='*', $filter=array(), $offset=0, $limit=200, $orderBy=null)
    {
        return parent::getList($cols, $filter, $offset, $limit, $orderBy);
    }

    public function count($filter=null)
    {
        if($filter['status'])
        {
            $filter['approve_status'] = $filter['status'];
            unset($filter['status']);
        }
        return kernel::single('search_object')->instance('item')->count($filter);
    }

    /**
     * 重写搜索的下拉选项方法
     * @param null
     * @return null
     */
    public function searchOptions(){
        $columns = array();
        foreach($this->_columns() as $k=>$v)
        {
            if(isset($v['searchtype']) && $v['searchtype'])
            {
                $columns[$k] = $v['label'];
            }
        }

        $columns = array_merge(array(
            'shop_name'=>app::get('sysitem')->_('所属店铺'),
            'cat_name'=>app::get('sysitem')->_('商品类目'),
            'brand_name'=>app::get('sysitem')->_('商品品牌'),
        ),$columns);

        return $columns;
    }

    /**
     * @brief 删除商品
     * @author ajx
     * @param $params array  item_ids
     * @param $msg string 处理结果
     *
     * @return
     */
    public function doDelete($params)
    {
        if(!$params['item_id'])
        {
            $msg = app::get('sysitem')->_('没有商品ID！');
            throw new \LogicException($msg);
        }
        $params['item_id'] = $params['item_id'];
        //团购判断
        $activityStatus = app::get('sysitem')->rpcCall('promotion.activity.item.info', ['item_id'=>$params['item_id'], 'valid'=>1]);
        if($activityStatus['status'])
        {
            $msg = app::get('sysitem')->_('该商品正在活动中不可删除！');
            throw new \LogicException($msg);
        }

        $giftItem = app::get('sysitem')->rpcCall('promotion.gift.item.get',['item_id'=>$params['item_id'],'end_time'=>'than','valid'=>1]);
        if($giftItem)
        {
            $msg = app::get('sysitem')->_('该商品正在赠品活动中不可删除！');
            throw new \LogicException($msg);
        }

        $giftSku = app::get('sysitem')->rpcCall('promotion.gift.sku.get',['item_id'=>$params['item_id'],'end_time'=>'than','valid'=>1]);
        if($giftSku)
        {
            $msg = app::get('sysitem')->_('该商品正在赠品活动中不可删除！');
            throw new \LogicException($msg);
        }

        $filter = ['item_id'=>$params['item_id']];
        $skuIds = app::get('sysitem')->model('sku_store')->getList('sku_id', $filter);
        $skuIds = array_column($skuIds, 'sku_id');

        $db = app::get('sysitem')->database();
        $db->beginTransaction();
        try{
            $rs = app::get('sysitem')->model('item')->delete($filter);
            $rs = app::get('sysitem')->model('item_count')->delete($filter);
            $rs = app::get('sysitem')->model('item_desc')->delete($filter);
            $rs = app::get('sysitem')->model('item_nature_props')->delete($filter);
            $rs = app::get('sysitem')->model('item_status')->delete($filter);
            $rs = app::get('sysitem')->model('item_store')->delete($filter);
            $rs = app::get('sysitem')->model('sku')->delete($filter);
            $rs = app::get('sysitem')->model('sku_store')->delete($filter);
            $rs = app::get('sysitem')->model('spec_index')->delete($filter);

            kernel::single('sysitem_item_redisStore')->deleteItemStore($params['item_id']);
            kernel::single('sysitem_item_redisStore')->deleteSkuStore($skuIds);

            $db->commit();
        }catch(Exception $e){
            $db->rollback();
            $msg = app::get('sysitem')->_('商品删除失败');
            throw new \logicException($msg);
        }

        return true;
    }
}
