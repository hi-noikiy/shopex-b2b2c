<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
//随机红包
class syspromotion_hongbao_stochastic extends syspromotion_abstract_hongbao{


  /**
     * 将生成红包list结构保存到Redis
     *
     * @param int $hongbaoId
     * @param array $data
     */
    public function hongbaolistSetToRedis($hongbaoId, $data)
    {
        $hongbao = reset($data['hongbao_list']);
        $money = $hongbao['money'];
        $num = $hongbao['num'];

        $payLadId = $this->createPayload('stochasticHongbao',$hongbaoId);

        //先清空该红包队列
        $this->redis->del($payLadId);

        for($j=$hongbao['num'];$j>0;$j--)
        {
            $randMoney = floatval($this->__getRandomMoney($hongbao));
            $this->redis->rpush($payLadId,$randMoney);
        }

        //验证红包是否分配成功
        $redPackage = $this->redis->lrange($payLadId,0,-1);
        if(count($redPackage) < $num)
        {
            throw new \LogicException(app::get('syspromotion')->_('分配红包出错'));
        }

        //记录红包数量
        //保存红包总金额 总数量
        $this->setRedis($this->createPayload('hongbaolist',$hongbaoId,$money), $num);
        $this->setRedis($this->createPayload('totalMoney',$hongbaoId), $data['total_money'],false);
        $this->setRedis($this->createPayload('totalNum',$hongbaoId), $data['total_num']);
        return true;
    }

    /**
     * 给用户发放红包
     */
    public function getUserHongbao($userId, $money, $hongbaoData)
    {
        $hongbaoId = $hongbaoData['hongbao_id'];
        $payloadId = $this->createPayload('hongbaolist', $hongbaoId, $money);
        $hongbaoTypeNum = $this->baseHongbaoType->execRedisCommad('decr', $payloadId);
        if( $hongbaoTypeNum < 0 )
        {
            throw new \LogicException(app::get('syspromotion')->_('红包已被领完'), 200);
        }

        //获取该用户的随机红包，并派发
        $money = $this->redis->lpop($this->createPayload('stochasticHongbao', $hongbaoId));
        //$redPackage = $this->redis->lrange($this->createPayload('stochasticHongbao', $hongbaoId),0,-1);
        if(!$money)
        {
            throw new \LogicException(app::get('syspromotion')->_('红包已被领完'), 200);
        }

        //用户红包金额  判断用户是否有领取资格
        $userGetHongbaoMoney = $this->baseHongbaoType->execRedisCommad('incrby', $this->createPayload('userGetMoney', $hongbaoId, $userId), $this->getmoney($money));
        if( $userGetHongbaoMoney > $this->getmoney($hongbaoData['user_total_money']))
        {
            $this->redis->rpush($this->createPayload('stochasticHongbao', $hongbaoId),$money );
            throw new \LogicException(app::get('syspromotion')->_('你已达到红包领取金额最大值'));
        }

        //用户红包数量
        $userGetHongbaoNum = $this->baseHongbaoType->execRedisCommad('incr', $this->createPayload('userGetNum', $hongbaoId, $userId) );
        if( $userGetHongbaoNum > $hongbaoData['user_total_num'] )
        {
            $this->redis->rpush($this->createPayload('stochasticHongbao', $hongbaoId),$money );
            throw new \LogicException(app::get('syspromotion')->_('你已达到红包领取数量最大值'));
        }

        //红包总量剩余金额
        $totalMoney = $this->baseHongbaoType->execRedisCommad('decrby', $this->createPayload('totalMoney',$hongbaoId), $this->getmoney($money));
        //红包总量剩余数量
        $totalNum = $this->baseHongbaoType->execRedisCommad('decr', $this->createPayload('totalNum',$hongbaoId));
        if( $totalMoney < 0 || $totalNum < 0 )
        {
            throw new \LogicException(app::get('syspromotion')->_('红包已被领完'), 200);
        }
        return $money;
    }

    private function __getRandomMoney(&$redPackage)
    {
        $money = ecmath::number_minus($redPackage['money'])*100;
        $min = ($this->carryBit())*100;
        $num = $redPackage['num'];

        if($money <= 0)
        {
            throw new \LogicException(app::get('syspromotion')->_('分配红包出错'));
        }
        if($money == $num)
        {
            return ecmath::number_div([$money,$num])/100;
        }

        if($num == 1)
        {
            return ecmath::number_minus($money/100);
        }

        //领红包的最高上限
        $max = $money/$num*2;
        $randMoney = mt_rand($min,$max); //领取的红包金额
        if(!$randMoney)
        {
            $randMoney = $min;
        }

        $redPackage['num']--;
        $redPackage['money'] = ecmath::number_minus([$money,$randMoney])/100;

        return ecmath::number_div([$randMoney,100]);
    }

     public function carryBit()
    {
        //$decimalType = app::get('ectools')->getConf('system.money.operation.carryset');
        $decimalDigit = app::get('ectools')->getConf('system.money.decimals');
        switch($decimalDigit)
        {
        case 0:
            $min = 1;
            break;
        case 1:
            $min = 0.1;
            break;
        case 2:
            $min = 0.01;
            break;
        case 3:
            $min = 0.001;
            break;
        }
        return $min;
    }
}
