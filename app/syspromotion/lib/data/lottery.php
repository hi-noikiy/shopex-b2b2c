<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class syspromotion_data_lottery
{
    private $_instance = null;
    public function __construct($bonusType)
    {
        $this->bonusTypes=array(
            'hongbao' => 'syspromotion_lottery_hongbao',
            'point' => 'syspromotion_lottery_point',
            'custom' => 'syspromotion_lottery_custom',
        );
        $this->obj = $this->bonusTypes[$bonusType];
        $this->set_instance($this->obj);

    }

    public function set_instance(&$obj){
        $this->_instance = new $this->obj;
    }

    public function get_instance(){
        return $this->_instance;
    }

    public function issue($data){
       return  $this->get_instance()->issue($data);
    }
}