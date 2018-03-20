<?php
class syspromotion_scratchcard_prize_point implements syspromotion_scratchcard_prize_interface
{

    public function receiveScratchcard($scratchcard, $prizeInfo, $scratchcard_result){

        return $prizeInfo;
    }

    public function exchangeScratchcard($scratchcard_result){
        $apiParams = [];
        $apiParams['user_id'] = $scratchcard_result['user_id'];
        $apiParams['type'] = 'obtain';
        $apiParams['num'] = $scratchcard_result['extend_data']['num'];
        $apiParams['behavior'] = '刮刮卡抽奖送积分';
        $apiParams['remark'] = '刮刮卡抽奖送积分';

        $res = app::get('syspromotion')->rpcCall('user.updateUserPoint',$apiParams);

        return $res;
    }

}
