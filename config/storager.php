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
    | 默认storage处理类
    |--------------------------------------------------------------------------
    |
    | 默认storage处理方式
    | 目前支持 ttprosystem | base_storage_filesystem
    | 对应原系统:  KVSTORE_STORAGE
    |
    */
    //'default' => 'ttprosystem',
    'default' => 'filesystem',
    //'default' => 'qiniu',
    //'default' => 'aliyuncs',

    /*
    |--------------------------------------------------------------------------
    | mongodb配置
    |--------------------------------------------------------------------------
    |
    | hosts 支持多实例. 目前只支持 192.168.0.230:11211 风格的写法
    | "mongodb:///tmp/mongo-27017.sock" 两种风格
    | options MongoClient::__construct 第二个参数 An array of options for the
    | connection
    */
    'ttprosystem' => array(
        'hosts' => array(
            '192.168.51.96:1978',
            //'192.168.0.231:11211',
        ),
    ),

    /*
    |--------------------------------------------------------------------------
    | 七牛存储配置
    |--------------------------------------------------------------------------
     */
    'qiniu' => array(
        'auth' => [
            'accessKey'=>'kejAhJms5tFC7foJqqoML3RKYmeYyHQiUjiykbKK',
            'secretKey'=>'6BGwR95rnSsq5XDK5csknfrNBFX_mns2ff2K4Y_c'
        ],
        'bucket' => 'shopexdemoimage',
    ),

    /*
    |--------------------------------------------------------------------------
    | 阿里云oss存储配置
    |--------------------------------------------------------------------------
     */
    'aliyuncs' => array(
        'accessKeyId'=>'LTPIx65W25kytbzK',//从OSS获得的AccessKeyId
        'AccessKeySecret'=>'SuOiepqudg2p7x3kR43BAKQEsNwmNYx',//您从OSS获得的AccessKeySecret
        'endpoint'=>'oss-cn-shanghai.aliyuncs.com',//您选定的OSS数据中心访问域名，例如oss-cn-hangzhou.aliyuncs.com
        'bucket' => 'shopex',//您使用的Bucket名字，注意命名规范;
    ),

    /*
    |--------------------------------------------------------------------------
    | 静态资源映象站地址(js css)
    |--------------------------------------------------------------------------
    |
    | 资源映像站地址
    |
    */
    'host_mirrors' => array(
        //'http://img0.example.com',
        //'http://img2.example.com',
    ),

    /*
    |--------------------------------------------------------------------------
    | 图片映象站地址
    |--------------------------------------------------------------------------
    |
    | 图片资源映像站地址
    | 一个域名标识对应一个域名，在替换的时候域名的时候替换对应的值就可以了
    | 图片域名标识不可变，添加后慎重删除，除非确保改标识下不存在图片了
    | 如果未配置则不需要配置键值默认使用
    |
    | shopexdemoimage 为键值的时候是关键字(正式环境中请避免使用，用于shopex添加测试图片数据)
    | 默认指向config/link.php中配置shopexdemoimage的链接
    | 主要用于测试数据的图片显示
    */
    'host_mirrors_img' => [
        //'http://img0.example.com',//使用默认后替换该域名
        //'img0'=>'http://img0.example.com',
        //'img1'=>'http://img2.example.com',
        //'aliyuncs'=>'http://shopex.oss-cn-shanghai.aliyuncs.com',
    ],
);
