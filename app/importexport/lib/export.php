<?php
/**
 * 直接导出，不通过队列
 */
class importexport_export {

    public function fileDownload($filetype, $model, $filename, $filter,$orderBy=null)
    {
           $objWriter = kernel::single('importexport_type_writer');
           //实例化导出数据类
           $dataObj = kernel::single('importexport_data_object',$model);
           $dataObj->set_orderBy($orderBy);
           //设置导出字段
           $object =  kernel::service('importexport.'.$model);
           if( is_object($object) ){
               if($fields = $object->shop_export_fields)
               {
                   $dataObj->set_title($fields);
               }
           }
           $data = [];
           $outdata = [];
           //导出数据写到本地文件
           $offset = 0;
           while( $listFlag = $dataObj->fgetlist($data,$filter,$offset) )
           {
               $outdata = array_merge($outdata, $data);
               $offset++;
           }

           // 保存数据
           $objWriter->writeDocument($outdata);
           $objWriter->saveDocument(null, $filetype);
           // 下载
           return $objWriter->downDocument($filename, $filetype);
    }
}
