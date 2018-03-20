<?php
/**
 * 添加sku
 * item.sku.add
 */
class sysitem_api_sku_create {

    /**
     * 接口作用说明
     */
    public $apiDescription = '添加单个sku';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        $return['params'] = array(
            'item_id' => ['type'=>'integer','valid'=>'required','description'=>'商品主键','example'=>'2'],
            'title' => ['type'=>'string','valid'=>'','description'=>'商品标题','example'=>''],
            'bn' => ['type'=>'string','valid'=>'','description'=>'商品编号','example'=>''],
            'price' => ['type'=>'numeric','valid'=>'','description'=>'商品价格','example'=>''],
            'cost_price' => ['type'=>'numeric','valid'=>'','description'=>'成本价','example'=>''],
            'mkt_price' => ['type'=>'numeric','valid'=>'','description'=>'原价','example'=>''],
            'barcode' => ['type'=>'string','valid'=>'','description'=>'条形码','example'=>''],
            'weight' => ['type'=>'numeric','valid'=>'','description'=>'商品重量','example'=>''],
            'spec_info' => ['type'=>'string','valid'=>'','description'=>'物品描述','example'=>''],
            'spec_desc' => ['type'=>'string','valid'=>'','description'=>'规格值,序列化','example'=>''],
            'status' => ['type'=>'boolean','valid'=>'','description'=>'sku状态','example'=>''],
            'outer_id' => ['type'=>'string','valid'=>'','description'=>'商家设置的外部id','example'=>''],
            'shop_id' => ['type'=>'integer','valid'=>'','description'=>'店铺id','example'=>''],
            'image_default_id' => ['type'=>'string','valid'=>'','description'=>'商品默认图','example'=>''],
            'cat_id' => ['type'=>'integer','valid'=>'','description'=>'商品类目ID','example'=>''],
            'brand_id' => ['type'=>'integer','valid'=>'','description'=>'品牌ID','example'=>''],
            'shop_cat_id' => ['type'=>'integer','valid'=>'','description'=>'商家自定义分类id','example'=>''],
        );

        return $return;
    }

    public function create($params)
    {
        return $itemListData;
    }
}
