<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    /*
    |--------------------------------------------------------------------------
    | System Debug Mode
    |--------------------------------------------------------------------------
    |
    | 当开启调试模式, 详细的错误会暴露出来, 否则会提示错误页
    | 对应原系统: DEBUG_PHP + DEBUG_CSS + DEBUG+JS
    |
    */
    'debug' => true,

    /*
    |--------------------------------------------------------------------------
    | System Url
    |--------------------------------------------------------------------------
    |
    | 定义此URL会在几种情况下使用
    | 1. 执行命令行
    | 2. 执行系统级命令
    | 3. 当路由定义了 domain, 但当有部分路由没有定义domain时. 会使用此url作为domain 
    | 对应原系统: BASE_URL
    |
    */
    'url' => '%URL%',

    /*
    |--------------------------------------------------------------------------
    | 二级域名配置
    |--------------------------------------------------------------------------
    | 注意事项：
    | 1、开启了nginx的rewrite伪静态！！！
    | 2、一定要支持泛域名解析！！！
    | 3、一定要配置正确的域名根部！！！
    |
    | subdomain_enabled  是否开启二级域名，开启二级域名需要web服务器配置域名泛解析
    |
    | subdomain_limits 店铺二级域名可修改次数,商户最多可以修改几次域名
    |
    | subdomain_basic 二级域名简略域名,如您的域名是www.example.com，这里就填写example.com
    | 如果您的域名为 www.example.com ,则此项填写 example.com ,则店铺的二级域名类似于 shop3333.example.com
    | 如果您的域名本身就是二级域名,例如为 mall.example.com ,则此项一般要填写为 mall.example.com ，则店铺的二级域名类似于 shop3333.mall.example.com，以此类推
    |
    */
    'subdomain_enabled' => false, //默认不开启
    'subdomain_basic' => '', //默认为空，如果开启了店铺二级域名，这里填写错误的话，会导致系统访问错误，请慎重
    'subdomain_limits' => 3, //默认3次

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    | 对应原系统:DEFAULT_TIMEZONE
    |
    */

    'timezone' => '%TIMEZONE%',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    | 对应原系统: LANG
    |
    */
    'locale' => 'zh_CN',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    | 对应原系统: APP_STATICS_HOST
    |
    */
    //'statics_host' => 'http://img.demo.cn;http://img1.demo.cn',

);


