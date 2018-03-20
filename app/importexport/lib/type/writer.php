<?php
/**
 * writer.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
 class importexport_type_writer
 {
     public $objPHPExcel;
     public $objActiveSheet;
     
     protected $property = ['Creator'=>'shopex Onex bbc', 'LastModifiedBy'=>'shopex Onex bbc', 'Title'=>'shopex Onex bbc'];
     protected $fileType = [
             'xls'=>'Excel5',
             'csv'=>'CSV',
     ];
     
     public function __construct()
     {
         $this->objPHPExcel = new PHPExcel();
         // 表格默认都只有一个sheet
         $this->objPHPExcel->setActiveSheetIndex(0);
         $this->objActiveSheet = $this->objPHPExcel->getActiveSheet();
     }
     
     /**
      * 把数据写入文件中
      * @param array $data 整理好的数据
      * @example
      * [
      *     0=>[
      *         'oid' => '子订单编号', 
                'tid' => '订单编号', 
                'shop_id' => '所属商家', 
                'settlement_time' => '可结算时间', 
      *     ],
      *     1=>[
      *         'oid' => 1601261432150002, 
                'tid' => 1601261432140002, 
                'shop_id' => 'onexbbc自营店（自营店铺）', 
                'settlement_time' => '2016-01-26 14:37', 
      *     ]
      * ]
      * @return bool
      * */
     public function writeDocument($data)
     {
         if(!$data)
         {
             return false;
         }
         
         // 解析数据
         $this->colHeaders = $data[0];
         $this->colHeaderKeys = array_keys($this->colHeaders);
         unset($data[0]);
         $this->rowsData = $data;
         
         // 文档属性
         $this->writeProperty();
         // 设置单元格样式
         $this->writeCellStyle();
         // 写标题栏
         $this->writeColHeads();
         // 写表格内容
         $this->writeContent();
         
         return true;
     }
     
     /**
      * 保存文件
      * 
      * @param string $filename 文件名
      * @param string $type 文件类型
      * @return string  返回文件路径
      * */
     
     public function saveDocument($file = null, $type='xls')
     {
         $type = strtolower($type);
         if(!array_key_exists($type, $this->fileType))
         {
             return false;
         }
         
         $this->localFile = $file;
         if(!$file)
         {
             // 创建临时文件
             $this->localFile = tempnam(TMP_DIR,'importexport');
         }
         
         $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, $this->fileType[$type]);
         if($type == 'csv')
         {
             $objWriter->setDelimiter(',')
                       ->setEnclosure('"')
                       ->setLineEnding("\r\n")
                       ->setSheetIndex(0);
         }
         
         $objWriter->save($this->localFile);
         
         return $this->localFile;
     }
     
     /**
      * 下载文件
      * 
      * @param string $filename 文件名
      * @param string $type 导出文件类型
      * @param bool $isDelFile 是否删除文件
      * 
      * @return bool
      * */
     public function downDocument($filename, $type, $isDelFile = true)
     {
         $file = $this->localFile;
         if(!file_exists($file))
         {
             return false;
         }
         
         $this->set_queue_header($filename.'.'.$type);
         $handle = fopen($file, 'rb');
         while(!feof($handle)){
             set_time_limit(0);
             print_r(fread($handle, 1024*8));
             ob_flush();
             flush();
         }
         
         fclose($handle);
         if($isDelFile)
         {
             unlink($file);
         }
         
         exit;
     }
     
     // 写表格内容
     protected function writeContent()
     {
         if(!$this->rowsData)
         {
             return false;
         }
         $line = 2;
         foreach ($this->rowsData as $row)
         {
             $rowKeys = array_keys($row);
             $rowValue = array_values($row);
             // 防止数据写入混乱
             if($this->colHeaderKeys === $rowKeys)
             {
                 foreach ($rowValue as $index=>$text)
                 {
                     $coor = $this->getCoordinate($index, $line);
                     $this->objActiveSheet->setCellValueExplicit($coor, $text, PHPExcel_Cell_DataType::TYPE_STRING);
                 }
             }
             
             $line++;
         }
         
         $this->objActiveSheet->setTitle($this->property['Title']);
     }
     
     /**
      * 写文档头部标题栏
      * */
     protected function writeColHeads()
     {
         $headers = $this->colHeaders;
         $vals = array_values($headers);
         foreach ($vals as $k=>$v)
         {
             $coorDinate = $this->getCoordinate($k, 1);
             $this->objActiveSheet->getStyle($coorDinate)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
             $this->objActiveSheet->getStyle($coorDinate)->getFill()->getStartColor()->setARGB('4EEE94');
             $this->objActiveSheet->getStyle($coorDinate)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
             $this->objActiveSheet->setCellValue($coorDinate, $v);
         }
     }
     
     // 设置单元格样式
     protected function writeCellStyle()
     {
         foreach ($this->colHeaderKeys as $k=>$v)
         {
             $coor = $this->getCoordinate($k);
             $this->objActiveSheet->getColumnDimension($coor)->setAutoSize(true);
             
         }
     }
     
     // 写文档属性
     protected function writeProperty()
     {
         $objProps = $this->objPHPExcel->getProperties();
         if($this->property)
         {
             foreach ($this->property as $key=>$val)
             {
                 $method = 'set'.ucfirst($val);
                 if(method_exists($objProps, $method))
                 {
                     $objProps->$method();
                 }
             }
         }
     }
     
     /**
      * 设置文档基本属性
      * @param array $property 文档属性
      * @example ['Creator'=>'Onex bbc', 'LastModifiedBy'=>'Onex bbc']
      * */
     public function setProperty($property)
     {
         $this->property = $property;
     }
     /**
      * 获取单元格坐标
      * @param int $index 数字索引
      * @param int $currentLine 当前行
      * @return string 
      * */
     protected function getCoordinate($index, $currentLine = 0)
     {
         $key = null;
         $colHeads = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
         $start = floor($index/26);
         if($start < 1)
         {
             $key = $colHeads[$index];
         }
         else
         {
             if($start > 26) break;
             $end = $index%26;
             $key = $colHeads[$start-1].$colHeads[$end];
         }
         if($currentLine)
         {
             $key = $key.$currentLine;
         }
         
         return $key;
     }
     
     /**
      *下载文件支持断点续传header
      *
      * @params string $filename 下载文件名称
      */
     public function set_queue_header($filename,$size=null){
         header("Cache-Control: public");
         header("Content-Type: application/force-download");
         header("Accept-Ranges: bytes");
         if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
             $iefilename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
             header("Content-Disposition: attachment; filename=\"$iefilename\"");
         } else {
             header("Content-Disposition: attachment; filename=\"$filename\"");
         }
         header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
         header('Expires:0');
         header('Pragma:public');
          
          
         if( $size !== null ){
             if(isset($_SERVER['HTTP_RANGE'])) {
                 list($a, $range)=explode("=",$_SERVER['HTTP_RANGE']);
                 str_replace($range, "-", $range);
                 $size2=$size-1;
                 $new_length=$size2-$range;
                 header("HTTP/1.1 206 Partial Content");
                 header("Content-Length: $new_length");
                 header("Content-Range: bytes $range$size2/$size");
             } else {
                 $range = 0;
                 $size2=$size-1;
                 $size3=$size;
                 header("Content-Range: bytes 0-$size2/$size");
                 header("Content-Length: ".$size3);
             }
         }
         return $range;
     }
 }
 