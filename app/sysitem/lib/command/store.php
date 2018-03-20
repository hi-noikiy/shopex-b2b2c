<?php
/**
 * ShopEx licence
 *
 * @category ecos
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @version 0.1
 */

class sysitem_command_store extends base_shell_prototype
{
    /**
     * @var 定义名称
     */
    public $command_syncToRedis = '商品的可销售库存同步到Redis中存储';

    /**
     * @param null
     * @return null
     */
    public function command_syncToRedis()
    {
        $itemStoreModel = app::get('sysitem')->model('item_store');
        $skuStoreModel = app::get('sysitem')->model('sku_store');
        $redisStore = kernel::single('sysitem_item_redisStore');
        $count = $itemStoreModel->count();
        logger::info(sprintf('Total %d', $count));
        $pagesize = 50;
        for($i=0; $i<$count; $i+=$pagesize)
        {
            $itemStoreList = array();
            $itemStoreList = $itemStoreModel->getList('item_id,store,freez', array(), $i, $pagesize);
            foreach( $itemStoreList as $row )
            {
                $realStore = ($row['store'] - $row['freez']) > 0 ? ($row['store'] - $row['freez']) : 0;
                $skuStoreList = array();
                $skuStoreList = $skuStoreModel->getList('item_id,sku_id,store,freez', array('item_id'=>$row['item_id']));
                $skuStoreArr = array();
                foreach( $skuStoreList as $key=>$skuRow )
                {
                    $skuStoreArr[$key]['sku_id'] = $skuRow['sku_id'];
                    $skuStoreArr[$key]['store'] = ($skuRow['store'] - $skuRow['freez']) > 0 ? ($skuRow['store'] - $skuRow['freez']) : 0;
                    $skuStoreArr[$key]['freez'] = $skuRow['freez'];
                }
                $redisStore->init($row['item_id'], $realStore, $row['freez'], $skuStoreArr);
            }
            $endNum = ($i+$pagesize) > $count ? $count : ($i+$pagesize);
            logger::info(sprintf('同步商品可销售库存到Redis成功数量 %d-%d!', $i, $endNum));
        }

        logger::info('同步商品可销售库存到redis成功');
    }//End Function

    /**
     * @var 定义名称
     */
    public $command_storeSyncDB = 'Redis中发生库存改变的商品，将对应商品的库存同步到数据库';

    public function command_storeSyncDB()
    {
        $redisStore = kernel::single('sysitem_item_redisStore');
        $itemIds = $redisStore->getUpStoreItemIds();
        if( $itemIds )
        {
            $itemStoreModel = app::get('sysitem')->model('item_store');
            $data = $redisStore->getItemStore($itemIds);
            foreach( $data as $itemId => $row )
            {
                $itemStoreModel->update(['store'=>$row['store'], 'freez'=>$row['freez']], ['item_id'=>$itemId]);
                $redisStore->delUpStoreItemIds($itemId);
                logger::info(sprintf('同步商品ID %d!', $itemId));
            }
        }

        $skuIds = $redisStore->getUpStoreSkuIds();
        if( $skuIds )
        {
            $skuStoreModel = app::get('sysitem')->model('sku_store');
            $data = $redisStore->getSkuStore($skuIds);
            foreach( $data as $skuId => $row )
            {
                $skuStoreModel->update(['store'=>$row['store'], 'freez'=>$row['freez']], ['sku_id'=>$skuId]);
                $redisStore->delUpStoreSkuIds($skuId);
                logger::info(sprintf('同步货品ID %d!', $skuId));
            }
        }
    }//End Function

}//End Class

