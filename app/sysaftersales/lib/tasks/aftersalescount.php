<?php
/**
 * ShopEx licence
 * 计算商品售后次数
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysaftersales_tasks_aftersalescount extends base_task_abstract implements base_interface_task{

    // 每个队列执行100条订单信息
    var $limit = 100;
    public function exec($params=null)
    {
        logger::info('统计30天内商品售后次数计算任务开始！');
        // 初始化所有 sysitem_item_count 表的月售后数量值初始化为0
        app::get('sysitem')->model('item_count')->update(['aftersales_month_count'=>0]);
        $offset = 0;
        $filter = [
            'modified_time|bthan' => time()-3600*24*30,
            'modified_time|sthan' => time(),
            'status' => 2,
        ];
        while( $data = $this->__aftersalesItemIdsList($filter, $offset) ){
            $offset++;
            // 把分页得到的Id加入相关队列
            $this->__calcAftersalesCount($data);
        }
        logger::info('统计30天内商品售后次数计算任务结束！');
    }

    /**
     * 分页获取ID
     * @param  int $filter 过滤条件
     * @param  int $offset 页数
     * @return array        返回商品ID
     */
    private function __aftersalesItemIdsList($filter, $offset)
    {
        $itemIds = app::get('sysaftersales')->model('aftersales')->getList('item_id', $filter, $offset*$this->limit, $this->limit, ' modified_time ASC ');
        return $itemIds;
    }

    //执行，更新商品售后次数
    private function __calcAftersalesCount($data)
    {
        foreach ($data as $v)
        {
            $db = app::get('sysitem')->database()->createQueryBuilder();
            $db->update('sysitem_item_count');

            $stmt = $db->set('aftersales_month_count', 'aftersales_month_count + 1')
                       ->where($db->expr()->in('item_id', $v['item_id']))
                       ->execute();
        }
        return true;
    }

}