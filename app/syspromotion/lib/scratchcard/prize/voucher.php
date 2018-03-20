<?php
class syspromotion_scratchcard_prize_voucher implements syspromotion_scratchcard_prize_interface
{

    public function receiveScratchcard($scratchcard, $prizeInfo, $scratchcard_result)
    {
        $gradeInfo = app::get('syspromotion')->rpcCall('user.grade.basicinfo',['user_id'=>$scratchcard_result['user_id']]);

        $apiParams = [
            'voucher_id' => $prizeInfo['voucher_id'],
            'user_id'    => $scratchcard_result['user_id'],
            'grade_id'   => $gradeInfo['grade_id'],
        ];

        $voucherCode = app::get('syspromotion')->rpcCall('promotion.voucher.code.get', $apiParams);

        return $voucherCode;
    }

    public function exchangeScratchcard($scratchcard_result){
        if($scratchcard_result['extend_data'])
        {
            $apiParams = [];
            $apiParams = [
                'user_id' => $scratchcard_result['user_id'],
                'voucher_id' => $scratchcard_result['extend_data']['voucher_id'],
                'voucher_code' => $scratchcard_result['extend_data']['voucher_code'],
                'obtain_desc' => '刮刮卡抽奖送购物券',
            ];

            $res = app::get('syspromotion')->rpcCall('user.voucher.code.get', $apiParams);
        }

        return $res;
    }

}
