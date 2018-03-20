<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2014-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysfinance_data_guaranteeMoney
{
    public function __construct()
    {
        pamAccount::setAuthType('desktop');
        $this->objMdlGuranteeMoney = app::get('sysfinance')->model('guaranteeMoney');
    }
    /**
     * 单店保证金额度调整
     *
     * @param array postdata 不能为空
     * @return boolean true or false
     */
    public function setGuaranteeMoney($params)
    {
        $guaranteeMoney = $this->objMdlGuranteeMoney->getRow('*', ['shop_id'=>$params['shop_id']]);
        $guaranteeMoneyBalance = $guaranteeMoney['guarantee_money_balance'];
        $accountStatus = $this->__getAccountStatus($guaranteeMoney['guarantee_money'], $guaranteeMoneyBalance, $params['shop_id']);
        $data = array(
            'shop_id' =>$params['shop_id'],
            'guarantee_money' => $params['guarantee_money'],
            'account_status'  => $accountStatus,
            'modified_time' => time(),
        );

        try
        {
            $result = $this->objMdlGuranteeMoney->update($data,['shop_id'=>$params['shop_id']]);

            if($result){
                $logInfo = array(
                    'shop_id' => $params['shop_id'],
                    'money' => $params['guarantee_money'],
                    'op_type' =>'configchange',
                    'memo' => '平台设置单店保证金额度',
                    'admin_userid' => pamAccount::getAccountId(),
                    'admin_username' => pamAccount::getLoginName(),
                    'created_time' => time(),
                );
                $this->setOplog($logInfo);
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            throw new \LogicException($msg);
        }

        return $result;
    }

    /**
     * 保证金余额调整
     *
     * @param array postdata 不能为空
     * @return boolean true or false
     */
    public function adjustBalance($params)
    {
        $guaranteeMoney = $this->objMdlGuranteeMoney->getRow('*', ['shop_id'=>$params['shop_id']]);

        if($params['op_type'] =='recharge')
        {
            $guaranteeMoneyBalance=$guaranteeMoney['guarantee_money_balance'] + $params['money'];
        }
        elseif ($params['op_type'] =='expense')
        {
            $guaranteeMoneyBalance=$guaranteeMoney['guarantee_money_balance'] - $params['money'];
        }

        $accountStatus = $this->__getAccountStatus($guaranteeMoney['guarantee_money'], $guaranteeMoneyBalance, $params['shop_id']);
        $data = array(
            'shop_id' =>$params['shop_id'],
            'guarantee_money_balance' => $guaranteeMoneyBalance,
            'modified_time' => time(),
            'account_status'  => $accountStatus,
        );

        try
        {
            $result = $this->objMdlGuranteeMoney->update($data,['shop_id'=>$params['shop_id']]);

            if($result){
                $logInfo = $params;
                $logInfo['created_time'] =time();
                $logInfo['admin_userid'] = pamAccount::getAccountId();
                $logInfo['admin_username'] = pamAccount::getLoginName();
                $this->setOplog($logInfo);

                if($accountStatus != 0 )
                {
                    $notifyData = array(
                        'shop_id' => $params['shop_id'],
                        'account_status' => $accountStatus,
                    );
                    event::fire('guaranteemoney_status.notify',[$notifyData]);
                }

            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            throw new \LogicException($msg, 1);
        }

        return $result;

    }

    /**
     *@brief 判断保证金账户状态(余额低于预警线，)
     *
     *@param $guaranteeMoney 保证金额度
     *@param $guaranteeMoneyBalance  保证金余额
     *@param $shopId  店铺id
     *
     *@return
     *
     */
    private function __getAccountStatus($guaranteeMoney, $guaranteeMoneyBalance, $shopId)
    {
        $shopInfo = app::get('sysfinance')->rpcCall('shop.type.getinfo', ['shop_id'=>$shopId]);

        $guaranteeMoney_warningline = $guaranteeMoney*$shopInfo['guarantee_money_warningline']/100;
        if($guaranteeMoneyBalance >= $guaranteeMoney_warningline){
            $account_status = 0;

        }elseif ($guaranteeMoneyBalance >=0 && $guaranteeMoneyBalance <$guaranteeMoney_warningline) {
            $account_status = 1;
        }else{
            $account_status = 2;
        }

        return $account_status;
    }

    /**
     *@brief 增加保证金操作记录
     *
     *@param
     *
     *@return
     *
     */
    private function setOplog($params)
    {
        try
        {
            return app::get('sysfinance')->model('guaranteeMoney_oplog')->insert($params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            throw new \LogicException($msg);
        }
    }
}
