<?php

class syspromotion_distribute_plugin_instance
{

    private $__classMap = [
        'hongbao' => 'syspromotion_distribute_plugin_hongbao',
        'voucher' => 'syspromotion_distribute_plugin_voucher',
    ];

    public function getAdapter($type)
    {
        return kernel::single($this->__classMap[$type]);
    }

}

