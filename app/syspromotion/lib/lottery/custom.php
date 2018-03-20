<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
//自定义奖品

class syspromotion_lottery_custom implements syspromotion_interface_lottery
{
    public $bonusType='custom';

    /**
     * 自定义奖品
     */
    public function issue($data)
    {
        try
        {
            $data['bonusType'] = $data;
            $data['created_time'] = time();
            $data['loginName'] = userAuth::getLoginName();
            $data['bonus_desc'] = $data['prizeInfo']['bonus_desc'];
            $prizeInfo = array(
                'bonus_type'=>$data['prizeInfo']['bonus_type'],
                'bonus_desc'=>$data['prizeInfo']['bonus_desc'],
                'is_delivery'=>$data['prizeInfo']['is_delivery'],
                'img'=>$data['prizeInfo']['img'],
            );
            $data['prizeInfo'] = serialize($prizeInfo);

            $objMdlLotteryResult = app::get('syspromotion')->model('lottery_result');

            return $objMdlLotteryResult->save($data);
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }
}
