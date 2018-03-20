<?php

/**
 * reader.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class importexport_type_reader {

    protected $allowType = ['xls'=>'Excel5', 'csv'=>'CSV',];
    protected $objReader;
    protected $objPHPExcel;
    protected $objSheet;
    protected $file;
    protected $type;
    
    public function __construct($params)
    {
        $this->file = $params['file'];
        $this->type = $params['type'];
        $this->allowType();
        $this->readyRead();
    }
    // 获取总行数
    public function getRow()
    {
        return $this->objSheet->getHighestRow();
    }
    
    // 获取总列数
    public function getCol()
    {
        return PHPExcel_Cell::columnIndexFromString($this->objSheet->getHighestColumn());
    }
    
    /**
     * 读取文档
     * 
     * @param bool $isDelFile 是否删除文件
     * @return array $result 读取结果
     * */
    public function readDocument($isDelFile = true)
    {
        $objSheet = $this->objSheet;
        // 获取总行数和总列数
        $rowCount = $this->getRow();
        $cols = $this->getCol();
        
        $result = [];
        $row = [];
        for($r=1; $r <= $rowCount; $r++)
        {
            for($c=0; $c<$cols; $c++)
            {
                $row[$c] = $objSheet->getCell(PHPExcel_Cell::stringFromColumnIndex($c).$r)->getValue();
            }
        
            $result[] = $row;
        }
        
        if($isDelFile)
        {
            unlink($this->file);
        }
        
        return $result;
    }
    
    // 判断文件类型
    protected function allowType()
    {
        if(!array_key_exists($this->type, $this->allowType))
        {
            throw new LogicException(app::get('importexport')->_('非法类型'));
        }
        
        return true;
    }
    
    // 准备读取文件
    protected function readyRead()
    {
        if(!file_exists($this->file))
        {
            throw new LogicException(app::get('importexport')->_('文件不存在'));
        }
        
        $this->objReader = PHPExcel_IOFactory::createReader($this->allowType[$this->type]);
        $this->objPHPExcel = $this->objReader->load($this->file);
        // 目前只支持一个sheet
        $this->objSheet = $this->objPHPExcel->getSheet(0);
        
        return true;
    }
}
 