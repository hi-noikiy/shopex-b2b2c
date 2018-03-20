<?php

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * topapi
 *
 * -- image.upload
 * -- 上传图片
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_image_upload implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '上传图片';


    public function setParams()
    {
        return [
            'upload_type'       => ['type'=>'string', 'valid'=>'required|in:binary,base64', 'desc'=>'上传图片类型，支持图片二进制流，base64'],
            'image'             => ['type'=>'binary|string',   'valid'=>'required', 'desc'=>'图片文件内容,不能为空'],
            'image_input_title' => ['type'=>'string', 'valid'=>'required', 'desc'=>'包括后缀名的图片标题,不能为空，如Bule.jpg, 图片上传后取图片文件的默认名'],
            'image_type'        => ['type'=>'string', 'valid'=>'required|in:complaints,aftersales,rate', 'desc'=>'图片类型, complaints 用户投诉商家图片, aftersales售后图片, rate 评价图片'],
        ];
    }

    /**
     * @return stirng image_id 上传图片相对地址
     * @return stirng url 上传图片原图URL地址
     * @return stirng t_url 上传图片微图URL地址
     */
    public function handle($params)
    {
        $fileObject = $this->__getFileObject($params);

        $objLibImage = kernel::single('image_data_image');
        //设置上传图片的账户
        $imageData = $objLibImage->setUploadImageAccount('user', $params['user_id'])->store($fileObject, 'user', $params['image_type']);
        $objLibImage->rebuild($imageData['ident'], $params['image_type']);

        $imageSrc['image_id'] = $imageData['url'];
        $imageSrc['url'] = base_storager::modifier($imageData['url']);
        $imageSrc['t_url'] = base_storager::modifier($imageData['url'],'T');

        return $imageSrc;
    }

    private function __getFileObject($params)
    {
        if( $params['upload_type'] == 'binary' )
        {
            $image = $params['image'];
        }
        else
        {
            if( preg_match('/^(data:\s*image\/(\w+);base64,)/', $params['image'], $result) )
            {
                $image = base64_decode(str_replace($result[1], '', $params['image']));
            }
            else
            {
                throw new \LogicException('上传失败');
            }
        }

        $tmpTarget = tempnam(TMP_DIR, 'image');

        file_put_contents($tmpTarget, $image);

        $imageParams = getimagesize($tmpTarget);
        $size = filesize($tmpTarget);

        $fileObject = new UploadedFile($tmpTarget, $params['image_input_title'], $imageParams['mime'], $size, 0, true);

        return $fileObject;
    }
}

