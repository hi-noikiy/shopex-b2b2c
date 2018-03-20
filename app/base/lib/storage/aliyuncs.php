<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

use OSS\Core\OssException;
use OSS\OssClient;

class base_storage_aliyuncs implements base_interface_storager {

    public function __construct()
    {
        $accessKeyId = config::get('storager.aliyuncs.accessKeyId');
        $accessKeySecret = config::get('storager.aliyuncs.AccessKeySecret');
        //<您选定的OSS数据中心访问域名，例如oss-cn-hangzhou.aliyuncs.com>;
        $endpoint = config::get('storager.aliyuncs.endpoint');
        try
        {
            $this->ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        }
        catch (OssException $e)
        {
            throw $e;
        }

        //您使用的Bucket名字，注意命名规范;
        $this->bucket = config::get('storager.aliyuncs.bucket');
    }

    public function save( $fileObject )
    {
        $object = md5(uniqid('', true).$file. microtime()).'.'.$fileObject->getClientOriginalExtension();

        try
        {
            $result = $this->ossClient->uploadFile($this->bucket, $object, $fileObject->getPathname());
        }
        catch(OssException $e)
        {
            logger::error('uploadFile Error:'.$e->getMessage());
            throw new \Exception('上传失败');
        }

        $data['ident'] = $object;
        $data['url'] = '/'.$object;

        return $data;
    }

    public function getSizeImageUrl($imageUrl, $size)
    {
        return $imageUrl.'?x-oss-process=style/'.strtolower($size);
    }

    /**
     * 根据原有的图片生成指定大小的图片
     *
     * @param $fileObject
     * @param $ident 存储的唯一值
     */
    public function rebuild($fileObject, $ident)
    {
        return true;
    }


    public function replace($id, $fileObject )
    {
        return false;
    }


    //是否需要删除对应的规格文件
    public function isRemoveSizeFile()
    {
        return false;
    }

    public function remove($key)
    {
        $bucketManager = new BucketManager($this->auth);
        $err = $bucketManager->delete($this->bucket, $key);

        if( $err )
        {
            throw new Exception('删除失败');
        }

        return true;
    }

    public function getFile($id)
    {
    }

}
