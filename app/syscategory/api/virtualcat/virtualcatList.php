<?php
class syscategory_api_virtualcat_virtualcatList
{
    public $apiDescription = "获取类目树形结构";
    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'platform'       => ['type'=>'string', 'valid'=>'required|in:pc,h5,app','description'=>'使用平台'],
        );
        return $return;
    }
    public function getList($params)
    {
        return kernel::single('syscategory_data_virtualcat', $params['platform'])->getTree();
    }


}
