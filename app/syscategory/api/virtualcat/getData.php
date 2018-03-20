<?php
class syscategory_api_virtualcat_getData{

    public $apiDescription = "获取指定3级类目的信息以及该父级的所有结构";
    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'virtual_cat_id' => ['type'=>'string','valid'=>'required', 'description'=>'类目id',],
            'platform'       => ['type'=>'string', 'valid'=>'required|in:pc,h5,app','description'=>'使用平台'],
            'fields'         => ['type'=>'field_list','valid'=>'', 'description'=>'获取类目的指定字段'],
        );
        return $return;
    }
    public function getList($params)
    {
        $catId = explode(',', $params['virtual_cat_id']);

        $row = "virtual_cat_id,virtual_cat_name,virtual_cat_logo";
        if($params['fields'])
        {
            $row = $params['fields'];
        }
        $row = str_append($row,'level,cat_path,virtual_parent_id');

        $objCatMdl = app::get('syscategory')->model('virtualcat');
        $data = array();
        $cats = $objCatMdl->getList($row,['virtual_cat_id'=>$catId,'platform'=>$params['platform']]);

        $result = [];
        foreach ($cats as $key => $cat) {
            if($cat['level'] == 1)
                    {
                        $data['lv1'] = $cat;
                    }
                    elseif($cat['level'] == 2)
                    {

                        $catLv1 = $objCatMdl->getRow($row,['virtual_cat_id'=>$cat['virtual_parent_id'],'platform'=>$params['platform']]);
                        $data['lv1'] = $catLv2;
                        $data['lv2'] = $cat;
                    }
                    elseif($cat['level'] == 3)
                    {

                        $catIds = array_filter(explode(',',$cat['cat_path']));
                        $list = $objCatMdl->getList($row,['virtual_cat_id'=>$catIds,'platform'=>$params['platform']]);
                        foreach($list as $key=>$value)
                        {
                            if($value['level'] == 1)
                            {
                                $data['lv1'] = $value;
                            }
                            if($value['level'] == 2)
                            {
                                $data['lv2'] = $value;
                            }
                        }
                        $data['lv3'] = $cat;
                    }

                    $result[] = $data;
        }
        if(count($catId) == 1 && $result)
        {

            return $result[0];
        }
        return $result;
    }
}
