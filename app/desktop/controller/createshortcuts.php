<?php
class desktop_ctl_createshortcuts extends base_routing_controller
{
    function index()
    {
        $furl = kernel::base_url(1).kernel::url_prefix().'/shopadmin';
$content = "[InternetShortcut]
URL=".$furl."
IDList=[{000214A0-0000-0000-C000-000000000046}]
Prop3=19,2";

        /** ���ݸ�������� **/
        $filename = app::get('desktop')->getConf('background_title');
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);

        if (preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT']) ) 
        {
            header('Content-Disposition:  attachment; filename=' . $encoded_filename . '.url;');
        }
        elseif (preg_match("/Firefox/", $_SERVER['HTTP_USER_AGENT']))
        {
            header('Content-Disposition: attachment; filename*=utf8' .  $filename . '.url;');
        }
        else 
        {
            header('Content-Disposition: attachment; filename=' .  $filename . '.url;');
        }
        /** end **/

        echo $content;
    }
}
