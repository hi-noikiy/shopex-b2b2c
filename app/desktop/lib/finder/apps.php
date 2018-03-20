<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_finder_apps
{

    var $column_tools='操作';
    var $column_tools_width='150';
    function column_tools(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $colList[$k] = $this->_column_tools($row);
        }
        return $colList;
    }

    function _column_tools($row){
        $local_ver = $row['local_ver'];
        $remote_ver = $row['remote_ver'];
        $status = $row['status'];
        $app_id = $row['app_id'];

        $update_install_btn = '<button type="button" class="btn btn-default btn-sm" onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'install\']).run(\''.$app_id.'\')});">'.app::get('desktop')->_('升级并安装').'</button>&nbsp;';
        $download_install_btn = '<button  type="button" class="btn btn-default btn-sm" onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'install\']).run(\''.$app_id.'\')});">'.app::get('desktop')->_('下载并安装').'</button>&nbsp;';
        $install_btn = '<button class="btn btn-default btn-sm" type="button" onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'install\']).run(\''.$app_id.'\')});">'.app::get('desktop')->_('安装').'</button>&nbsp;';
        
        $locked_app_ids = app::get('base')->model('apps')->get_locked_app_ids();
        
        if(in_array($app_id,$locked_app_ids)){
            $pause_btn = '<button type="button" class="btn btn-default btn-sm disabled">'.app::get('desktop')->_('停用').'</button>&nbsp;';
            $active_btn = '<button type="button" class="btn btn-default btn-sm disabled">'.app::get('desktop')->_('启用').'</button>&nbsp;';
            $uninstall_btn = '<button type="button" class="btn btn-default btn-sm disabled">'.app::get('desktop')->_('卸载').'</button>&nbsp;';
        }else{
            $pause_btn = '<button onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'pause\']).run(\''.$app_id.'\')});" class="btn btn-default btn-sm" type="button">'.app::get('desktop')->_('停用').'</button>&nbsp;';
            $active_btn = '<button onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'active\']).run(\''.$app_id.'\')});" class="btn btn-default btn-sm" type="button">'.app::get('desktop')->_('启用').'</button>&nbsp;';
            $uninstall_btn = '<button onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'uninstall\']).run(\''.$app_id.'\')});" class="btn btn-default btn-sm" type="button">'.app::get('desktop')->_('卸载').'</button>&nbsp;';    
        }
        
        $update_btn = '<button type="button" class="btn btn-default btn-sm" onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'download\',\'update\']).run(\''.$app_id.'\')});">'.app::get('desktop')->_('升级').'</button>&nbsp;';

        $output = '';
        switch($status){
            case 'uninstalled':
            if(!$local_ver){
                $output .= $download_install_btn;
            }elseif(version_compare($remote_ver,$local_ver,'>')){
                $output .= $update_install_btn;  
            }else{
                $output .= $install_btn;     
            }
            break;

            case 'installed':
            $output .= $start_btn;
            $output .= $uninstall_btn;
            if(version_compare($remote_ver,$local_ver,'>')){
                $output .= $update_btn;
            }
            break;

            case 'active':
            $output .= $pause_btn;
            $output .= $uninstall_btn;
            if(version_compare($remote_ver,$local_ver,'>')){
                $output .= $update_btn;
            }
            break;

            case 'paused':
            $output .= $active_btn;
            break;
        }
        return $output;
    }

    var $detail_info='info';
    function detail_info($id){
        $pagedata['appinfo'] = app::get($id)->define();

        return view::make('desktop/appmgr/info.html', $pagedata);
    }

}
