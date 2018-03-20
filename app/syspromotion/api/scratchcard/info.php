<?php
/**
 * ShopEx licence
 *
 * - promotion.scratchcard.get
 * - 获取单个转盘抽奖详情
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 *
 */
final class syspromotion_api_scratchcard_info {

    public $apiDescription = '获取单个刮刮卡详情';

    public function getParams()
    {
        $return['params'] = array(
            'scratchcard_id' => ['type'=>'int', 'valid'=>'required', 'title'=>'刮刮卡活动ID', 'description'=>'刮刮卡活动ID'],
            'user_id' => ['type'=>'int', 'valid'=>'', 'title'=>'会员ID', 'description'=>'会员ID,可以获取该会员剩余多少次机会'],
        );
        return $return;
    }

    /**
     * 获取转盘活动详情
     */
    public function get($params)
    {
        $scratchcardId = $params['scratchcard_id'];
        $userId = $params['user_id'];
        $result = kernel::single('syspromotion_scratchcard_object')->getScratchcard($scratchcardId, $userId);
        return $result;
    }
}

