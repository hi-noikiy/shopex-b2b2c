<?php

class sysitem_item_store {


    public function getItemListByStore($row,$filter,$start,$limit,$orderBy)
    {
        $store = app::get('sysitem')->database()->createQueryBuilder();
        $store->select('I.item_id,I.modified_time,I.title,I.image_default_id,I.dlytmpl_id,I.price,I.nospec,S.store,ST.approve_status,ST.list_time')
           ->from('sysitem_item_store', 'S')
           ->leftJoin('S', 'sysitem_item', 'I', 'S.item_id=I.item_id')
           ->leftJoin('S', 'sysitem_item_status', 'ST', 'S.item_id=ST.item_id')
           ->where('S.store<='.$store->createPositionalParameter($filter['store']))
           ->andWhere('I.shop_id='.$store->createPositionalParameter($filter['shop_id']))
           ->setFirstResult($start)
           ->setMaxResults($limit);
           //->groupBy('T.trade_from');
        $storeList = $store->execute()->fetchAll();
        return $storeList;
        //echo '<pre>';print_r($storeCount);exit();
    }
    public function getItemCountByStore($filter)
    {
        $store = app::get('sysitem')->database()->createQueryBuilder();
        $store->select('count(I.item_id) as itemNum')
           ->from('sysitem_item_store', 'S')
           ->leftJoin('S', 'sysitem_item', 'I', 'S.item_id=I.item_id')
           ->where('S.store<='.$store->createPositionalParameter($filter['store']))
           ->andWhere('I.shop_id='.$store->createPositionalParameter($filter['shop_id']));
        $storeCount = $store->execute()->fetchAll();
        return $storeCount[0]['itemNum'];
        //echo '<pre>';print_r($storeCount);exit();
    }

    public function updateStore($itemId, $skuId, $store)
    {
        $objItemRedisStore = kernel::single('sysitem_item_redisStore');

        $realStore = $objItemRedisStore->getStoreBySkuId($skuId, 'realstore');
        $realStore = $realStore['realStore'] ? $realStore['realStore'] : 0;

        //新增库存数
        $addStore = $store - $realStore;
        //更新货品库存
        $objItemRedisStore->incrbyStore($itemId, $skuId, $addStore);

        return true;
    }

    //更新指定sku bn的库存
    public function updateStoreByBn($skuBn, $shopId, $store)
    {
        $ids = $this->__getIdsByBn($skuBn, $shopId);
        $itemId = $ids['item_id'];
        $skuId = $ids['sku_id'];
        return $this->updateStore($itemId, $skuId, $store);
    }

    private function __getIdsByBn($skuBn, $shopId)
    {
        if( !$skuBn || !$shopId ) return false;

        $ItemModel = app::get('sysitem')->model('item');
        $skuModel = app::get('sysitem')->model('sku');

        //根据sku bn获取到对应的item_id, 不同店铺的bn可以重复
        $filter = ['bn'=>$skuBn];
        $skuRes = $skuModel->getList('item_id', $filter);
        $itemIds = array_column($skuRes,'item_id');
        if( empty($itemIds) )
        {
            throw new LogicException( "bn {$skuBn} 不存在" );
        }

        //过滤出指定店铺的，对应skuBn的item_id
        $filter = ['item_id|in'=>$itemIds, 'shop_id'=>$shopId];
        $itemId = $ItemModel->getRow('item_id', $filter);
        if( empty($itemId) )
        {
            throw new LogicException( "该店铺中bn {$skuBn} 不存在" );
        }

        return $skuModel->getRow('item_id,sku_id', ['item_id'=>$itemId, 'bn'=>$skuBn] );
    }
}

