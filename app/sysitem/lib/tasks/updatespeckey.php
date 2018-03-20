<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysitem_tasks_updatespeckey extends base_task_abstract implements base_interface_task{

    public function exec($params=null)
    {
        $db = app::get('sysitem')->database();
        $rs = $db->executeQuery('SELECT sku_id,spec_info,spec_desc FROM sysitem_sku WHERE sku_id>='.$params['start'] . ' AND ' .' sku_id<='.$params['end'])->fetchAll();
        foreach ($rs as $val) {
            if($val['spec_info']){
                $spec_desc = unserialize($val['spec_desc']);
                sort($spec_desc['spec_desc']['spec_value_id']);
                $specvalueid = $spec_desc['spec_value_id'];
                $specKey = implode('_', $specvalueid);
                $sql = 'UPDATE `sysitem_sku` set `spec_key`="'.$specKey.'" WHERE `sku_id`='.$val['sku_id'];
                $db->executeQuery( $sql );
            }
        }

        // $db = app::get('sysitem')->database();
        // $rs = $db->executeQuery('SELECT sku_id,spec_info,spec_desc FROM sysitem_sku WHERE sku_id>='.$params['start'] . ' AND ' .' sku_id<='.$params['end'])->fetchAll();
        // $sql = '';
        // foreach ($rs as $val) {
        //     if($val['spec_info']){
        //         $spec_desc = unserialize($val['spec_desc']);
        //         sort($spec_desc['spec_desc']['spec_value_id']);
        //         $specvalueid = $spec_desc['spec_value_id'];
        //         $specKey = implode('_', $specvalueid);
        //         $sql .= 'UPDATE `sysitem_sku` set `spec_key`="'.$specKey.'" WHERE `sku_id`='.$val['sku_id'].';';
        //     }
        // }
        // if($sql){
        //     $db->executeQuery( $sql );
        // }
    }


}
