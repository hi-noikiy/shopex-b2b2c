<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syspromotion_task{

    public function post_install($options)
    {
        kernel::single('base_initial', 'syspromotion')->init();
    }

    public function post_update($dbver)
    {
        // 更新原来的促销关联id
        if($dbver['dbver'] < 0.2)
        {
            $db = app::get('syspromotion')->database();
            $xyList = $db->executeQuery('SELECT xydiscount_id,limit_number,discount FROM syspromotion_xydiscount where end_time>'.time())->fetchAll();
            foreach ($xyList as $key => $value)
            {
                $joinxydiscount = $value['limit_number'].'|'.$value['discount'];
                $db->executeUpdate('UPDATE syspromotion_xydiscount SET condition_value = ? WHERE xydiscount_id = ?', [$joinxydiscount, $value['xydiscount_id']]);
            }
        }

        if($dbver['dbver'] < 0.4)
        {
            $db = app::get('syspromotion')->database();
            $couponList = $db->executeQuery('SELECT coupon_id,shop_id FROM syspromotion_coupon')->fetchAll();;
            foreach ($couponList as $key => $value)
            {
                $db->executeUpdate('UPDATE syspromotion_coupon_item SET shop_id = ? WHERE coupon_id = ?', [$value['shop_id'], $value['coupon_id']]);
            }
        }

        if($dbver['dbver'] < 0.5)
        {
            $list = [
                'userUseMoney', 'userRefundMoney', 'RefundMoney' ,  'useRefundMoney', 'useTotalMoney', 'totalMoney', 'userGetMoney', 'totalMoney',
            ];

            foreach( $list as $key )
            {
                $this->__resetHongbao($key);
            }
        }
    }

    private function __resetHongbao($resetKey)
    {
        $redis = redis::scene('hongbao');
        $userUseListKey = $redis->keys($resetKey.'*');
        foreach( $userUseListKey as $key )
        {
            $key = substr($key,8);
            $money = $redis->get($key);
            if( $money && is_float($money+0) )
            {
                $money = round($money,3);
                $redis->set($key, $this->__getmoney($money));
                logger::info('键值为['.$key.']的金额由['.$money.']改为['.$this->__getmoney($money).']');
                error_log('键值为['.$key.']的金额由['.$money.']改为['.$this->__getmoney($money).']', 3, DATA_DIR.'/hongbao.log');
            }
        }
    }

    private function __getmoney($money)
    {
        return floatval(ecmath::number_multiple([$money,100]));
    }

}

