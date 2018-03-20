<?php

class sysaftersales_api_aftersalessetting_list {

    /**
     * aftersales.cat.setting.list
     * 接口作用说明
     */
    public $apiDescription = "获取特殊类目售后设置列表";
    public $use_strict_filter = true; //是否启用严格过滤

    public function getParams()
    {
        $return['params'] = array(
            'fields'=> ['type'=>'field_list','valid'=>'', 'description'=>'获取需要返回的字段'],
            'aftersalestype' => ['type'=>'string', 'valid'=>'', 'description'=>'售后类型'],
        );
        return $return;
    }

    public function getList($params)
    {
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        if($params['aftersalestype'] == 'refund')
        {
            $filter['refund_days|noequal'] = -1;
        }
        elseif ($params['aftersalestype'] == 'changing')
        {
            $filter['changing_days|noequal'] = -1;
        }

        $objMdlAftersales = app::get('sysaftersales')->model('setting');
        $settingData = $objMdlAftersales->getList($params['fields'],$filter);

        return $settingData;
    }

}

