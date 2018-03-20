<?php

class topshopapi_format_json{

    public function __construct()
    {
        // $this->addHeaders('Content-Type', 'application/json;charset=utf-8');
    }


    public function formatData($result)
    {
        if( $result['result'] == array() || $result['result'] === '' )
        {
            $result['result'] = (object)[];
        }

        if( $result['result'] === true )
        {
            unset($result['result']);
            $result['result']['status'] = 'success';
        }

        return response::json($result)->send();
    }

}
