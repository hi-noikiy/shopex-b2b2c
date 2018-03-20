<?php
interface syspromotion_scratchcard_prize_interface
{
    //刮刮卡生成的时候出发的动作
    public function receiveScratchcard($scratchcard, $prizeInfo, $scratchcard_result);

    //刮刮卡领取的时候触发的动作
    public function exchangeScratchcard($scratchcard_result);

}
