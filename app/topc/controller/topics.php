<?php
class topc_ctl_topics extends topc_controller{

    public function __construct($app)
    {
        parent::__construct();
        $this->setLayoutFlag('topics');
    }

    function index()
    {
        $filter = input::get();
        if($filter['cat_id']){
            $data = app::get('topc')->rpcCall('category.cat.get.info',array('cat_id'=>$filter['cat_id'],'platform'=>'pc','fields'=>'cat_name,cat_template'));
            $this->setLayout($data[$filter['cat_id']]['cat_template']);
        }elseif ($filter['virtual_cat_id']) {
            $data = app::get('topc')->rpcCall('category.virtualcat.getData',array('virtual_cat_id'=>$filter['virtual_cat_id'],'platform'=>'pc','fields'=>'virtual_cat_name,virtual_cat_template'));
            if($data['lv1']){
                $this->setLayout($data['lv1']['virtual_cat_template']);
            }else{
                $this->setLayout($data['lv2']['virtual_cat_template']);
            }            
        }

        return $this->page('topc/topics.html');
    }
}
