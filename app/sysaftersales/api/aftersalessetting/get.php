<?php

class sysaftersales_api_aftersalessetting_get {

    /**
     * aftersales.setting.get
     * 接口作用说明
     */
    public $apiDescription = '获取指定类目退货换货设置详情';
    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'cat_id' => ['type'=>'string','valid'=>'required', 'description'=>'类目ID'],
        );
        return $return;
    }

    public function get($params)
    {
        $refundSetting = unserialize(app::get('sysaftersales')->getConf('refundSetting'));
        $changingSetting = unserialize(app::get('sysaftersales')->getConf('changingSetting'));
        $objMdlAftersales = app::get('sysaftersales')->model('setting');
        $catData = $objMdlAftersales->getRow('*', ['cat_id'=>$params['cat_id']]);
        $return = array(
            'cat_id'=> $params['cat_id'],
            'changing_active' => $changingSetting['status'],
            'refund_active' => $refundSetting['status'],
        );

        if($refundSetting['status'])
        {
            if($catData && $catData['refund_days'] >= 0){
                $return['refund_days'] = $catData['refund_days'];
            }else{
                $return['refund_days'] = $refundSetting['day'];
            }
        }

        if($changingSetting['status'])
        {
            if($catData && $catData['changing_days'] >= 0){
                $return['changing_days'] = $catData['changing_days'];
            }else{
                $return['changing_days'] = $changingSetting['day'];
            }
        }

        return $return;
    }
}
