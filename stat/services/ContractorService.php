<?php

namespace app\stat\services;

use app\stat\model\Contractor;
use app\stat\model\ContractorEmail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Description of ContractorrService
 *
 * @author kotov
 */
class ContractorService {
    /**
     * Получить актуальный список производителей
     * @param array $fields список возвращаемых полей
     */
    public static function getActualContractors($fields = array())
    {        
        $filterContractors = array(['param' =>'Id','operation' => 'NOT IN','staticNumber' => '(467,435)' ]);
        $orderBy = array (['name' => 'Name']);
        //return self::getRowsArray($fields, null, null,$filterContractors, $orderBy);
        return Contractor::getRowsArray($fields,$filterContractors,$orderBy);        
    }
     /**
     * Возвращает список производителей
     * @param type $datasources
     * @param type $classifier
     * @param type $typedata
     * @param type $filter
     * @param type $presents
     * @return type
     */
    public static function getContractorList($datasources,$classifier,$typeData,$filter='',$present = true,$limit = '')
    {
        $pFilter = ''; // фильтр присутствующих на портале производителей
        if ($present) {
            $pFilter = ' AND "TBLCONTRACTOR"."Present" = 1 ';
        }
        $classifierFilter = '';
        if (is_array($classifier)) {
            foreach ($classifier as $id) {
                $classifierFilter .= 'SELECT "Id" FROM "TBLCLASSIFIER" CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "Id"='.$id;
                $classifierFilter .= ' UNION ';
            }
            $classifierFilter = trim($classifierFilter,' UNION ');
        } else {
            $classifierFilter = 'SELECT "Id" FROM "TBLCLASSIFIER" CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "Id"='.$classifier;
        }
        $sql = 'SELECT DISTINCT "TBLCONTRACTOR"."Id" AS "Id", "TBLCONTRACTOR"."Name" AS "Name" 
                FROM ( SELECT "ContractorId" FROM "TBLPRODUCTION" WHERE "TypeData" 
                IN '.$datasources.') "contr_prod", "TBLCONTRACTOR", "CACHECONTRACTORCLASSIFIER"                     
                WHERE "contr_prod"."ContractorId" = "TBLCONTRACTOR"."Id" AND
                "TBLCONTRACTOR"."TypeId" = '.$typeData.' AND 
                "CACHECONTRACTORCLASSIFIER"."ContractorId" = "contr_prod"."ContractorId" AND
                "CACHECONTRACTORCLASSIFIER"."ClassifierId" IN ('.$classifierFilter.')'. $pFilter.' ORDER BY "TBLCONTRACTOR"."Name" ASC';
        if ($filter) {
            $sql = 'SELECT * FROM ('.$sql.') WHERE "Id" NOT IN ('.$filter.')';
        }
        if ($limit) {
            $sql = 'SELECT * FROM ('.$sql.') WHERE "Id" IN ('.$limit.')';
        }
        return getDb()->querySelect($sql);
    }
    /**
     * Возвращает список производителей для экономических отчетов
     * @param type $classifierid
     * @param type $filter
     * @return type
     */
    public static function getContractorsListForEconomic($classifier,$filter,$present = true,$limit = '') {
        $pFilter = ''; // фильтр присутствующих на портале производителей
        if ($present) {
            $pFilter = ' AND "TBLCONTRACTOR"."Present" = 1 ';
        }
        $classifierFilter = '';
        if (is_array($classifier)) {
            foreach ($classifier as $id) {
                $classifierFilter .= 'SELECT "Id" FROM "TBLCLASSIFIER" CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "Id"='.$id;
                $classifierFilter .= ' UNION ';
            }
            $classifierFilter = trim($classifierFilter,' UNION ');
        } else {
            $classifierFilter = 'SELECT "Id" FROM "TBLCLASSIFIER" CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "Id"='.$classifier;
        }
        $sql = 'SELECT DISTINCT "TBLCONTRACTOR"."Id" AS "Id", "TBLCONTRACTOR"."Name" AS "Name" FROM 
                (SELECT "ContractorId" FROM "TBLECONOMIC" WHERE "TypeData" IN (12)) "contr_prod", "TBLCONTRACTOR", "CACHECONTRACTORCLASSIFIER" 
                WHERE "contr_prod"."ContractorId" = "TBLCONTRACTOR"."Id" AND 
                "TBLCONTRACTOR"."TypeId" = 2 AND
                "CACHECONTRACTORCLASSIFIER"."ContractorId" = "contr_prod"."ContractorId" AND
                "CACHECONTRACTORCLASSIFIER"."ClassifierId" IN ('.$classifierFilter.')'.$pFilter.' ORDER BY "TBLCONTRACTOR"."Name" ASC';
        if ($filter) {
            $sql = 'SELECT * FROM ('.$sql.') WHERE "Id" NOT IN ('.$filter.')';
        }
        if ($limit) {
            $sql = 'SELECT * FROM ('.$sql.') WHERE "Id" IN ('.$limit.')';
        }
        return getDb()->querySelect($sql);
    }
    /**
     * Вернуть список производителей для SQL-запроса экономических отчетов
     * @param array|string $contractors_list список производителей
     * @return string
     */
    public static function getContractorsForDimensions($contractorsList)
    {
        $sResult = '';
        if (is_string($contractorsList)) {
            $contractors_list = explode(',', $contractors_list);           
        }
        if (count($contractorsList) > 0) {
            $idx = 0;
            foreach ($contractorsList as $contractor)
            {
                if ($idx++ != 0) {
                    $sResult .= ' UNION ';
                }
                $sResult.= ' SELECT '.$contractor.' AS "Id" FROM DUAL ';
            }
        }
        return $sResult;
    }
    /**
     * Выгрузка статистики производителей, заполняющих формы
     * @param array $params
     */
    public static function getExcelContractors(array $params = array())
    {        
        $email_for_dispatch = [];
        $email_list = ContractorEmail::getRows();
        foreach ($email_list as $val) {
            $email_for_dispatch[$val['ContractorId']][] = $val['Email'];            
        }
        unset ($email_list); 
        $defMonth = date('m') != 1 ? date('m') - 1 : 12;
        $defYear = $defMonth != 12 ? date("Y") : date("Y") - 1;
        $month = isset($params['month']) ? $params['month'] :  $defMonth;
        $year = isset($params['year']) ? $params['year'] :  $defYear;
        $filter = isset($params['filter']) ? $params['filter'] : 0;        
        $cat = isset($params['category']) ? (int) $params['category'] : 0;
        
        switch ($cat) {            
            case 1:
                $fileName = 'Производители_СХТ';
                break;
            case 2:
                $fileName = 'Производители_строительно_дорожной_техники';
                break;
            case 3:
                $fileName = 'Производители_прицепов_и_полуприцепов';
                break;                
            case 4:
                $fileName = 'Производители_техники_для_пищевой_промышленности';
                break;        
            case 5:
                $fileName = 'Производители_оборудования_и_компонентов';
                break;
             case 8:
                $fileName = 'Производители_прочей_техники';
                break;
            default:
                $fileName = 'Все_производители';
                break;                      
        }
        $thisYear = intval(date('Y'));
        
         $aYears = array($thisYear-1,$thisYear);
         if ($month > 12 or !in_array($year ,$aYears )) {
             die('Неверные параметры запроса');
        }
    
    $querySelect = 'SELECT ctr.*,tu."Email" AS "userEmail",tu."Login",
  (tu."SurName" || \' \' || tu."Name" || \' \' || tu."PatronymicName") AS "fio",
  tu."Phone" AS "userPhone", tu."Phone2" AS "userMobilePhone" FROM (
  SELECT r."Id", r."Name", LISTAGG ( CASE r."DataBaseTypeId" WHEN 1 THEN TO_CHAR(r."Date",\'dd.mm.YYYY\')
        ELSE null END ) WITHIN GROUP(order by r."Name") AS  "production",
    LISTAGG ( CASE r."DataBaseTypeId" WHEN 2 THEN TO_CHAR(r."Date",\'dd.mm.YYYY\') 
    ELSE null END ) WITHIN GROUP(order by r."Name") AS "shipment",
    LISTAGG ( CASE r."DataBaseTypeId" WHEN 4 THEN TO_CHAR(r."Date",\'dd.mm.YYYY\')
        ELSE null END ) WITHIN GROUP(order by r."Name") AS "export",
    LISTAGG ( CASE r."DataBaseTypeId" WHEN 5 THEN TO_CHAR(r."Date",\'dd.mm.YYYY\')
        ELSE null END ) WITHIN GROUP(order by r."Name") AS "import",
    LISTAGG ( CASE r."DataBaseTypeId" WHEN 12 THEN TO_CHAR(r."Date",\'dd.mm.YYYY\')
        ELSE null END ) WITHIN GROUP(order by r."Name") AS "salary",
  r."Phone" AS "contractorPhone", r."Email" AS "contractorEmail", r."Email2" AS "contractorEmail2",r."IsRosagromash"
  FROM ( SELECT DISTINCT tc."Id",tc."Name", tc."Phone", tc."Email", tc."Email2", i."DataBaseTypeId",i."Date",tc."IsRosagromash" 
  FROM  TBLCONTRACTOR tc, TBLINPUTFORM i WHERE  i."Month" = '.$month.' AND i."Year" = '.$year.'. AND
      i."ContractorId" = tc."Id" UNION SELECT c."Id", c."Name", c."Phone",c."Email", c."Email2", NULL,NULL,c."IsRosagromash"
    FROM TBLCONTRACTOR c ) r GROUP BY r."Id",r."Name",r."Email",r."Email2",r."Phone",r."IsRosagromash") ctr
    LEFT OUTER JOIN TBLUSER tu ON ctr."Id"=tu."ContractorId" WHERE ctr."Id" != 435 
    AND tu."RoleId" = 2 ORDER BY ctr."Name"';
    if ($filter == 1) {
     $querySelect = 'SELECT s1.* FROM ( '.$querySelect. ' ) s1, TBLCONTRACTOR t
  WHERE t."Id" = s1."Id" AND t."Present" = 1';
     }
    if ($cat != 0) {
         $querySelect = 'SELECT s2.* FROM ('.$querySelect.' ) s2, TBLCONTRACTORCATEGORY tcc
                 WHERE tcc."ContractorId" = s2."Id" AND tcc."CategoryId" = '.$cat;
     }
    $aData = getDb()->querySelect($querySelect);
    $len = count($aData);
    if ($len <=0)
        die('Не найдены данные для выгрузки в EXCEL');
  
 header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
 header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
 header ( "Cache-Control: no-cache, must-revalidate" );
 header ( "Pragma: no-cache" );
 header ( "Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=ANSI" );
 header ( "Content-Disposition: attachment; filename=".$fileName."(".date("d-m-Y").").xlsx" );
 
 //   require_once (ROOT_DIR.'/plugins/PHPExcel.php');
//    require_once (ROOT_DIR.'/plugins/PHPExcel/Writer/Excel5.php');
    $cols = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O');
    $xls = new Spreadsheet();   
    $xls->setActiveSheetIndex(0);
    $sheet = $xls->getActiveSheet();
    $sheet->setTitle('Таблица производителей');
   // $sheet->setTitle('Таблица производителей с информацией по заполненным формаз за период'.
       //     $this->get_month($month).','.$year.'г.');
    $sheet->setCellValue('A1','Производитель');
    $sheet->setCellValue('B1','Производство');
    $sheet->setCellValue('C1','Отгрузка в РФ');
    $sheet->setCellValue('D1','Экспорт');
    $sheet->setCellValue('E1','Импорт');
    $sheet->setCellValue('F1','Чис. и зар.');    
    $sheet->setCellValue('G1','ФИО исп.');
  //  $sheet->setCellValue('G1','Эл. почта (логин)');
    $sheet->setCellValue('H1','Раб тел. исп.');            
    $sheet->setCellValue('I1','Моб. тел. исп.');
   // $sheet->setCellValue('I1','Моб. тел. исп.');
    $sheet->setCellValue('J1','Тел. комп.');    
    $sheet->setCellValue('K1','E-mail польз.');    
    $sheet->setCellValue('L1','E-mail комп.');    
    $sheet->setCellValue('M1','E-mail комп. для отчетов');
    $sheet->setCellValue('N1','Логин');
    $sheet->setCellValue('O1','Членство в ассоциации');
    $prevId = 0;

    for ($i=0;$i<$len;$i++)
    {
        $email2 = (key_exists($aData[$i]['Id'], $email_for_dispatch)) ? implode(';',$email_for_dispatch[$aData[$i]['Id']]) : '';
     //   if ($aData['Id'][$i] != $prevId) {
        $sheet->setCellValue('A'.($i+2),$aData[$i]['Name']);
        
        
            if  (!empty($aData[$i]['production'])) {
            $sheet->getStyle('B'.($i+2))->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('B'.($i+2))->getFill()->getStartColor()->setARGB('00DCF2ED');
            $sheet->setCellValue('B'.($i+2),$aData[$i]['production']);
        }
                if (!empty($aData[$i]['shipment'])) {
            $sheet->getStyle('C'.($i+2))->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('C'.($i+2))->getFill()->getStartColor()->setARGB('00DCF2ED');
            $sheet->setCellValue('C'.($i+2),$aData[$i]['shipment']);

        }
                if (!empty($aData[$i]['export'])) {
            $sheet->getStyle('D'.($i+2))->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('D'.($i+2))->getFill()->getStartColor()->setARGB('00DCF2ED');
            $sheet->setCellValue('D'.($i+2),$aData[$i]['export']);

        }
        if (!empty($aData[$i]['import'])) {
            $sheet->getStyle('E'.($i+2))->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('E'.($i+2))->getFill()->getStartColor()->setARGB('00DCF2ED');
            $sheet->setCellValue('E'.($i+2),$aData[$i]['import']);

        }
                if (!empty($aData[$i]['salary'])) {
            $sheet->getStyle('F'.($i+2))->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('F'.($i+2))->getFill()->getStartColor()->setARGB('00DCF2ED');
            $sheet->setCellValue('F'.($i+2),$aData[$i]['salary']);

        }
            
       // $sheet->setCellValue('B'.($i+2),$aData['production'][$i]);
        //$sheet->setCellValue('C'.($i+2),$aData['shipment'][$i]);
        //$sheet->setCellValue('D'.($i+2),$aData['export'][$i]);
        //$sheet->setCellValue('E'.($i+2),$aData['salary'][$i]);
        $sheet->setCellValue('J'.($i+2),$aData[$i]['contractorPhone']);
        $sheet->setCellValue('L'.($i+2),$aData[$i]['contractorEmail']);
        $sheet->setCellValue('M'.($i+2),$email2);
       // }
        
        $sheet->setCellValue('G'.($i+2),$aData[$i]['fio']);
	$sheet->setCellValue('K'.($i+2),$aData[$i]['userEmail']);
        $sheet->setCellValue('H'.($i+2),$aData[$i]['userPhone']);
        $sheet->setCellValue('I'.($i+2),$aData[$i]['userMobilePhone']);
        $sheet->setCellValue('N'.($i+2),$aData[$i]['Login']);
        $sheet->setCellValue('O'.($i+2),($aData[$i]['IsRosagromash'] == 1 ? 'Да' : 'Нет'));
       // $prevId = $aData['Id'][$i];
    }
    $sheet->getStyle('A1:O1')->getFont()->setBold(true);
    $sheet->getStyle('A1:O'.($len+1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle('G1:O1')->getFill()->setFillType(Fill::FILL_SOLID);
    $sheet->getStyle('G1:O1')->getFill()->getStartColor()->setARGB('00F0F8FF');
    
    foreach ($cols as $val)
    {
        $sheet->getColumnDimension($val)->setAutoSize(true);
    }
   $sheet->freezePane('B2');
    $objWriter = new Xlsx($xls);                                
    $objWriter->save('php://output'); 
        
    }
}
