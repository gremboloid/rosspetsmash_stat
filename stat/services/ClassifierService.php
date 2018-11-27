<?php

namespace app\stat\services;

use app\stat\Tools;
use app\stat\model\Brand;
use app\stat\helpers\FileHelper;
use app\stat\db\QuerySelectBuilder;
/**
 * Description of ClassifierService
 *
 * @author kotov
 */
class ClassifierService {
    
    protected static $_CACHE = 'classifier.dat';
    /**
     * Идентификатор производителя
     * @var int 
     */
    protected $contractorId;

    /**
     * Id производителя
     * @param type $id
     */
    public function __construct(int $id) {
        $this->contractorId = $id;
    }

    /**
     * 
     * @param int $rootId
     * @param bool $fullClassifier
     */
    public function getClassifierHierarchy(int $rootId = null,bool $fullClassifier = false) 
    {
        if (!( is_admin() || 
                is_analytic() || 
                is_rosspetsmash() ||
                $fullClassifier )) {
            $brand = Brand::getFieldByValue('ContractorId', $this->contractorId);
            $sql = 'SELECT DISTINCT "m"."ClassifierId" FROM TBLMODEL "m" WHERE "m"."BrandId" = '. $brand;//.$brand;
            $classifierList = Tools::getValuesFromArray(getDb()->querySelect($sql), 'ClassifierId');
            $sql = 'SELECT DISTINCT c."Id",Null AS "PId", c."Name"
                    FROM BIX.TBLCLASSIFIER c WHERE c."ClassifierId" = 41
                    START WITH c."Id" IN ('. implode(',', $classifierList).') CONNECT BY PRIOR c."ClassifierId" = c."Id"';
            $classifierSections = getDb()->querySelect($sql,null,true);
            $res = [];
            foreach ($classifierSections as $section) {
                $res = array_merge($res,Tools::mapTree($this->getCurrentClassifierHierarchy($section['Id'])));
            } 
            return $res;
        }
        return Tools::mapTree($this->getCurrentClassifierHierarchy($rootId));
    }
    
    /**
     * Вернуть дерево классификатора в виде JSON строки
     * @param int $rootId корневой раздел
     * @param bool $full выгрузка всего классификатора
     * @return string строка в формате JSON
     */
    public function getClassifierListJSON($rootId = null,$full = false) 
    {
        // $classifierHierarchy = Tools::mapTree($this->getClassifierHierarchy());
        $classifierHierarchy = $this->getClassifierHierarchy($rootId,$full);
         if (is_admin()) {
             $fileHelper = new FileHelper(_CACHE_DIR_.self::$_CACHE);
             if (!$fileHelper->isFileExist()) {
                 $string = Tools::getJSONtree($classifierHierarchy);
                 $fileHelper->putOrReplaceData($string);
             }
             else {
                 return  json_encode($fileHelper->getFileData());
            }
            return json_encode($string);             
        }
        return json_encode(Tools::getJSONtree($classifierHierarchy));         
    }
    
