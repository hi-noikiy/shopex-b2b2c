<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_lottery extends topc_controller {

    public function index(){
        $params = array(
            'lottery_id'=>input::get('lottery_id'),
            'status'=>'active',
        );
        $lotteryInfo = app::get('topc')->rpcCall('promotion.lottery.get',$params);
        if(!$lotteryInfo){
            return kernel::abort(404);
        }
        $pagedata = $lotteryInfo;
        if($lotteryInfo['lottery_rules']){
            foreach ($lotteryInfo['lottery_rules'] as $key => $value) {
                $lotteryInfo['lottery_rules'][$key]['id'] = $key;
                if(isset($lotteryInfo['lottery_rules'][$key]['rate'])){
                    unset($lotteryInfo['lottery_rules'][$key]['rate']);
                }
            }
            $pagedata['bonusInfo'] = json_encode($lotteryInfo['lottery_rules']);
        }

        $user_id = userAuth::id();
        if($user_id){
            $jointNum = redis::scene('syspromotion')->get('lottery_joint_limit_'.$user_id.'-'.$lotteryInfo['lottery_id']);
            if(is_null($jointNum)){
                $pagedata['lottery_joint_limit'] = $lotteryInfo['lottery_joint_limit'];
                redis::scene('syspromotion')->set('lottery_joint_limit_'.$user_id.'-'.$lotteryInfo['lottery_id'],$lotteryInfo['lottery_joint_limit']);
            }else{
                $pagedata['lottery_joint_limit'] = $jointNum;
            }
        }else{
            $pagedata['lottery_joint_limit'] = $lotteryInfo['lottery_joint_limit'];
        }

        $this->setLayout('lottery.html');

        return $this->page("topc/promotion/lottery.html",$pagedata);
    }

    //随机获取奖励
    public function getPrize(){

        $data = input::get();
        $user_id = userAuth::id();
        if(!$user_id){
            $msg = app::get('topc')->_('未登录,请登录！');
            $url = url::action('topc_ctl_passport@signin');
            return $this->splash(error,$url,$msg,true);
        }

        $params = array(
            'lottery_id'=>$data['lottery_id'],
            'status'=>'active',
        );
        $lotteryInfo = app::get('topc')->rpcCall('promotion.lottery.get',$params);
        $jointNum = redis::scene('syspromotion')->get('lottery_joint_limit_'.$user_id.'-'.$data['lottery_id']);
        try
        {
            if(!$lotteryInfo){
                throw new Exception(app::get('topc')->_('活动已结束！'));
            }
            if(intval($data['lottery_joint_limit'])<1){
                throw new Exception(app::get('topc')->_('抱歉，您的抽奖次数已用完！'));
            }
            if($data['last_modified_time'] != $lotteryInfo['modified_time']){
                throw new Exception(app::get('topc')->_('活动已变更，请刷新页面！'));
            }

            if(intval($jointNum) >0){
                $lottery_joint_limit = $jointNum -1;
                redis::scene('syspromotion')->set('lottery_joint_limit_'.$user_id.'-'.$data['lottery_id'],$lottery_joint_limit);
            }else{
                throw new Exception(app::get('topc')->_('抱歉，您的抽奖次数已用完！'));
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }

        foreach ($lotteryInfo['lottery_rules'] as $key => $value) {
            $arr[$key] = $value['rate'];
        }

        $prizeNum = $this->getRand($arr);
        $prizeInfo= $lotteryInfo['lottery_rules'][$prizeNum];
        $prizeInfo['lottery_joint_limit'] = $lottery_joint_limit;
        $prizeInfo['id'] = $prizeNum;
        //发放奖励
        $apiData['user_id'] = userAuth::id();
        $apiData['lottery_id'] = $data['lottery_id'];
        $apiData['bonus_type'] = $prizeInfo['bonus_type'];
        $apiData['prizeInfo'] = $lotteryInfo['lottery_rules'][$prizeNum];
        $apiData['lottery_name'] = $lotteryInfo['lottery_name'];
        $apiData['prizeInfo']['lottery_id'] = $data['lottery_id'];;
        try
        {
            if($prizeInfo['bonus_type'] != 'none'){
                $result = app::get('topc')->rpcCall('promotion.bonus.issue',$apiData);
                $prizeInfo['hongbaomoney'] = $result;
            }

        }
        catch(Exception $e)
        {
            $prizeInfo = $lotteryInfo['lottery_rules'][0];
            $prizeInfo['id'] = 0;
            $prizeInfo['lottery_joint_limit'] = $lottery_joint_limit;
            unset($prizeInfo['rate']);
            $msg = app::get('topc')->_('很遗憾，未中奖');

            $url = url::action('topc_ctl_lottery@index',array('lottery_id'=>$data['lottery_id']));
            return $this->splash('success',$url,$prizeInfo,true);
        }
        $url = url::action('topc_ctl_lottery@index',array('lottery_id'=>$data['lottery_id']));
        return $this->splash('success',$url,$prizeInfo,true);
    }

    //积分兑换抽奖次数
    public function getExchangeNum(){
        $data = input::get();
        try
        {
            $points = app::get('topc')->rpcCall('user.point.get',['user_id' => userAuth::id()]);
            if(intval($points['point_count']) < 1){
                throw new Exception(app::get('topc')->_('积分兑换失败！'));
            }
            $pointData = array(
                'user_id' => userAuth::id(),
                'type' => 'consume',
                'num' => $data['pointNum'],
                'behavior' => '积分兑换抽奖次数',
                'remark' => '积分兑换抽奖次数'
            );
            $result = app::get('topc')->rpcCall('user.updateUserPoint',$pointData);
            if(!$result){
                throw new Exception(app::get('topc')->_('积分兑换失败！'));
            }

            $user_id = userAuth::id();
            $jointNum = redis::scene('syspromotion')->get('lottery_joint_limit_'.$user_id.'-'.$data['lotteryid']);
            $lottery_joint_limit = $jointNum + 1;
            redis::scene('syspromotion')->set('lottery_joint_limit_'.$user_id.'-'.$data['lotteryid'],$lottery_joint_limit);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }
        return intval($lottery_joint_limit);
    }

    //获取奖项id算法
    public function getRand($proArr){
        $result = "";
        $proSum = array_sum($proArr);
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }

    //填写获奖信息弹框
    public function lottery_info_dialog()
    {
        return view::make('topc/promotion/lottery_dialog.html');
    }
}
