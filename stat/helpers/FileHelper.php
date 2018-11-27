<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\stat\helpers;


class FileHelper 
{
   /** @var string Имя файла */    
    protected $fileName;
    /**@var boolean флаг инициализации */
    protected $isFile=false;


    public function __construct($fileName)
    {
        $this->fileName = $fileName;
        if (is_file($fileName)) {
            $this->isFile = true;
        }
        
    }
    public function isFileExist()
    {
       return $this->isFile; 
    }
    public function putOrReplaceData($data)
    {
        if (!$res = fopen ($this->fileName,'w')) {
            return $res;
        }
        $this->isFile = true;
        $result = fwrite($res, $data);
        fclose($res);
        return $result ? true : $result;        
    }
    public function getFileData() 
    {
        if (!$res = fopen ($this->fileName,'r')) {
            return $res;
        }
        $result = fread($res,filesize($this->fileName));
        fclose($res);
        return $result;
        
    }
    public function unserializeFileData() {
        $res = $this->getFileData();
        if ($res) {
            return unserialize($res);
        } else {
            return false;
        }
    }
    public function deleteFile() {
        if ($this->isFile) {
            unlink($this->fileName);
            $this->isFile = false;
        }
    }
}