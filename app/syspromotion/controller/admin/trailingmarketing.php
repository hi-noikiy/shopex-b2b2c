<?php

/**
 * trailingmarketing.php
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syspromotion_ctl_admin_trailingmarketing extends desktop_controller
{
    public function index()
    {
        $marketingSetting = unserialize(app::get('syspromotion')->getConf('trailingmarketing'));
        $apiParams = [
            'fields' => 'scratchcard_id,scratchcard_name',
            'used_platform' => $marketingSetting['platform'],
        ];
        $scratchcard = app::get('syspromotion')->rpcCall('promotion.scratchcard.list',$apiParams);
        $pagedata['scratchcardList'] = $scratchcard['data'];
        $pagedata['marketing_type'] = [
            'scratchcard' => '刮刮卡',
        ];
        $pagedata['marketing'] = $marketingSetting;

        return $this->page('syspromotion/trailingmarketing/setting.html', $pagedata);
    }

    // 保存尾随营销设置
    public function save(){
        $conf = input::get('marketing');
        try
        {
            if($conf['status'] && $conf['type'] == 'scratchcard' && !$conf['scratchcard_id']){
                throw new Exception(app::get('syspromotion')->_('当前无可用刮刮卡，请添加！'));
            }
            app::get('syspromotion')->setConf('trailingmarketing', serialize($conf));
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg, 'ture');
        }

        $msg = app::get('syspromotion')->_('设置成功！');
        return $this->splash('success',null,$msg,'true');
    }

    public function ajaxGetscratchcardList()
    {
        $platform = input::get('platform')? input::get('platform') : 0;
        $apiParams = [
            'fields' => 'scratchcard_id,scratchcard_name',
            'used_platform' => $platform,
        ];
        $scratchcard = app::get('syspromotion')->rpcCall('promotion.scratchcard.list',$apiParams);
        $pagedata['scratchcardList'] = $scratchcard['data'] ;

        return response::json($pagedata);
    }
}

