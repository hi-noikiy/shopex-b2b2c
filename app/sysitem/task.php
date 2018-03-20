<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysitem_task{

    public function post_install($options)
    {
        kernel::single('base_initial', 'sysitem')->init();
        kernel::single('sysitem_command_store')->command_syncToRedis();
    }

    public function post_update($dbver)
    {
        if($dbver['dbver']<0.6){
            $db = app::get('sysitem')->database();
            $itemList = $db->executeQuery('select shop_id,item_id from sysitem_item')->fetchAll();
            foreach ($itemList as $key => $value)
            {
                app::get('sysitem')->model('item_status')->update(array('shop_id'=>$value['shop_id']),array('item_id'=>$value['item_id']));
            }
        }
        if($dbver['dbver'] < 0.7)
        {
            $db = app::get('sysitem')->database();
            $itemList = $db->executeQuery('SELECT item_id,store,freez FROM sysitem_item');
            foreach ($itemList as $key => $value) {
                $id = $value['item_id'];
                $store = $value['store'] ? $value['store'] : 0;
                $freez = $value['freez']? $value['freez'] : 0;
                $list = $db->executeQuery('SELECT item_id,store,freez FROM sysitem_item_store WHERE item_id=?',[$id])->fetch();
                if(!$list['item_id'])
                {
                    $db->executeUpdate('insert into sysitem_item_store(item_id,store,freez) value (?,?,?)',[$id,$store,$freez]);
                }
            }
        }
        // 更新原来的促销关联id
        if($dbver['dbver'] < 0.9)
        {
            $db = app::get('sysitem')->database();
            $itemList = $db->executeQuery('SELECT * FROM sysitem_item_tag_promotion');
            foreach ($itemList as $key => $value)
            {
                $itemId = $value['item_id'];
                $pids = explode(',', $value['promotion_ids']);
                foreach($pids as $pid)
                {
                    $db->executeUpdate('insert into sysitem_item_promotion(item_id,promotion_id) value (?,?)',[$itemId,$pid]);
                }
            }
        }
        if($dbver['dbver'] < 1.0)
        {
            $db = app::get('sysitem')->database();
            $itemList = $db->executeQuery('SELECT item_id,bn,nospec FROM sysitem_item where nospec=?',['1'])->fetchAll();;
            foreach ($itemList as $key => $value)
            {
                $db->executeUpdate('UPDATE sysitem_sku SET bn = ? WHERE item_id = ?', [$value['bn'], $value['item_id']]);
            }
        }

        if( $dbver['dbver'] < 1.1 )
        {
            $db = app::get('sysitem')->database();
            $db->executeUpdate("update sysitem_item set shop_cat_id=concat(',',shop_cat_id,',')");
        }

        if( $dbver['dbver'] < 1.2 )
        {
            $db = app::get('sysitem')->database();
            $itemList = $db->executeQuery('SELECT item_id,shop_id,cat_id,brand_id,shop_cat_id,image_default_id,spec_desc FROM sysitem_item ')->fetchAll();;
            foreach($itemList as $key=>$value)
            {
                if(unserialize($value['spec_desc']))
                {
                    $spec_desc[$value['item_id']] = unserialize($value['spec_desc'])[1];
                    unset($value['spec_desc']);
                }
                app::get('sysitem')->model('sku')->update($value,array('item_id'=>$value['item_id']));
            }
            /*
             * 冗余货品图片时，色卡图片和商品图片不能分辨，暂时不适用规格图片
            $itemSku = $db->executeQuery('SELECT item_id,sku_id,spec_desc FROM sysitem_sku')->fetchAll();;
            foreach($itemSku as $key=>$value)
            {
                if(unserialize($value['spec_desc']))
                {
                    $spec_value_id = unserialize($value['spec_desc'])['spec_value_id'];
                    foreach($spec_value_id as $v)
                    {
                        $image_id = $spec_desc[$value['item_id']][$v]['spec_image_url'];
                        if($image_id)
                        {
                            $db->executeUpdate("update sysitem_sku set image_default_id=? where sku_id=?",[$image_id,$value['sku_id']]);
                        }
                    }
                }
            }
             */
        }
        if( $dbver['dbver'] < 1.3 ){
            $db = app::get('sysitem')->database();
            $rs = $db->executeQuery('SELECT max(sku_id) as maxnum FROM sysitem_sku ')->fetch();
            $pages = ceil($rs['maxnum']/1000);

            for($i=0;$i<$pages;$i++){
                $start = $i*1000+1;
                $params = ['start'=>$start, 'end'=>$start+999];
                queue::push('sysitem_tasks_updatespeckey','sysitem_tasks_updatespeckey', $params);
            }
        }

        //升级到1.4的时候触发
        if( $dbver['dbver'] < 1.4 ){
            kernel::single('sysitem_command_store')->command_syncToRedis();
        }
    }

}

