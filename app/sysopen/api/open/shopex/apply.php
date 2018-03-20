<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * open.shop.apply.bind
 */
class sysopen_api_open_shopex_apply {

    public $apiDescription = "店铺申请绑定shopex体系软件";

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'    => ['type'=>'int', 'valid'=>'required', 'example'=>'1','desc'=>'店铺ID'],
            'shop_name'  => ['type'=>'string', 'valid'=>'required', 'example'=>'oms','desc'=>'店铺名称'],
            'to_node_id' => ['type'=>'string', 'valid'=>'required', 'example'=>'1240914536','desc'=>'对方节点ID', 'msg'=>'请填写目标Shopex节点'],
            'node_type'  => ['type'=>'string', 'valid'=>'required', 'example'=>'ecos.ome','desc'=>'对方节点类型'],
        );
        return $return;
    }

    public function handle($params)
    {
        return kernel::single('sysopen_shopex_bind')->applyBind($params);
    }
}


