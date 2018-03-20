<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysapp_ctl_admin_widgets extends desktop_controller {

    /*
     * workground
     * @var string
     */
    var $workground = 'site.wrokground.theme';

    public function __construct(&$app)
    {
        $this->tmpls = kernel::single('sysapp_module_config')->tmpls;// 页面类型
        $this->widgets = kernel::single('sysapp_module_config')->widgets;// 挂件类型
        $this->linkmapapp = kernel::single('sysapp_module_config')->linkmapapp;// 对应app端页面类型，用于app端判断怎么跳转页面
        parent::__construct($app);
    }
    public function edit_widgets($widgetsId)
    {
        $objMdlWidgetsInstance = app::get('sysapp')->model('widgets_instance');
        $winfo = $objMdlWidgetsInstance->getRow('*', array('widgets_id'=>$widgetsId));
        $pagedata['setting'] = $winfo['params'] ;
        $pagedata['_PAGE_'] = 'sysapp/widgets/'.$winfo['widget'].'/_config.html';
        $pagedata['widgets_id'] = $widgetsId;
        $pagedata['widget'] = $winfo['widget'];

        return view::make('sysapp/main_widgets.html', $pagedata);
    }

    public function save_widgets()
    {
        $postdata = input::get();
        $widgetsId = input::get('widgets_id');
        $widgetFunc = input::get('widget');
        unset($postdata['app']);
        unset($postdata['ctl']);
        unset($postdata['act']);
        unset($postdata['widgets_id']);
        unset($postdata['widget']);
        $this->begin("?app=sysapp&ctl=admin_tmpl&act=index");
        try
        {
            //挂件配置保存
            $objMdlWidgetsInstance = app::get('sysapp')->model('widgets_instance');
            if(method_exists($this, $widgetFunc))
            {
                $postdata = call_user_func([$this, $widgetFunc], $postdata);
            }
            $flag = $objMdlWidgetsInstance->update( ['params'=>$postdata], ['widgets_id'=>$widgetsId] );
            if(!$flag)
            {
                throw new \LogicException(app::get('sysapp')->_('配置挂件失败'));
            }
        }
        catch(\Exception $e)
        {
            $msg = $e->getMessage();
            $this->end(false, $msg);
        }

        $this->adminlog("配置挂件[widgets_id:{$widgetsId}]", 1);
        $this->end('true');
    }

    public function output($pagedata)
    {
        if( $pagedata['_PAGE_'] ){
            $pagedata['_PAGE_'] = 'topc/member/'.$pagedata['_PAGE_'];
        }else{
            $pagedata['_PAGE_'] = 'topc/member/'.$this->action_view;
        }
        return $this->page('topc/member/main.html', $pagedata);
    }


/************************************  挂件配置参数  start  ************************************************************/

    // 组装app端对应的参数，webview是app端的页面,webparam是app端页面需要的参数
    private function processAppMapParams($linktype, $idorlink)
    {
        if($linktype!='h5'){
            if(!$linktype){
                return $this->splash('error', null, '页面类型没填！');
            }
            if($this->linkmapapp[$linktype]['paramkey'] && !$idorlink){
                return $this->splash('error', null, 'id值没填！');
            }
            $params = [
                'webview' =>  $this->linkmapapp[$linktype]['apppage'],
                'webparam' => $this->linkmapapp[$linktype]['paramkey'] ? [ $this->linkmapapp[$linktype]['paramkey']=>$idorlink ] : '',
            ];
        }else{
            if(!$idorlink){
                return $this->splash('error', null, 'h5的链接未填！');
            }
            $params = [
                'webview' => $idorlink,
                'webparam' => (object)[],
            ];
        }
        return $params;
    }

    // 分类商品导航挂件
    protected function category_nav($params)
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
    protected function floor($params)
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
    protected function icons_nav($params)
    {
        $newParams['pic'] =[];
        foreach($params['pic'] as $k=>$v)
        {
            if( !$v['linktype'] && !$v['image'] ) continue;
            if( $v['image'] ){
                if( !$v['linktype'] ){
                    return $this->splash('error', null, '位置'.($k+1).'页面类型没选！');
                }
            }
            if( $v['linktype'] ){
                if( !$v['image'] ){
                    return $this->splash('error', null, '位置'.($k+1).'图片没选！');
                }
            }
            // if(  $v['linktype'] && $this->linkmapapp[$linktype]['paramkey'] && !$v['linktarget']){
            //     return $this->splash('error', null, '位置'.$k.' ID没选！');
            // }

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
    protected function slider($params)
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
    protected function single_pic($params)
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
    protected function double_pics($params)
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

}//End Class
