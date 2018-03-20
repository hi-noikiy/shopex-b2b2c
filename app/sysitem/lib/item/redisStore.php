<?php
/**
 * 商品可销售库存和SKU库存，存储到redis中
 *
 * store 表示商品或者SKU可售库存
 * freez 表示商品或者SKU冻结库存
 */
class sysitem_item_redisStore {

    /**
     * redis object
     */
    public $redis = null;

    public function __construct()
    {
        $this->redis = redis::scene('sysitem');
    }

    /**
     * 存储库存的key
     *
     * @param string $type 存储库存类型，item sku
     * @param int $itemId 商品ID
     * @param int $skuId SKU ID
     */
    private function __genKey(...$data)
    {
        return sha1(implode('-',$data));
    }

    //商品库存hash key
    private function __getItemStoreHashKey($itemId)
    {
        return $this->__genKey($itemId, 'store');
    }

    //商品冻结库存 hash key
    private function __getItemFreezHashKey($itemId)
    {
        return $this->__genKey($itemId, 'freez');
    }

    //货品库存 hash key
    private function __getSkuStoreHashKey($skuId)
    {
        return $this->__genKey($skuId, 'sku_store');
    }

    //货品冻结库存 hash key
    private function __getSkuFreezHashKey($skuId)
    {
        return $this->__genKey($skuId, 'sku_freez');
    }

    /**
     * 将Mysql DB中库存数据初始化到redis中
     * cmd  sysitem:store syncToRedis 命令中调用该方法
     *
     * 添加商品和编辑商品修改商品库存
     * 调用该方法将最新编辑的商品库存同步
     */
    public function init($itemId, $realstore, $freez, $skuStoreArr)
    {
        $params[] = 'store';

        //商品可售库存
        $params[] = $this->__getItemStoreHashKey($itemId);
        $params[] = $realstore;

        //商品冻结库存
        $params[] = $this->__getItemFreezHashKey($itemId);;
        $params[] = $freez;

        foreach( $skuStoreArr as $row )
        {
            //sku可售库存
            $params[] = $this->__getSkuStoreHashKey($row['sku_id']);
            $params[] = $row['store'];

            //sku冻结库存
            $params[] = $this->__getSkuFreezHashKey($row['sku_id']);
            $params[] = $row['freez'];
        }

        return call_user_func_array([$this->redis, 'hmset'], $params);
    }

    /**
     * 添加／编辑商品同步库存到redis
     *
     * @param int $itemId 商品ID
     * @param int $store 商品总库存
     */
    public function initItemStore($itemId, $store)
    {
        $freez = $this->redis->hget('store', $this->__getItemFreezHashKey($itemId));

        $realstore = $store - $freez;
        $this->redis->hset('store', $this->__getItemStoreHashKey($itemId), $realstore);
        return true;
    }

    /**
     * 添加／编辑商品同步库存到redis
     *
     * @param array $skuStoreArr sku_id货品ID store货品总库存 freez货品冻结库存
     */
    public function initSkuStore($skuStoreArr)
    {
        $params[] = 'store';

        foreach( $skuStoreArr as $row )
        {
            //获取到冻结库存
            $freez = $this->redis->hget('store', $this->__getSkuFreezHashKey($row['sku_id']));

            //sku可售库存
            $params[] = $this->__getSkuStoreHashKey($row['sku_id']);
            $params[] = $row['store'] - $freez;
        }

        return call_user_func_array([$this->redis, 'hmset'], $params);
    }

    /**
     * 删除指定的货品ID的数据
     */
    public function deleteSkuStore($skuIds)
    {
        if( empty($skuIds) ) return true;

        if( ! is_array($skuIds) )
        {
            $skuIds = array($skuIds);
        }

        $params[] = 'store';
        foreach( $skuIds  as $skuId )
        {
            $params[] = $this->__getSkuStoreHashKey($skuId);
            $params[] = $this->__getSkuFreezHashKey($skuId);
        }

        return call_user_func_array([$this->redis, 'HDEL'], $params);
    }

