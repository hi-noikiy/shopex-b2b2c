<?php

class topapi_api_v1_theme_modules implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取页面模块配置';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'tmpl' => ['type'=>'string', 'valid'=>'required|in:index', 'example'=>'index', 'desc'=>'返回页面的模块配置'],
        ];
    }

    public function handle($params)
    {
        $tmpl = $params['tmpl'];
        $modules['modules'] = app::get('topapi')->rpcCall('sysapp.theme.get', array('tmpl'=>$tmpl));
        // 如果params为空则将其值设为空数组，防止app端解析崩溃
        foreach ($modules['modules'] as &$v)
        {
            if( $v['widget'] == 'double_pics' && $v['params'] && !empty($v['params']['pic']) )
            {
                $v['params']['pic'] = array_values($v['params']['pic']);
            }

            if( !$v['params'] ) $v['params'] = (object)[];
        }
        $url = "http://images.bbc.shopex123.com/images/33/e2/ff/56e438276be7f2d7ae2b7bede423048f6847e906.png";
        $modules['brand_logo'] = app::get('sysconf')->getConf('sysconf_setting.wap_logo') ? app::get('sysconf')->getConf('sysconf_setting.wap_logo') : $url;

        return $modules;
    }

}
