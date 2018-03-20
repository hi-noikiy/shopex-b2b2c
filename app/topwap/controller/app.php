<?php

class topwap_ctl_app extends topwap_controller {

    public function index()
    {
        $data = app::get('sysapp')->getConf('app.download.boot.setting');
        $pagedata = unserialize($data);
        if( $pagedata['open'] == 'false' )
        {
            return redirect::action('topwap_ctl_default@index');
        }

        return $this->page('topwap/app.html', $pagedata);
    }

    public function wxDownloadBoot()
    {
        $data = app::get('sysapp')->getConf('app.download.boot.setting');
        $pagedata = unserialize($data);
        return view::make('topwap/wx_app.html', $pagedata);
    }
}

