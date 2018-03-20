<?php
/**
 * 开通新店铺装修
 */
class sysshop_events_listeners_openNewDecorate
{
    public function open($shopId)
    {
        redis::scene('shopDecorate')->hset('wapdecorate_status','shop_'.$shopId, 'open');
        redis::scene('shopDecorate')->hset('appdecorate_status','shop_'.$shopId, 'open');

        return true;
    }
}