<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysitem_trade_store
{

    public function __construct()
    {
        $this->redisStore = kernel::single('sysitem_item_redisStore');
    }

    /**
     * 下单减库存
     * 直接扣减库存(对应下单减库存),对应function recoverItemStore
     * @param  array  $arrParams sku库存信息
     * @return bool
     */
    public function minusItemStore($arrParams=array())
    {
        //redis扣减商品可销售库存
        $this->redisStore->decrbyStore($arrParams['item_id'], $arrParams['sku_id'], $arrParams['quantity']);
        return true;
    }

    /**
     * 取消订单恢复库存
     * 恢复扣减的库存(对应下单减库存),对应function minusItemStore
     * @param  array  $arrParams sku库存信息
     * @return bool
     */
    public function recoverItemStore($arrParams=array())
    {
        $this->redisStore->incrbyStore($arrParams['item_id'], $arrParams['sku_id'], $arrParams['quantity']);
        return true;
    }

    /**
     * 支付后处理，付款减库存的订单商品，修改store字段和freez字段
     * 例如$arrParams = array (
     *     'item_id' => 135,
     *     'sku_id' => 449,
     *     'quantity' => 1,
     *     'sub_stock' => 0,
     *     'status' => 'afterpay',
     * )
     * @param  array  $arrParams sku库存信息
     * @return bool
     */
    public function minusItemStoreAfterPay($arrParams=array())
    {
        $this->redisStore->decrbyFreez($arrParams['item_id'], $arrParams['sku_id'], $arrParams['quantity']);
        return true;
    }

    /**
     * 付款减库存时冻结库存
     * 冻结库存(对应付款减库存),对应 function unfreezeItemStore
     * @param  array  $arrParams sku库存信息
     * @return bool
     */
    public function freezeItemStore($arrParams=array())
    {
        //redis扣减商品可销售库存
        $this->redisStore->decrbyStore($arrParams['item_id'], $arrParams['sku_id'], $arrParams['quantity'], true);
        return true;
    }

    /**
     * 付款减库存情况下取消订单释放库存
     * 解冻库存(对应付款减库存),对应 function freezeItemStore
     * @param  array  $arrParams sku库存信息
     * @return bool
     */
    public function unfreezeItemStore($arrParams=array())
    {
        $this->redisStore->incrbyStore($arrParams['item_id'], $arrParams['sku_id'], $arrParams['quantity'], true);
        return true;
    }
}
