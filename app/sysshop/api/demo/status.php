<?php
class sysshop_api_demo_status{

    public $apiDescription = "更新店铺状态";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'integer','valid'=>'required|min:1','description'=>'店铺id'],
            'status' => ['type'=>'string','valid'=>'in:active,dead','description'=>'提交状态'],
            'close_reason' => ['type'=>'string','valid'=>'required_if:status,dead','description'=>'店铺关闭原因'],
        );
        return $return;
    }
    public function update($params)
    {
        $objShop = kernel::single('sysshop_data_shop');
        $result = $objShop->updateShopStatus($params);

        return $result;
    }
}
