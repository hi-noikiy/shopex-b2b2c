<?php
/**
 * 在装修店铺的时候，目前默认装修的是店铺首页
 *
 * 装修店铺首页的的时候，挂件ID ，挂件排序，挂件配置参数，挂件页面显示参数
 */
class sysdecorate_new_widgets {

    /**
     * 平台 pc wap app
     */
    public $platform = null;

    /**
     * 装修店铺页面名称
     *
     * index 首页
     */
    public $pageName = null;

    public function __construct()
    {
        $this->objMdlWidgetsInstance = app::get('sysdecorate')->model('widgets_instance');
    }

    /**
     * 保存页面配置参数
     */
    public function setConfig($pageName='index', $platform='pc', $params)
    {
        $data = [];

        if( $params['widgets_id'] )
        {
            $data['widgets_id'] = $params['widgets_id'];
        }

        $data['theme']         = $this->getThemeName($platform);
        $data['page_name']     = $pageName;
        $data['order_sort']    = $params['order_sort'];
        $data['shop_id']       = $params['shop_id'];
        $data['widgets_type']  = $params['widgets_type'];
        $data['params']        = serialize($params['params']);
        $data['modified_time'] = time();

        return $this->objMdlWidgetsInstance->save($data);
    }

    /**
     * 获取页面配置参数
     */
    public function getConfig($shopId, $pageName='index', $platform='pc')
    {
        $data = $this->objMdlWidgetsInstance->getList('*', array('shop_id'=>$shopId, 'theme'=>$this->getThemeName($platform), 'page_name'=>$pageName));
        $result = [];
        foreach( $data as $row )
        {
            $row['params'] = unserialize($row['params']);
            $result[$row['order_sort']] = $row;
        }
        ksort($result);

        return $result;
    }

    /**
     * 获取模板名称 function
     *
     * @return void
     */
    protected function getThemeName($platform)
    {
        return 'new_'.$platform;
    }

    public function clean($params)
    {
        return $this->objMdlWidgetsInstance->delete(
            array(
                'shop_id'=>$params['shop_id'],
                'theme'=>$this->getThemeName($params['platform']),
                'page_name'=>$params['page_name'],
            ));
    }

    public function delete($params)
    {
        return $this->objMdlWidgetsInstance->delete(
            array(
                'shop_id'=>$params['shop_id'],
                'theme'=>$this->getThemeName($params['platform']),
                'page_name'=>$params['page_name'],
                'widgets_id|notin' => explode(',',$params['exclude_widgetsIds'])
            ));
    }
}
