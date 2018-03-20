<?php
class syspromotion_scratchcard_prize_hongbao implements syspromotion_scratchcard_prize_interface
{

    public function receiveScratchcard($scratchcard, $prizeInfo, $scratchcard_result){
        $apiParams = [];
        $apiParams['user_id'] = $scratchcard_result['user_id'];
        $apiParams['hongbao_id'] = $prizeInfo['hongbaoid'];
        $apiParams['money'] = $prizeInfo['hongbaomoney'];
        $apiParams['hongbao_obtain_type'] = 'userGet';

        $res = app::get('syspromotion')->rpcCall('user.hongbao.tmp.get', $apiParams);

        return $res;
    }

    public function exchangeScratchcard($scratchcard_result){
        $apiParams = [];
        $apiParams['user_id'] = $scratchcard_result['user_id'];
        $apiParams['tmphongbao_id'] = $scratchcard_result['extend_data']['user_tmp_hongbao_id'];

        $res = app::get('syspromotion')->rpcCall('user.hongbao.tmp.receive', $apiParams);

        return $res;
    }

}
