<?php

/**
 * pagetmpl.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syspromotion_pagetmpl {

    public function saveData($data)
    {

        $objMdl = app::get('syspromotion')->model('page_tmpl');

        if(!$data['ptmpl_id']){
            $data['created_time'] = time();
            $data['updated_time'] = time();
        }else{
            $data['updated_time'] = time();
        }

        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            $objMdl->save($data);
            $db->commit();
        } catch ( LogicException $e )
        {
            $db->rollback();
            throw $e;
        }
        
        return true;
    }
    
    public function getInfo($ptmplid, $row='*')
    {
        if(!$ptmplid)
        {
            return false;
        }
        
        $objMdl = app::get('syspromotion')->model('page_tmpl');
        return $objMdl->getRow($row, ['ptmpl_id'=>$ptmplid]);
    }

    public function delete($params){
        $objMdlPage = app::get('syspromotion')->model('page');
        $objMdlPagetmpl = app::get('syspromotion')->model('page_tmpl');
        $list = $objMdlPage->getList('page_id,page_name,page_tmpl,used_platform',['used_platform'=>'app','page_tmpl'=>$params]);

        if($list){
            throw new Exception(app::get('syspromotion')->_('模板使用中！'));
        }
        else{
            $return = $objMdlPagetmpl->delete(['ptmpl_id'=>$params]);
        }

        return true;

    }
}
