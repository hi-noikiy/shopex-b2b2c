<?php

class sysopen_shopex_server
{
    public function process()
    {
        return json_encode([
            'res'=>'fail',
            'msg'=>app::get('sysopen')->_('尚未开放'),
        ]);
    }
}
