<?php


namespace app\stat\report\out;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
/**
 *
 * @author kotov
 */
interface IReport {
    /**
     *
     */
     public function getWebTableData();
     /**
      * Формирование файла Excel с отчетом
      * @param Spreadsheet $xls 
      */
     public function getExcelData( Spreadsheet $xls ) ;
             
}
