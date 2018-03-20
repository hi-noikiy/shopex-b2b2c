<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class toputil_ctl_trafficStatic 
{

    /**
     * 生成网站流量统计log文件
     * @return json
     */
    public function stat()
    {
        if(!config::get('stat.disabled')){
            $data = input::get();
            $params['page'] = $data['type'];
            $params['page_rel_id'] = $data['id'];
            $params['use_platform'] = $data['use_platform'];
            $params['shop_id'] = $data['shop_id'];
            $params['remote_addr'] = $_SERVER['REMOTE_ADDR'];
            app::get('sysstat')->rpcCall('sysstat.traffic.data.create',$params);
        } 
    }
}
