<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
//定额红包

class syspromotion_lottery_point implements syspromotion_interface_lottery{

    public $bonusType='point';

    /**
     * 发放积分
     */
    public function issue($data)
    {
        $pointData['user_id'] = $data['user_id'];
        $pointData['type'] = 'obtain';
        $pointData['num'] = $data['prizeInfo']['num'];
        $pointData['behavior'] = '抽奖送积分';
        $pointData['remark'] = '抽奖送积分';
        try
        {
            $result = app::get('syspromotion')->rpcCall('user.updateUserPoint',$pointData);
            $objMdlLotteryResult = app::get('syspromotion')->model('lottery_result');
            $data['bonusType'] = $data;
            $data['created_time'] = time();
            $data['loginName'] = userAuth::getLoginName();
            $data['bonus_desc'] = $data['prizeInfo']['num'].'积分';
            $prizeInfo = array(
                'bonus_type'=>'point',
                'bonus_desc'=>'积分',
                'num'=>$data['prizeInfo']['num'],
            );
            $data['prizeInfo'] = serialize($prizeInfo);

            return $objMdlLotteryResult->save($data);
        }
        catch(Exception $e)
        {
            throw $e;
        }

    }
}

   