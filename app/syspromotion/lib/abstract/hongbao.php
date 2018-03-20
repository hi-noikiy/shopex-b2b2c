<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

abstract class syspromotion_abstract_hongbao{

    abstract public function hongbaolistSetToRedis($hongbaoId, $data);
    abstract  public function getUserHongbao($userId, $money, $hongbaoData);

    public function __construct($object)
    {
        $this->baseHongbaoType = $object;
        $this->redis = redis::scene('hongbao');
    }


    public function getHongbaoInfo($hongbaoId, $data)
    {
        $data['getTotalMoney'] = ecmath::number_minus([$data['total_money'], $this->getRedis($this->createPayload('totalMoney',$hongbaoId))]);
        $data['getTotalNum'] =  (int)ecmath::number_minus([$data['total_num'], $this->redis->get($this->createPayload('totalNum',$hongbaoId))]);
        $data['useTotalMoney'] = $this->getRedis($this->createPayload('useTotalMoney',$hongbaoId));
        //$data['useTotalNum'] = (int)$this->redis->get($this->createPayload('useTotalNum',$hongbaoId));
        $data['refundMoney'] = $this->getRedis($this->createPayload('RefundMoney', $hongbaoId));
        $data['useRefundMoney'] = $this->getRedis($this->createPayload('useRefundMoney', $hongbaoId));

        foreach( $data['hongbao_list'] as $key=>&$value )
        {
            $payloadId = $this->createPayload('hongbaolist', $hongbaoId, $value['money']);
            $value['getNum'] = intval(ecmath::number_minus([$value['num'], $this->redis->get($payloadId)]));
        }

        return $data;
    }

    /**
     * 退还红包给用户
     *
     * @param int $hongbaoId 退还红包的红包ID
     * @param int $userId 退还用户ID
     * @param float $money 退还红包金额
     */
    public function refundUserHongbao($hongbaoId, $userId, $money)
    {
        //增加用户退还红包总金额
        $data = $this->baseHongbaoType->execRedisCommad('incrby', $this->createPayload('userRefundMoney', $hongbaoId, $userId), $this->getmoney($money));

        //增加红包ID对应的退还总金额
        $this->baseHongbaoType->execRedisCommad('incrby', $this->createPayload('RefundMoney', $hongbaoId), $this->getmoney($money));

        return $money;
    }

    /**
     * 使用退还的红包
     *
     * @param int $userId 使用红包用户ID
     * @param int $hongbaoId 使用红包的ID
     * @param float $money 红包金额
     */
    public function useRefundHongbao($userId, $hongbaoId, $money)
    {
        //使用用户退还红包总金额
        $userRefundMoney = $this->baseHongbaoType->execRedisCommad('decrBy', $this->createPayload('userRefundMoney', $hongbaoId, $userId), $this->getmoney($money));
        if( $userRefundMoney < 0 )
        {
            throw new \LogicException(app::get('syspromotion')->_('你红包金额不足'));
        }

        //增加红包ID对应的使用退还总金额
        $this->baseHongbaoType->execRedisCommad('incrby', $this->createPayload('useRefundMoney', $hongbaoId), $this->getmoney($money));

        return true;
    }

    /**
     * 使用红包
     *
     * @param int $userId 使用红包用户ID
     * @param int $hongbaoId 使用红包的ID
     * @param float $money 红包金额
     */
    public function useHongbao($userId, $hongbaoId, $money)
    {
        //用户已使用红包金额
        $userUseHongbaoMoney = $this->baseHongbaoType->execRedisCommad('incrby', $this->createPayload('userUseMoney', $hongbaoId, $userId), $this->getmoney($money));
        //用户领取的红包金额
        $userTotalHongbaoMoney = $this->getRedis($this->createPayload('userGetMoney', $hongbaoId, $userId));
        if( ($userUseHongbaoMoney/100) > $userTotalHongbaoMoney )
        {
            throw new \LogicException(app::get('syspromotion')->_('你红包金额不足'));
        }

        //用户使用对应红包总金额
        $this->baseHongbaoType->execRedisCommad('incrby', $this->createPayload('useTotalMoney', $hongbaoId), $this->getmoney($money));
        //用户使用对应红包总数量
        //$this->baseHongbaoType->execRedisCommad('incr', $this->createPayload('useTotalNum',$hongbaoId));

        return true;
    }

    public function createPayload(...$data)
    {
        return implode('_', $data);
    }

    /**
     * @brief redis存值
     *
     * @param $key 键值
     * @param $value 数据
     * @param $isInt 是否是整型
     *
     * @return
     */
    public function setRedis($key,$value,$isInt=true)
    {
        if(!$isInt)
        {
            $value = floatval(ecmath::number_multiple([$value,100]));
        }
        $value = intval($value);
        $this->redis->set($key, $value);
    }
    /**
     * @brief 获取redis存值
     *
     * @param $key 键值
     * @param $isInt 是否是整型
     *
     * @return
     */
    public function getRedis($key)
    {
        $data = ecmath::number_div([$this->redis->get($key),100]);
        return $data;
    }

    public function getmoney($money)
    {
        return floatval(ecmath::number_multiple([$money,100]));
    }
}


