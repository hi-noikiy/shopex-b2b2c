<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class site_view_input{

    function input_checkbox($params){
        $params['type'] = 'checkbox';
        $params['class'] = 'x-check'.($params['class'] ? ' '.$params['class'] : '');
        $params['autocomplete'] = 'off';
        return utils::buildTag($params,'input');
    }
    function input_radio($params){
        $params['type'] = 'radio';
        $params['class'] = 'x-check'.($params['class'] ? ' '.$params['class'] : '');
        $params['autocomplete'] = 'off';
        return utils::buildTag($params,'input');
    }

    function input_datepicker($params){
        if(!$params['type']){
            $params['type'] = 'date';
        }
        if(!$params['vtype']){
            $params['vtype'] = 'date';
        }else if ($params['vtype'] != 'date'){
          $params['vtype'] = $params['vtype'].'&&date';
        }
        if(is_numeric($params['value'])){
            $params['value'] = date('Y-n-j',$params['value']);
        }
        if(isset($params['concat'])){
            $params['name'] .= $params['concat'];
            unset($params['concat']);
        }
        // if(!$params['format'] || $params['format']=='timestamp'){
        //     $prefix = '<input type="hidden" name="_DTYPE_'.strtoupper($params['type']).'[]" value="'.htmlspecialchars($params['name']).'" />';
        // }else{
        //     $prefix = '';
        // }

        $params['type'] = 'text';
        $return = utils::buildTag($params,'input class="x-input calendar'.($params['class'] && $params['class'] != 'cal' ? ' '.$params['class'] : '').'" maxlength="10" readonly="readonly"');
        return $prefix.$return;
    }

    // 用于选择系统pc、wap链接生成组件
    function input_linkselect($params){
        $plat = trim($params['platform']);
        $linkoptions = kernel::single('site_utils_linkcfg')->$plat;

        $pagedata['domid'] = view::ui()->new_dom_id();
        $pagedata['value'] = $params['value'];
        $pagedata['name'] = $params['name'];
        $pagedata['platform'] = $plat;
        $pagedata['linktypename'] = $params['linktypename'];
        $pagedata['linktypevalue'] = $params['linktypevalue'];
        $pagedata['linkoptions'] = $linkoptions;

        return view::make('site/admin/utils/linkselect/linkselect.html', $pagedata)->render();
    }


}//End Class
