<?php
/**
 * 商品删除
 * item.delete
 */
class sysitem_api_item_delete{

    public $apiDescription = "商品删除";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'item_id' => ['type'=>'integer','valid'=>'required|integer|min:1','description'=>'商品id','example'=>'2'],
            'shop_id' => ['type'=>'integer','valid'=>'required|integer|min:1','description'=>'店铺id','example'=>'3'],
        );
        return $return;
    }

    public function itemDelete($params)
    {
        try
        {
              $objMdlItem = app::get('sysitem')->model('item');
              if(!$objMdlItem->count(['item_id'=>$params['item_id'],'shop_id'=>$params['shop_id']])){
                $msg = app::get('sysitem')->_('只能删除本店铺商品');
                throw new \LogicException($msg);
              }
              $result = $objMdlItem->doDelete($params);
              if(!$result)
              {
                  throw new Exception('商品删除失败');
              }
              
              event::fire('del.item', array($params['item_id']));
        }
        catch(Exception $e)
        {
            throw new Exception($e->getMessage());
        }
        return true;
    }
}
