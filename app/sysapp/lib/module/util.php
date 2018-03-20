<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysapp_module_util {
    //转化链接的配置信息。
    public $linkmapapp ;
    public function __construct(){
        $this->linkmapapp = kernel::single('sysapp_module_config')->linkmapapp;
    }

    public function processAppMapParams($linktype, $idorlink)
    {
        if($linktype!='h5'){
            if(!$linktype){
                throw new RuntimeException('页面类型没填！');
            }
            if($this->linkmapapp[$linktype]['paramkey'] && !$idorlink){
                throw new RuntimeException('id值未填！');
            }
            $params = [
                'webview' =>  $this->linkmapapp[$linktype]['apppage'],
                'webparam' => $this->linkmapapp[$linktype]['paramkey'] ? [ $this->linkmapapp[$linktype]['paramkey']=>$idorlink ] : '',
            ];
        }else{
            if(!$idorlink){
                throw new RuntimeException('h5的链接未填！');
            }
            $params = [
                'webview' => $idorlink,
                'webparam' => (object)[],
            ];
        }
        return $params;
    }

    // 分类商品导航挂件
    public function category_nav($params)
    {
        $newParams['pic'] =[];
        foreach($params['pic'] as $v)
        {
            if( !$v['categoryname'] && !$v['linkinfo'] && !$v['link'] && !$v['linktarget'] ) continue;
            $appmap = $this->processAppMapParams('catlist', $v['cat_id']);//固定是三级分类对应商品列表
            $newParams['pic'][] = [
                'categoryname' => $v['categoryname'],
                'linkinfo' => $v['linkinfo'],
                'cat_id' => $v['cat_id'],
                'image' => $v['image'],
                'imagesrc' => base_storager::modifier($v['image'], 't'),
                'webview' => $appmap['webview'],
                'webparam' => $appmap['webparam'],
            ];
        }

        return $newParams;
    }

    // 楼层挂件
    public function floor($params)
    {
        $appmap = $this->processAppMapParams($params['linktype'], $params['morelink']);
        $params['webview'] = $appmap['webview'];
        $params['webparam'] = $appmap['webparam'];
        if($params['styletag']=='one')
        {
            unset($params['pic']['2']);
            unset($params['pic']['3']);
        }
        $pics = $params['pic'];
        unset($params['pic']);
        $params['pic'] =[];
        foreach($pics as $v)
        {
            if( !$v['linktype'] && !$v['link'] ) continue;
            $appmappic = $this->processAppMapParams($v['linktype'], $v['link']);
            $params['pic'][] = [
                'linktype' => $v['linktype'],
                'image' => $v['image'],
                'link' => $v['link'],
                'imagesrc' => base_storager::modifier($v['image']),
                'webview' =>  $appmappic['webview'],
                'webparam' => $appmappic['webparam'],
            ];
        }

        return $params;
    }

    // 快捷导航挂件
    public function icons_nav($params)
    {
        $newParams['pic'] =[];
        foreach($params['pic'] as $k=>$v)
        {
            if( !$v['linktype'] && !$v['image'] ) continue;
            if( $v['image'] ){
                if( !$v['linktype'] ){
                    throw new RuntimeException('位置'.($k+1).'页面类型没选！');
                }
            }
            if( $v['linktype'] ){
                if( !$v['image'] ){
                    throw new RuntimeException('位置'.($k+1).'图片没选！');
                }
            }

            $appmap = $this->processAppMapParams($v['linktype'], $v['linktarget']);
            $newParams['pic'][] = [
                'tag' => $v['tag'],
                'linktype' => $v['linktype'],
                'linktarget' => $v['linktarget'],
                'image' => $v['image'],
                'imagesrc' => base_storager::modifier($v['image'], 't'),
                'webview' => $appmap['webview'],
                'webparam' => $appmap['webparam'],
            ];
        }

        return $newParams;
    }

    // 轮播挂件
    public function slider($params)
    {
        $newParams['pic'] =[];
        foreach($params['pic'] as $v)
        {
            if( !$v['link'] && !$v['linktarget'] && !$v['linkinfo'] ) continue;
            $appmap = $this->processAppMapParams($v['linktype'], $v['linktarget']);
            $newParams['pic'][] = [
                'link' => $v['link'],
                'linktarget' => $v['linktarget'],
                'linkinfo' => $v['linkinfo'],
                'linktype' => $v['linktype'],
                'imagesrc' => base_storager::modifier($v['link']),
                'webview' => $appmap['webview'],
                'webparam' => $appmap['webparam'],
            ];
        }

        return $newParams;
    }

    // 单图挂件
    public function single_pic($params)
    {
        $appmap = $this->processAppMapParams($params['linktype'], $params['link']);
        $newParams = [
            'image' => $params['image'],
            'linktype' => $params['linktype'],
            'link' => $params['link'],
            'imagesrc' => base_storager::modifier($params['image']),
            'webview' => $appmap['webview'],
            'webparam' => $appmap['webparam'],
        ];

        return $newParams;
    }

    // 双图挂件
    public function double_pics($params)
    {
        $newParams['pic'] =[];
        foreach($params['pic'] as $v)
        {
            if( !$v['linktype'] && !$v['linktarget'] ) continue;
            $appmap = $this->processAppMapParams($v['linktype'], $v['linktarget']);
            $newParams['pic'][] = [
                'link' => $v['link'],
                'linktype' => $v['linktype'],
                'linktarget' => $v['linktarget'],
                'linkinfo' => $v['linkinfo'],
                'imagesrc' => base_storager::modifier($v['link']),
                'webview' =>  $appmap['webview'],
                'webparam' => $appmap['webparam'],
            ];
        }

        return $newParams;
    }



}


