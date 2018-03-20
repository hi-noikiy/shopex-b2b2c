<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syscategory_ctl_admin_virtualcatSetting extends desktop_controller {
    public $workground = 'syscategory.workground.category';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    public function index()
    {
        $pagedata['wapenable'] = app::get('syscategory')->getConf('virtualcat.wapenable');
        $pagedata['appenable'] = app::get('syscategory')->getConf('virtualcat.appenable');
        return $this->page('syscategory/admin/vcatsetting.html', $pagedata);
    }

    public function saveSetting()
    {
        $data = input::get();
        $this->begin();
        app::get('syscategory')->setConf('virtualcat.wapenable',$data['wapenable']);
        app::get('syscategory')->setConf('virtualcat.appenable',$data['appenable']);
        $this->adminlog('编辑移动端虚拟分类', 1);
        $this->end(true,app::get('syscategory')->_('保存成功'));
    }

}