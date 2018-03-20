<?php
/**
 * topapi
 *
 * -- common.getsetting
 * -- 用于获取系统配置
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_common_getsetting implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '用于获取系统配置';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'settingname' => ['type'=>'string', 'valid'=>'required', 'desc'=>'配置名'],
        ];
    }

    /**
     * 用于获取系统配置
     * @param  array $data 入参
     * @return array       配置的值
     */
    public function handle($data)
    {
        $settingnames = explode(',', $data['settingname']);
        $ret = [];
        foreach ($settingnames as $v)
        {
            $ret[$v] = $this->__getConf($v);
        }
        return $ret;
    }

    // 配置名，返回配置的值
    private function __getConf($settingname)
    {
        switch ($settingname) {
            case 'check_register_multipletype':
                $set = app::get('sysconf')->getConf('user.account.register.multipletype');
                break;
            default:
                throw new \LogicException( app::get('topai')->_("参数名{$settingname}不存在") );
                break;
        }

        return $set;
    }

}

