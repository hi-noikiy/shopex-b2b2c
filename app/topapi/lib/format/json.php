<?php

class topapi_format_json{

    public function __construct()
    {
        // $this->addHeaders('Content-Type', 'application/json;charset=utf-8');
    }


    public function formatData($result)
    {
        if( $result['data'] == array() || $result['data'] === '' )
        {
            $result['data'] = (object)[];
        }

        if( $result['data'] === true )
        {
            unset($result['data']);
            $result['data']['status'] = 'success';
        }

        return response::json($result)->send();
    }

}
