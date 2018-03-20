<?php
/**
 * 商品添加、编辑
 * item.create
 */
class sysitem_api_item_create {

    public $apiDescription = "商品添加、编辑";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'integer','valid'=>'required|min:1','description'=>'店铺id'],
            'cat_id' => ['type'=>'integer','valid'=>'required|integer|min:1','description'=>'商品类目ID','msg'=>'商品分类不能为空'],
            'brand_id' => ['type'=>'integer','valid'=>'required|integer|min:1','description'=>'品牌ID','msg'=>'品牌不能为空'],
            'shop_cat_id' => ['type'=>'string','valid'=>'required','description'=>'商家自定义分类id'],

            'title' => ['type'=>'string','valid'=>'required|string|max:50','description'=>'商品标题'],
            'sub_title' => ['type'=>'string','valid'=>'string|max:150','description'=>'商品子标题、卖点，请不要超过150个字符,不要有特殊字符'],
            'weight' => ['type'=>'numeric','valid'=>'required|numeric|min:0.01','description'=>'商品重量', 'msg'=>'重量必填|重量必须为数字|重量必须大于0'],
            'unit' => ['type'=>'string','valid'=>'','description'=>'计价单位'],
            'list_image' => ['type'=>'string','valid'=>'','description'=>'商品图片，支持多个，用逗号分隔,例如 images/29/e5/22/670cf312b0aaace1ebf6305d6f346ee147f29c16.jpg,images/65/8e/c4/bbeb17e97aad90ff3d777bf45e84bfce754f6611.jpg','example'=>'images/29/e5/22/670cf312b0aaace1ebf6305d6f346ee147f29c16.jpg,images/65/8e/c4/bbeb17e97aad90ff3d777bf45e84bfce754f6611.jpg'],
            'order_sort' => ['type'=>'integer','valid'=>'','description'=>'排序,此参数暂时不起作用'],

            'approve_status' => ['type'=>'string','valid'=>'in:onsale,instock','description'=>'商品状态'],
            // 'has_discount' => ['type'=>'boolean','valid'=>'','description'=>'是否支持会员打折,此参数暂时不起作用'],
            // 'is_virtual' => ['type'=>'boolean','valid'=>'','description'=>'是否是虚拟商品,此参数暂时不起作用'],
            'sub_stock' => ['type'=>'boolean','valid'=>'in:0,1','description'=>'减库存方式，0:付款减库存,1:下单减库存'],
            // 'is_offline' => ['type'=>'boolean','valid'=>'','description'=>'是否是线下商品'],

            'item_id' => ['type'=>'integer','valid'=>'','description'=>'商品id，新增商品则不填'],//编辑接口需要
            'store' => ['type'=>'string','valid'=>'','description'=>'商品级别的库存'],
            'price' => ['type'=>'numeric','valid'=>'required|numeric|min:0.01|max:10000000000', 'description'=>'标准售价', 'msg'=>'标准售价必填|标准售价必须是正整数|标准售价必须大于0|标准售价最大10000000000'],
            'cost_price' => ['type'=>'numeric','valid'=>'min:0.01', 'description'=>'成本价', 'msg'=>'成本价必须大于0的数字|成本价必须大于0的数字'],
            'mkt_price' => ['type'=>'numeric','valid'=>'','description'=>'原价'],
            'show_mkt_price' => ['type'=>'boolean','valid'=>'boolean','description'=>'是否在商品页展示原价'],
            'bn' => ['type'=>'string','valid'=>'','description'=>'商品编号，不填会自动生成'],
            'outer_id' => ['type'=>'string','valid'=>'max:30','description'=>'商家外部编码'],
            'barcode' => ['type'=>'string','valid'=>'','description'=>'商品级别的条形码'],

            'use_platform' => ['type'=>'string','valid'=>'in:0,1,2','description'=>'使用平台'],
            'dlytmpl_id' => ['type'=>'integer','valid'=>'required|integer|min:1','description'=>'运费模板ID','msg'=>'运费模板不能为空'],
            'desc' => ['type'=>'string','valid'=>'','description'=>'pc端文描'],
            'wap_desc' => ['type'=>'string','valid'=>'','description'=>'wap端文描'],

            // 'is_timing' => ['type'=>'boolean','valid'=>'','description'=>'是否定时上下架,此参数暂时不起作用'],
            // 'list_time' => ['type'=>'integer','valid'=>'','description'=>'上架时间'],

            'nature_props' => ['type'=>'boolean','valid'=>'','description'=>'自然属性信息'],
            'nospec' => ['type'=>'boolean','valid'=>'required|in:0,1','description'=>'是否是单品或者多规格'],
            'images' => ['type'=>'string','valid'=>'','description'=>'颜色属性图片'],
            'spec' => ['type'=>'boolean','valid'=>'','description'=>'选择的规格信息'],
            'spec_value' => ['type'=>'boolean','valid'=>'','description'=>'选择的规格值信息'],
            'itemParams' => ['type'=>'string','valid'=>'','description'=>'商品参数序列化'],
            'params' => ['type'=>'string','valid'=>'','description'=>'商品参数序列化'],
            'sku' => ['type'=>'jsonArray', 'valid'=>'required_if:nospec,0', 'example'=>'', 'description'=>'sku信息' ,'params' => [
                'price'      => ['type'=>'numeric',  'valid'=>'numeric|min:0.01','description'=>'SKU销售价', 'msg'=>'SKU销售价必填|SKU销售价必须是正整数|SKU销售价必须大于0|SKU销售价最大10000000000'],
                'mkt_price'  => ['type'=>'numeric',  'valid'=>'min:0', 'description'=>'SKU原价'],
                'cost_price'   => ['type'=>'numeric',  'valid'=>'min:0', 'description'=>'SKU成本价'],
                'store' => ['type'=>'integer',  'valid'=>'required|numeric|min:1|max:999999', 'description'=>'SKU库存', 'msg'=>'SKU库存必填|SKU库存必须是正整数|SKU库存必须大于0|SKU库存最大999999'],
                'bn' => ['type'=>'string',  'valid'=>'', 'description'=>'SKU编号，不填自动生成'],
                'barcode' => ['type'=>'string',  'valid'=>'', 'description'=>'SKU条形码'],
                'spec_desc' => ['type'=>'string',  'valid'=>'', 'description'=>'销售属性值'],
                'spec_info' => ['type'=>'string',  'valid'=>'', 'description'=>'销售属性文字描述'],
                'outer_id' => ['type'=>'string',  'valid'=>'', 'description'=>'外部编码'],
                'sku_id' => ['type'=>'integer',  'valid'=>'', 'description'=>'商品sku_id，新增则不填'],//新增产品不需要此字段
            ], 'msg'=>'多规格商品，请选择销售属性'],
        );
        return $return;
    }

    public function itemCreate($params)
    {
        $result = kernel::single('sysitem_data_item')->add($params, $itemId);
        if($result)
        {
            event::fire('update.item', array($itemId));
        }
        return $result;
    }
}