    /**
     * 删除指定商品的库存数据，如果有sku，则需要再次调用 deleteSkuStore 方法删除对应的sku数据
     */
    public function deleteItemStore($itemIds)
    {
        if( empty($itemIds) ) return true;

        if( ! is_array($itemIds) )
        {
            $itemIds = array($itemIds);
        }

        $params[] = 'store';
        foreach( $itemIds  as $itemId )
        {
            $params[] = $this->__getItemStoreHashKey($itemId);
            $params[] = $this->__getItemFreezHashKey($itemId);
        }

        return call_user_func_array([$this->redis, 'HDEL'], $params);
    }

    /**
     * 用于下单减库存和下单冻结库存
     *
     * @param int $itemId
     * @param int $skuId
     * @param int $num
     * @param string $isFreez  是否需要冻结库存
     */
    public function decrbyStore($itemId, $skuId, $num, $isFreez=false)
    {
        $skuKey = $this->__getSkuStoreHashKey($skuId);

        $skuStore = $this->redis->hincrby('store', $skuKey, -$num);
        if( $skuStore < 0 )
        {
            $this->redis->hincrby('store', $skuKey, $num);
            throw new \LogicException('商品库存不足');
        }

        //SKU库存扣减成功，商品则肯定有库存
        $this->redis->hincrby('store', $this->__getItemStoreHashKey($itemId), -$num);

        if( $isFreez )
        {
            $this->redis->hincrby('store', $this->__getItemFreezHashKey($itemId), $num);
            $this->redis->hincrby('store', $this->__getSkuFreezHashKey($skuId), $num);
        }

        $this->redis->hset('update.store.itemIds', $itemId, $itemId);
        $this->redis->hset('update.store.skuIds', $skuId, $skuId);

        return $return;
    }

    /**
     * 增加库存数据 恢复库存 需要异步将更改同步到db 可调用该方法
     *
     * @param bool isFreez 是否操作过冻结库存
     */
    public function incrbyStore($itemId, $skuId, $num, $isFreez=false)
    {
        $this->redis->hincrby('store', $this->__getItemStoreHashKey($itemId), $num);
        $this->redis->hincrby('store', $this->__getSkuStoreHashKey($skuId), $num);

        if( $isFreez )
        {
            $this->decrbyFreez($itemId, $skuId, $num);
        }

        $this->redis->hset('update.store.itemIds', $itemId, $itemId);
        $this->redis->hset('update.store.skuIds', $skuId, $skuId);

        return true;
    }

    /**
     * 付款减库存，支付后将冻结库存减去
     */
    public function decrbyFreez($itemId, $skuId, $num)
    {
        $return['item_freez'] = $this->redis->hincrby('store', $this->__getItemFreezHashKey($itemId), -$num);
        $return['sku_freez']  = $this->redis->hincrby('store', $this->__getSkuFreezHashKey($skuId), -$num);

        $this->redis->hset('update.store.itemIds', $itemId, $itemId);
        $this->redis->hset('update.store.skuIds', $skuId, $skuId);

        return $return;
    }

    public function getUpStoreItemIds()
    {
        return $this->redis->hgetAll('update.store.itemIds');
    }

    public function delUpStoreItemIds($itemId)
    {
        return $this->redis->hdel('update.store.itemIds', $itemId);
    }

    public function getUpStoreSkuIds()
    {
        return $this->redis->hgetAll('update.store.skuIds');
    }

    public function delUpStoreSkuIds($skuId)
    {
        return $this->redis->hdel('update.store.skuIds', $skuId);
    }