    protected function getCurrentClassifierHierarchy($rootId = null) {
        if (!isset($rootId)) {
        $filter = 'c."ClassifierId" IS NULL ';
        }
        else {
            $filter = 'c."Id" = '.intval($rootId);
        }
        $sql = 'SELECT LEVEL AS "Level",
                c."Id",c."ClassifierId" AS "PId", c."Name"
                FROM BIX.TBLCLASSIFIER c
                START WITH '.$filter.' CONNECT BY PRIOR c."Id" = c."ClassifierId" ORDER SIBLINGS BY c."OrderIndex" DESC NULLS LAST,c."Name"';
        $bindingParams = null;
        $aResult = getDb()->querySelect($sql,$bindingParams,true);
        // установка параметров для корневого элемента
        $aResult[key($aResult)]['PId'] = null;
        return $aResult;
    }
    /**
     * Вернуть доступные источники данных
     * @return attsy
     */
    public static function getFullReportClassifier()
    {
        $sql= 'SELECT "Id", "Name"  FROM
    "TBLCLASSIFIER" WHERE "Id" IN (42,43)';
        return getDb()->querySelect($sql);
    }
    /**
     * 
     * @param int $id
     * @return boolean
     */
    public static function getClassifierPath(int $id = 41)
    {
        $sql = 'SELECT SYS_CONNECT_BY_PATH(T."Id", \'/\') AS "root", LEVEL AS "Level"
                       FROM "TBLCLASSIFIER" T 
                       WHERE T."Id" = '.$id .
                       'START WITH "Id" IN (SELECT "Id" FROM "TBLCLASSIFIER" WHERE "ClassifierId" IS NULL)
                       CONNECT BY PRIOR "Id" = "ClassifierId"';
        $result = getDb()->querySelect($sql);
        if (count($result) == 1) {
            return $result[0];
        }
        else {                    
            return false;
        }
    }
    /**
     * Вернуть цепочку родительских уровней классификатора до указанного элемента
     * @param int $elementId 
     * @return array|null
     */
    public static function getClassifierParents($elementId)
    {
        if (!isset($elementId)) {
            return false;       
        }        
        $filter = 'c."Id" = '.intval($elementId);

            $sql = 'SELECT LEVEL AS "Level",
                       c."Id",c."ClassifierId" AS "PId", c."Name"
                    FROM BIX.TBLCLASSIFIER c
                    START WITH '.$filter.' CONNECT BY PRIOR c."ClassifierId" = c."Id"';
            $bindingParams = null;
        
        $aResult = getDb()->querySelect($sql,$bindingParams);
        // установка параметров для корневого элемента
     //   $aResult[key($aResult)]['PId'] = null;
        return $aResult;
    }
        public function getClassifierCsv() {
        ob_end_clean();
        $max_par = 0; // максимальная глубина вложенности
       
         // Вспомогательная функция возвращающая массив элементов заданной глубины вложенности
       
        function ret_deep($asArray,$d)
        {
            $retArray = array();
          foreach($asArray as $n => $ar)
         {
              
              if ($d == $ar[2]) 
                {
                    $retArray[$n] = $asArray[$n];
                }
            }      
              return $retArray;
          
          
        }


        // Вспомогательная функция, находящая родительский идентификатор
         function retParentId($classifier,$id,$rows)
        {
             if ($id) {
                for ($i = 0; $i < $rows; $i++) {
                    if ($id == $classifier[$i]["Id"]) {
                        return $classifier[$i]["ClassifierId"];
                    }
                }
            } else 
                {
                return false;                    
                }
        }
        $classifier = getDb()->getRows(new QuerySelectBuilder([
            'select' => [['Id'],['Name'],['ClassifierId']],
            'from' => 'TBLCLASSIFIER' ]) );

                // $deep=0; 

         $rows_count = count($classifier);
         for ($i=0;$i<$rows_count;$i++)
         {
          $parId = $classifier[$i]["ClassifierId"];
          $par = 0;
          if ($parId)
             // $parId = retParentId($classifier,$id,$this->nrows);
          do 
          {
              $par++;
              $parId = retParentId($classifier,$parId,$rows_count);
              if ($par> 10) break; // для исключения вечного цикла
          }
          while ($parId);

          $asArray[$classifier[$i]["Id"]] = array($classifier[$i]["Name"],$classifier[$i]["ClassifierId"],$par);
          if ($par > $max_par) $max_par = $par;
         }
         $count = 0;
         $idx = 0;
         $outputArray = array();
         $resArray = array();
         for ($deep=0;$deep<=$max_par;$deep++)
         {
         $outputArray[$deep] = ret_deep($asArray,$deep);    
         }
         $deep= 0;
         $cur_memo = array ();

                 while(true)
         {
        if (!empty($outputArray[$count])) {
            list($key, $val) = each($outputArray[$count]);
        } else {
            $key = null;
            $val = null;
        }
         if (!$key)
         {
             if ($count<1)
             break;
             if (!empty($outputArray[$count])) {
                reset($outputArray[$count]);
             }
             $count--;
             if (!empty($outputArray[$count-1])) {
                $cur_memo[$count] = key($outputArray[$count-1]);
             }
             continue;
         }
             if ($count == 0)
             {
                 if (!Tools::hasNext($outputArray[$count]))
                 {
                 $cur_memo[$count] = $val['Id'] = $key;
                 $resArray[$idx] = $val;
                 $idx++;
                 }
                 $count++;
                 continue;
             }
             if ($val[1] == $cur_memo[$count-1])
             {
                 $cur_memo[$count] = $val['Id'] = $key;
                 $resArray[$idx] = $val;
                 $idx++;
                 $count++;
             }

         }
         $indexer = array();
         for ($i=0;$i<$max_par;$i++)
         {
             $indexer[$i]=0;
         }
         $prev_deep = 0;

         for ($i=0;$i<$rows_count;$i++)
         {

             $deep = $resArray[$i][2];
                 if ($prev_deep > $deep)
                 { 
                     $n1 = $deep+1;
                     while ($n1!=$max_par)
                     {
                     $indexer[$n1++] = 0;

                     }
                 }

            $Name = iconv("UTF-8","windows-1251", $resArray[$i][0]);

            for ($n1=0;$n1<=$deep;$n1++)
            {
                if ($n1 == $deep) 
                {
                    $indexer[$deep]++;
                }
                if ($n1!=0)
                {
                     $str_out .= $indexer[$n1] . '.';
                }
            }

             $content .= $str_out .';' . $Name;
             $content .= "\n";
             $str_out = '';
             $prev_deep = $deep; 

         }

         header('Content-Type: text/html; charset=windows-1251');
         header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
         header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
         header('Cache-Control: no-store, no-cache, must-revalidate');
         header('Cache-Control: post-check=0, pre-check=0', FALSE);
         header('Pragma: no-cache');
         header('Content-transfer-encoding: binary');
         header('Content-Disposition: attachment; filename=classifier.csv');
         header('Content-Type: application/x-unknown');
         echo $content; 
        
    }
    
    
    
    
    
}