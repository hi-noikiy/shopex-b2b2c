<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
class syspromotion_scratchcard_result {
    private $__model = null;

    public function __construct()
    {
        $this->__model = app::get('syspromotion')->model('scratchcard_result');
    }

    public function createResult($result)
    {
        $result['created_time'] = time();
        $result['modified_time'] = time();
        return $this->__model->insert($result);
    }

    public function fireResult($result_id)
    {
        $result['modified_time'] = time();

        return $this->__model->update(['status'=>'exchanged'], ['result_id'=>$result_id]);
    }

    public function getScratchcardResult($resultId)
    {

        return $this->__model->getRow('*', ['result_id'=>$resultId]);
    }

}

