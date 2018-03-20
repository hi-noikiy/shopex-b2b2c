<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class site_ctl_admin_utils_linkselect extends desktop_controller {

    public function ajax_get_object()
    {
        $params = input::get();
        $plat     = trim($params['plat']);
        $linkoptions = kernel::single('site_utils_linkcfg')->$plat;// 对应app端页面类型，用于app端判断怎么跳转页面


        if( !$linkoptions[$params['linktype']]['obj'] )
        {
            return '';
        }

        $obj = $linkoptions[$params['linktype']]['obj'];
        $pagedata['filter'] = http_build_query($params['filter']) ? : $obj['filter'] ;
        $pagedata['callback'] = $params['callback'];
        $pagedata['object'] = $params['object'] ? : $obj['object'];
        $pagedata['textcol'] = $params['textcol'] ? : $obj['textcol'];
        $pagedata['emptytext'] = $params['emptytext'] ? : $obj['emptytext'];

        return view::make('site/admin/utils/linkselect/object.html', $pagedata);
    }

    public function ajax_get_url()
    {
        $postData = input::get();
        $plat     = trim($postData['platform']);
        $linktype = trim($postData['linktype']);
        $pk_id    = trim($postData['pk_id']);
        $params   = [];
        switch ($plat) {
            case 'topc':
                $linkcfg = kernel::single('site_utils_linkcfg')->topc;

                if($linkcfg[$linktype]['pk_name'] && $pk_id)
                {
                    $params = [$linkcfg[$linktype]['pk_name']=>$pk_id];
                }
                return url::action($linkcfg[$linktype]['action'], $params);
                break;

            case 'topwap':
                $linkcfg = kernel::single('site_utils_linkcfg')->topwap;

                if($linkcfg[$linktype]['pk_name'] && $pk_id)
                {
                    $params = [$linkcfg[$linktype]['pk_name']=>$pk_id];
                }
                return url::action($linkcfg[$linktype]['action'], $params);
                break;

            default:
                return '';
                break;
        }
    }

    public function ajax_get_linkselect()
    {
        $params = input::get();

        // $pagedata['filter'] = http_build_query($params['filter']);
        $pagedata['name'] = $params['name'];
        $pagedata['value'] = $params['value'];
        $pagedata['linktypename'] = $params['linktypename'];
        $pagedata['linktypevalue'] = $params['linktypevalue'];

        return view::make('site/utils/linkselect/getlinkselect.html', $pagedata);
    }



}
