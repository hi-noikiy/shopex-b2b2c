<?php

class base_cleandata{

    public function clean($type="clean")
    {
        //清除证书
        base_certi::del_certi();

        //清除shopex_id
        base_enterprise::set_enterprise_info(null);

        prism::cleanRedis();
    }

}