    public function getItemStore($itemIds, $type=null)
    {
        $type = $type ? strtolower($type) : null;

        $params[] = 'store';

        foreach( $itemIds as $itemId )
        {
            if( $type == 'realstore' || is_null($type) )
            {
                //sku可售库存
                $params[] = $reslut[$itemId]['realStore'] = $this->__getItemStoreHashKey($itemId);
            }

            if( $type == 'freez' || is_null($type) )
            {
                //sku冻结库存
                $params[] = $reslut[$itemId]['freez'] = $this->__getItemFreezHashKey($itemId);
            }
        }

        $data =  call_user_func_array([$this->redis, 'hmget'], $params);
        foreach( $reslut as $itemId => $row )
        {
            if( $row['realStore'] )
            {
                $reslut[$itemId]['realStore'] = current($data);
                next($data);
            }

            if( $row['freez'] )
            {
                $reslut[$itemId]['freez'] = current($data);
                next($data);
            }

            if( is_null($type) )
            {
                $reslut[$itemId]['store'] = $reslut[$itemId]['realStore'] + $reslut[$itemId]['freez'];
            }
        }

        return $reslut;

    }

    /**
     * 获取商品库存
     */
    public function getStoreByItemId($itemId, $type=null)
    {
        $type = $type ? strtolower($type) : null;

        //获取可售库存
        if( $type == 'realstore' )
        {
            $return['realStore'] = $this->redis->hget('store', $this->__getItemStoreHashKey($itemId));
        }
        //获取冻结库存
        elseif( $type == 'freez' )
        {
            $return['freez'] = $this->redis->hget('store', $this->__getItemFreezHashKey($itemId));
        }
        //获取可售库存和冻结库存
        else
        {
            $data = $this->redis->hmget('store', $this->__getItemStoreHashKey($itemId), $this->__getItemFreezHashKey($itemId));
            $return['realStore'] = $data[0];
            $return['freez'] = $data[1];
            $return['store'] = $return['realStore'] + $return['freez'];
        }

        return $return;
    }

    public function getSkuStore($skuIds, $type=null)
    {
        $type = $type ? strtolower($type) : null;

        $params[] = 'store';

        foreach( $skuIds as $skuId )
        {
            if( $type == 'realstore' || is_null($type) )
            {
                //sku可售库存
                $params[] = $reslut[$skuId]['realStore'] = $this->__getSkuStoreHashKey($skuId);
            }

            if( $type == 'freez' || is_null($type) )
            {
                //sku冻结库存
                $params[] = $reslut[$skuId]['freez'] = $this->__getSkuFreezHashKey($skuId);
            }
        }

        $data =  call_user_func_array([$this->redis, 'hmget'], $params);
        foreach( $reslut as $skuId => $row )
        {
            if( $row['realStore'] )
            {
                $reslut[$skuId]['realStore'] = current($data);
                next($data);
            }

            if( $row['freez'] )
            {
                $reslut[$skuId]['freez'] = current($data);
                next($data);
            }

            if( is_null($type) )
            {
                $reslut[$skuId]['store'] = $reslut[$skuId]['realStore'] + $reslut[$skuId]['freez'];
            }
        }

        return $reslut;
    }

    /**
     * 获取指定SKU ID的库存
     */
    public function getStoreBySkuId($skuId, $type=null)
    {
        $type = $type ? strtolower($type) : null;

        //获取可售库存
        if( $type == 'realstore' )
        {
            $return['realStore'] = $this->redis->hget('store', $this->__getSkuStoreHashKey($skuId));
        }
        //获取冻结库存
        elseif( $type == 'freez' )
        {
            $return['freez'] = $this->redis->hget('store', $this->__getSkuFreezHashKey($skuId));
        }
        //获取可售库存和冻结库存 总库存
        else
        {
            $data = $this->redis->hmget('store', $this->__getSkuStoreHashKey($skuId), $this->__getSkuFreezHashKey($skuId));
            $return['realStore'] = $data[0];
            $return['freez'] = $data[1];
            $return['store'] = $return['realStore'] + $return['freez'];
        }

        return $return;
    }
}

