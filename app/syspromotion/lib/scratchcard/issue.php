<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
class syspromotion_scratchcard_issue {

    private function getClassNameByType($type)
    {
        $className = 'syspromotion_scratchcard_prize_' . $type;
        return $className;
    }

    //return extension_params 扩展内容，以后还会传回给exchange方法
    public function receive($scratchcard, $prizeInfo, $scratchcard_result)
    {
        $type = $prizeInfo['bonus_type'];
        $className = $this->getClassNameByType($type);

        return kernel::single($className)->receiveScratchcard($scratchcard, $prizeInfo, $scratchcard_result);
    }

    public function exchange($scratchcard_result)
    {
        $type = $scratchcard_result['bonus_type'];
        $className = $this->getClassNameByType($type);
        return kernel::single($className)->exchangeScratchcard($scratchcard_result);
    }



}

