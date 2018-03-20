<?php

class syspromotion_distribute_plugin_hongbao implements syspromotion_distribute_plugin_interface
{
    public function receive($distributeDetail)
    {
        $apiParams = [];
        $apiParams['user_id'] = $distributeDetail['user_id'];
        $apiParams['hongbao_id'] = $distributeDetail['discount_param']['hongbaoid'];
        $apiParams['money'] = $distributeDetail['discount_param']['hongbaomoney'];
        $apiParams['hongbao_obtain_type'] = $distributeDetail['discount_param']['hongbao_obtain_type'];

        $hongbaoResult = app::get('syspromotion')->rpcCall('user.hongbao.get', $apiParams);

        return $hongbaoResult;
    }
}

