<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取单条满折促销数据
 */
final class syspromotion_api_fulldiscount_fulldiscountGet {

    public $apiDescription = '获取单条满折促销数据';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'               => ['type' => 'int', 'valid'    => '', 'example' => '', 'desc' => '店铺ID,user_id和shop_id必填一个'],
            'fulldiscount_id'       => ['type' => 'int', 'valid'    => '', 'example' => '', 'desc' => '满折促销id'],
            'fulldiscount_itemList' => ['type' => 'string', 'valid' => '', 'example' => '', 'desc' => '满折促销的商品'],
        );

        return $return;
    }

    /**
     *  获取单条满折促销信息
     * @param  array $params 筛选条件数组
     * @return array         返回一条满折促销信息
     */
    public function fulldiscountGet($params)
    {
        $fulldiscountInfo = kernel::single('syspromotion_data_object')->setPromotion('fulldiscount', $params['shop_id'])->getPromoitonInfo($params['fulldiscount_id']);
        $fulldiscountInfo['valid'] = $this->__checkValid($fulldiscountInfo);
        if($params['fulldiscount_itemList'])
        {
            $fulldiscountItems = kernel::single('syspromotion_data_object')->setPromotion('fulldiscount', $params['shop_id'])->getPromtionItems($params['fulldiscount_id']);
            $fulldiscountInfo['itemsList'] = $fulldiscountItems;
        }

        return $fulldiscountInfo;
    }

    // 检查满折是否可用
    private function __checkValid(&$fulldiscountInfo)
    {
        $now = time();
        if( ($fulldiscountInfo['fulldiscount_status']=='agree') && ($fulldiscountInfo['start_time']<$now) && ($fulldiscountInfo['end_time']>$now) )
        {
            return true;
        }
        return false;
    }

}

