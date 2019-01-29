<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\report\out;

use app\stat\Tools;
use app\stat\ViewHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use app\stat\helpers\ExcelHelper;
/**
 * Description of DefaultOut
 *
 * @author kotov
 */
class DefaultOut extends ReportCreator
{


    public function getExcelData(Spreadsheet $xls) 
    {
        $activeSheet = $xls->getActiveSheet();
        $nds = ( $this->sourceid == 4 ) ? 'NO_NDS' : 'NDS';
        $proportions = l('PROPORTIONS','report');
        $colsCount = count($this->data);
        $duCount = count ($this->dataunits);
        $otherColumnsCount = count ($this->othercolumns);
        $otherColumnsText = array();
        $units = l('UNIT','report');
        $dataUnits = array();
        foreach ($this->dataunits as $unit) {
            $dataUnits[]= l($unit,'words');
        }        
        if (count($this->othercolumns) == 0) {
            array_push($this->othercolumns,'Virtual');
        }
        foreach ($this->othercolumns as $other_column)
        {
            $headtext='';
            switch ($other_column)
            {
                case 'Contractor':
                    $headtext = l('COMPANY_SECTION','report');
                    break;

                case 'Model':                    
                    if ($this->reportname != 'models') {
                        $headtext = l('MODEL_SECTION','report');
                    }
                    else $headtext = 'Предприятие - Модель';
                    break;
            }                                
               $otherColumnsText[] = $headtext;               
        }
        
        
        $otherColumnsCount = count($this->othercolumns);
        $duCount = count($this->dataunits);
        if ($duCount == 1) { 
            $subheader  = false;
        }
        else {
            $subheader = true;      
        }
        if (!$this->ready) {
          return ;
        }
        if (!$subheader) {
            $additional_data = '';
        switch ($this->dataunits[0])
        {
            case 'DataAmount':
                $additional_data = ', '.l('DATAAMOUNT_UNIT','words');
            break;
            case 'DataPrice':
              $additional_data = ', '.$this->multiplier.' '.$this->currency.' ('.l($nds).')';
            break;            
            }            
        }

        $all_cols = $otherColumnsCount + $colsCount * $duCount ;
        $all_excel_cols = $all_cols - 1;
        
        // заголовок
        $classifierHeader = (is_array($this->classifierid) && count($this->classifierid) > 1) ?
                l('CLASSIFIER_SECTIONS','report') :
                l('CLASSIFIER_SECTION','report');
        $rows_list = [ [],
                [
                    [
                        'type' => 'text',
                        'cols' => $all_cols - 1,
                        'size' => 14,
                        'style' => [
                            'font' => ['bold'],
                            'horizontal_align' => 'center'
                        ],
                        'value' => l('DATA_SOURCE','report').': '. $this->source 
                    ] 
                ],
                [
                    [
                        'type' => 'text',
                        'size' => 14,
                        'style' => [
                            'font' => ['bold'],
                            'horizontal_align' => 'center'
                        ],
                        'cols' => $all_cols - 1,
                        'value' => l('REPORT_TYPE','report').': '. $this->name
                    ]
                ],
            [
                    [
                        'type' => 'text',
                        'size' => 14,
                        'style' => [
                            'font' => ['bold'],
                            'horizontal_align' => 'center'
                        ],
                        'cols' => $all_cols - 1,
                        'value' => $classifierHeader.': '. $this->classifier
                    ]
                ]
            
        ];
        // пустая строка
        $rows_list[] = [[]];
        if (!$this->ready) {
            return ;
        }
        if (!$subheader) {
            switch ($this->dataunits[0])
            {
                case 'DataAmount':
                    $additional_data = $units.': '.$dataUnits[0].  ', '.l('DATAAMOUNT_UNIT','words');
                    break;
                case 'DataAverageSalaryAll':
                case 'DataFondAll':
                    $additional_data = $units.': ' .$dataUnits[0]. ', '.$this->multiplier.' '.$this->currency;
                    break;
                case 'DataPrice':
                    $additional_data = $units.': ' .$dataUnits[0]. ', '.$this->multiplier.' '.$this->currency.' ('.l($nds).')';
                    break;            
                default:
                    $additional_data = $units.': ' . $dataUnits[0];
                    break;
            }
            
            
            $rows_list[] =  [
                    [
                        'type' => 'text',
                        'cols' => $all_cols - 1,
                        'size' => 12,
                        'style' => [
                            'font' => ['bold'],
                        ],
                        'value' => $additional_data
                    ]
            ];
            $r_idx = count ($rows_list);
            $start_border_index = $r_idx;
            foreach ($otherColumnsText as $col) {
                $rows_list[$r_idx] = [
                    [
                          'type' => 'text',
                          'value' => $col,
                          'bgcolor' => 'dbdbdb',
                        
                          'style' => [ 
                                    'font' => ['bold'],
                            ] 
                      ]
                    
                ];
            }
            foreach ($this->headarray as $val) {
                if (stristr($val, 'proportion') && key_exists($val, $proportions )) {
                   $value = $proportions[$val];                       
                } else {
                    $value = $val;
                }
               // $value = !is_demo() ? $value : '';
                $rows_list[$r_idx][] =                     [
                          'type' => 'space_text',
                          'value' => $value,
                          'bgcolor' => 'dbdbdb',
                        
                          'style' => [ 
                                    'font' => ['bold'],
                                    'horizontal_align' => 'center'
                            ] 
                      ];
            }
            $r_idx++;
        } else {
            $r_idx = count ($rows_list);
            $start_border_index = $r_idx;
            foreach ($otherColumnsText as $col) {
                $rows_list[$r_idx] = [
                    [
                          'type' => 'text',
                          'value' => $col,
                          'bgcolor' => 'dbdbdb',
                          'rows' => 1,
                          'style' => [ 
                                    'font' => ['bold'],                                    
                            ] 
                      ]
                    
                ];
                $h_idx = 1;
                $h_idx2 = 1;
            }
            foreach ($this->headarray as $val) {
                if (stristr($val, 'proportion') && key_exists($val, $proportions )) {
                    $value = $proportions[$val];                       
                } else {
                    $value = $val;
                }
             //   $value = !is_demo() ? $value : '';
                $rows_list[$r_idx][$h_idx] = [
                      'type' => 'text',
                      'value' => $value,
                      'bgcolor' => 'dbdbdb',
                      'cols' => $duCount - 1,
                      'style' => [ 
                                'font' => ['bold'],
                                'horizontal_align' => 'center'
                        ] 
                  ];                
                foreach ($this->dataunits as $dataunit) {
                    $headText = l($dataunit,'words');
                    if (!stristr($val, 'proportion')) {                            
                        switch ($dataunit)
                        {
                            case 'DataAmount':
                                $headText.= ', '.l('DATAAMOUNT_UNIT','words');
                                break;                            
                            case 'DataAverageSalaryAll':
                            case 'DataFondAll':
                                $headText.= ', '.$this->multiplier.' '.$this->currency;
                                break;
                            case 'DataPrice':                                
                                $headText.= ', '.$this->multiplier.' '.$this->currency. ' ('.l($nds).')';
                                break;
                        }
                    }
                    $rows_list[$r_idx+1][$h_idx2++] = [
                          'type' => 'text',
                          'value' => $headText,
                          'bgcolor' => 'dbdbdb',
                        
                          'size' => 10,
                          'wrap' => true,
                          'style' => [ 
                                    'font' => ['bold'],
                                    'horizontal_align' => 'center'
                            ] 
                      ];
                    
                      
                
                $h_idx+=1;
            }
        }
            
            $r_idx++;
            $rows_list[$r_idx][] = [];
            
        }
        
        $first_index = false;
        $row_index = 0; // Индекс строки
        $rows = array();
        $default_row_type = 'number';
        for ($outerIdx = 0;$outerIdx < $this->rowscount;$outerIdx++ ) {
            $row_type = $default_row_type;
            $row = array();
            $nullFlag = true;            
            $row_style_first = $row_style = $outerIdx == 0 ? ['font' => ['bold']] : array();            
            $row_style['horizontal_align'] = 'center';
            $row_style_first['horizontal_align'] = 'left';
            $total = $outerIdx == 0 ? true : false;
            if (count ($this->othercolumns) == 1 && key_exists('Region', $this->data[0][$outerIdx])) {
                $all = false;
                if (!$this->data[0][$outerIdx]['Region']) {
                    $all = true;
                }
                $column_name = $this->othercolumns[0];               
                if (!$this->data[0][$outerIdx][$column_name]) {
                    $row_style_first['font']= ['bold'];
                }
            }                        
            if ($otherColumnsCount > 0) {
                foreach ($this->othercolumns as $cName) {
                    $row_text = $cName != 'Virtual' ? $this->data[0][$outerIdx][$cName] : null;
                   // $nullFlag = false; 
                    if ($row_text == 'InAverage') {
                        $row2 = array();
                        $row2[] = [
                          'type' => 'text',
                          'style' => $row_style_first,
                          'value' => 'Всего по компаниям'  
                        ];
                        $row[] = [
                          'type' => 'text',
                          'style' => $row_style_first,
                          'value' => 'Среднее по компаниям'  
                        ];
                    for ($innerIdx = 0;$innerIdx < $colsCount;$innerIdx++ ) {
                        foreach ($this->dataunits as $unit) {
                            $period = $this->data[$innerIdx][$outerIdx]['Period'];
                            $prop = stristr($period, 'proportion');
                            $row_type = $prop ? 'special_number' : $default_row_type;                            
                            $val1 = $this->data[$innerIdx][$outerIdx][$unit];
                            $val2 = $this->data[$innerIdx][$outerIdx][$unit.'Avg'];                            
                            if (is_numeric($val1) && !$prop) {
                                $value1 = round($val1);
                             //   $value1 = \Rosagromash\Tools::addSpaces($val1);
                            } else {
                                $value1 = !$prop ? $val1 : $this->percentConvert(round($val1,2),true);
                            }  
                            if (is_numeric($val2) && !$prop) {
                                $value2 = round($val2);
                            //    $value2 = \Rosagromash\Tools::addSpaces($val2);
                            } else {
                                $value2 = !$prop ? $val2 : $this->percentConvert(round($val2,2),true);
                               // $row_type = 'number';
                            }
                             $value1 = !is_demo() ? $value1 : '';                           
                             $value2 = !is_demo() ? $value2 : '';                           
                            $row2[] = [
                                'type' => $row_type,
                                'style' => $row_style,
                                'value' => $value1
                            ];
                            $row[] = [
                                'type' => $row_type,
                                'style' => $row_style,
                                'value' => $value2
                                ];
                        }
                    }  
                  //  $row
                    $first_index = true; 
                    continue;
                    }
                    if ($cName == 'Virtual' && $total) {
                        $row[] = [
                          'type' => 'text',
                          'style' => $row_style_first,
                          'value' => l('ALL','words')  
                        ];
                    }
                    else {
                    if (key_exists('Region', $this->data[0][$outerIdx])) {
                        if ($this->data[0][$outerIdx]['Region'] && !$row_text) {                                             
                        $row_text = $this->data[0][$outerIdx]['Region'];
                        }
                    }
                    $row_text = $row_text ?? l('ALL','words');
                    $row[] = [
                        'type' => 'text',
                        'style' => $row_style_first,
                        'value' => $row_text
                        ];
                        
                    }                                    
                }
            }
            if (!$first_index) {
                $nullFlag = true;
                $zero = false; // нудевое значение ячейки
                for ($innerIdx = 0;$innerIdx < $colsCount;$innerIdx++ ) {
                    
                    foreach ($this->dataunits as $unit) {                    
                        $period = $this->data[$innerIdx][$outerIdx]['Period'];
                        $val = $this->data[$innerIdx][$outerIdx][$unit];
                        if (!empty ($val)) {
                           $nullFlag = false;                          
                        } elseif (!stristr($period, 'proportion')) {
                            $zero = true;
                        }
                        if (is_numeric($val) && !stristr($period, 'proportion')) 
                        {
                            $value = round($val);                            
                         //   $value = \Rosagromash\Tools::addSpaces($val);
                        } else {
                            if (!stristr($period, 'proportion')) {
                                $value = $val;
                            } else {
                                $value = $this->percentConvert(round($val,2),true,$zero);
                                $zero = false;
                            }
                            $row_type = 'special_number';
                        }
                        $value = !is_demo() ? $value : '';
                        $row[] = [
                            'type' => $row_type,
                            'style' => $row_style,
                            'value' => $value
                        ];
                    }
                } 
                
             //   $rows[$row_index++] = $row ;
            } else {
                $first_index = false;
            }
            if ($this->nonulls && !$total && !($outerIdx == 0))  {
                if (empty($nullFlag)) {
                    $rows[$row_index++] = $row;
                }
            } else {
                $rows[$row_index++] = $row ;
            } 
            if (!empty($row2)) {
                if (!in_array('DataAverageSalaryAll', $this->dataunits)) {
                    $rows[$row_index++] = $row2;
                }                
                $row2 = [];
            }
            
               
            //    $rows[$row_index++] = $row2 ;
      
                
      //  $rows_list = array_merge($rows_list,$rows);    
        }
        
        $rows_list = array_merge($rows_list,$rows);
        
        
        // Предварительная настройка листа        
     //   $activeSheet->getColumnDimension('B')->setWidth(30);
        $activeSheet->getRowDimension(1)->setRowHeight(18);
        $activeSheet->getRowDimension(2)->setRowHeight(18);
        $activeSheet->getRowDimension(3)->setRowHeight(18);
        $activeSheet->getRowDimension(4)->setRowHeight(18);
        if ($otherColumnsCount > 0) {
            for ($i = 1 ; $i <= $otherColumnsCount; $i++ ) {
                $activeSheet->getColumnDimensionByColumn($i)->setWidth(30);
            }
            $cnt = $colsCount * $duCount;
            for ($j = 0; $j < $cnt; $j++) {
                $activeSheet->getColumnDimensionByColumn($i + $j)->setWidth(16);
                        //->getAlignment()->setWrapText(true);
            }
        }
        $activeSheet->getStyle('B')->getAlignment()->setWrapText(true);
        $styleArray = array(
                        'borders' => array(
                            'allBorders' => array(
                                'borderStyle' => Border::BORDER_THIN,
                                
                            ),
                        ),
                    );
        $activeSheet->getStyleByColumnAndRow(1, $start_border_index, ($i + $j - 1), count($rows_list) - 1)->applyFromArray($styleArray);
       // $activeSheet->getStyle('B')->getAlignment()->setIndent(5);


        /*
        for ($i = 0;$i<$all_cols;$i++) {
            $activeSheet->getColumnDimensionByColumn(2 + $i)->setWidth(15);
        }
         * 
         */
        
        
        $commonStyles = [ 
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
         ];        
        $xlsHelper = new ExcelHelper($activeSheet);
        
        $xlsHelper->formatExcelSheetFromArray($rows_list,$commonStyles);                    
    }

    public function getWebTableData() 
    {
        $months = l('MONTHS','words');
        if ($this->userfilterenable) {
            if (count($this->userfilter) > 0) {
                $filter_months_str = '';
                foreach ($this->userfilter as $period) {
                     $month_idx = (int) $period['Month'];
                     $filter_months_str.= $months[$month_idx] . ' ' . $period['Year'].', ';
                }
                $filter_months_str = trim($filter_months_str, ', ');
            }
        }
      $alias =  'default' ;
      // добавление НДС для всех источников данных кроме экспорта
      $nds = ( $this->sourceid == 4 ) ? 'NO_NDS' : 'NDS';
      $proportions = l('PROPORTIONS','report');
      $colsCount = count($this->data);
      if (count($this->othercolumns) == 0) {
          array_push($this->othercolumns,'Virtual');
      }
      $otherColumnsCount = count($this->othercolumns);
      $dataCount = count($this->dataunits);
      if ($dataCount == 1) { 
         $this->tpl_vars['subheader']  = false;
      }
      else {
          $this->tpl_vars['subheader'] = true;      
      }
      $sResult = '';
      
      if (!$this->ready) {
          return $sResult;
      }
      if (count($this->dataunits) == 1) {
        $this->tpl_vars['additional_data'] = '';
        switch ($this->dataunits[0])
        {
            case 'DataAmount':
                $this->tpl_vars['additional_data'] = ', '.l('DATAAMOUNT_UNIT','words');
            break;
            case 'DataAverageSalaryAll':
            case 'DataFondAll':
                $this->tpl_vars['additional_data'] = ', '.$this->multiplier.' '.$this->currency;
            break;
            case 'DataPrice':
              $this->tpl_vars['additional_data'] = ', '.$this->multiplier.' '.$this->currency.' ('.l($nds).')';
            break;            
            }
            
        }
        
        // Создание заголовка       
        $tmp2Result = '';
        $this->tpl_vars['other_columns'] = array();
        $this->tpl_vars['head_columns'] = array();
        $this->tpl_vars['header2_columns'] = array();
        foreach ($this->othercolumns as $other_column)
            {
                $headtext='';
                switch ($other_column)
                {
                    case 'Contractor':
                        $headtext = l('COMPANY_SECTION','report');
                        break;

                    case 'Model':
                    if ($this->reportname != 'models') {
                        $headtext = l('MODEL_SECTION','report');
                    }
                    else $headtext = 'Предприятие - Модель';
                        break;
                }
                
                
               $this->tpl_vars['other_columns'][] = $headtext;
                if ($this->tpl_vars['subheader']) {
                    $this->tpl_vars['header2_columns'][]  = '';
                }                
            }
        foreach ($this->headarray as $val)
        {
            if (stristr($val, 'proportion') && key_exists($val, $proportions )) {
                $value = $proportions[$val];                       
            } else {
                $value = $val;
            }
            $this->tpl_vars['head_columns'][] = $value;
        }
        if ($this->tpl_vars['subheader']) {
        // создание подзаголовка

            for ($i=0; $i<count($this->headarray);$i++)
            {
                foreach ($this->dataunits as $dataunit)
                {
                    $headText = l($dataunit,'words');
                    if (!stristr($this->headarray[$i], 'proportion')) {                            
                        switch ($dataunit)
                        {
                            case 'DataAmount':
                                $headText.= ', '.l('DATAAMOUNT_UNIT','words');
                                break;
                            
                            case 'DataAverageSalaryAll':
                            case 'DataFondAll':
                                $headText.= ', '.$this->multiplier.' '.$this->currency;
                                break;
                            case 'DataPrice':                                
                                $headText.= ', '.$this->multiplier.' '.$this->currency. ' ('.l($nds).')';
                                break;
                        }
                    }
                    $this->tpl_vars['header2_columns'][] =$headText;
                }
                
            }
        }
        $sResult = '';
        $first_index = false;
                // Вывод содержимого таблицы        
        for ($outerIdx = 0;$outerIdx < $this->rowscount;$outerIdx++ )
        {            
            $all = false;
            $nullFlag = true;
            $row = '';
            $total = $outerIdx == 0 ? ' class="total"' : ' class="data"';
            if (count ($this->othercolumns) == 1 && key_exists('Region', $this->data[0][$outerIdx])) {
                
                if (!$this->data[0][$outerIdx]['Region']) {
                    $total = ' class="total"';
                    $all = true;
                }
                $column = $this->othercolumns[0];
                if (!$this->data[0][$outerIdx][$column]) {
                    $total = ' class="total"';
                }
                        
            }
            
            $row .= '<tr'.$total.'>';
            if ($otherColumnsCount > 0) {
                foreach ($this->othercolumns as $cName)
                {   
                    $row_text = $cName != 'Virtual' ? $this->data[0][$outerIdx][$cName] : null;
                    if ($row_text == 'InAverage') {
                        //continue;
                        $row2 = '<tr'.$total.'>';
                        $row2_text = "Среднее по компаниям";
                        $row1_text = "Всего по компаниям";
                        $row.='<td>'.$row1_text.'</td>';
                        $row2.='<td>'.$row2_text.'</td>';
                        
                        for ($innerIdx = 0;$innerIdx < $colsCount;$innerIdx++ ) {
                             foreach ($this->dataunits as $unit)
                             {
                                $period = $this->data[$innerIdx][$outerIdx]['Period'];
                                $val1 = $this->data[$innerIdx][$outerIdx][$unit];
                                $val2 = $this->data[$innerIdx][$outerIdx][$unit.'Avg'];
                                if (is_numeric($val1) && !stristr($period, 'proportion')) {
                                    $val1 = round($val1);
                                    $value1 = Tools::addSpaces($val1);
                                } else {
                                    $value1 = !stristr($period, 'proportion') ? $val1 : $this->percentConvert(round($val1,2));
                                }  
                                if (is_numeric($val2) && !stristr($period, 'proportion')) {
                                    $val2 = round($val2);
                                    $value2 = Tools::addSpaces($val2);
                                } else {
                                    $value2 = !stristr($period, 'proportion') ? $val2 : $this->percentConvert(round($val2,2));
                                }
                                $value1 = !is_demo() ? $value1 : '';
                                $value2 = !is_demo() ? $value2 : '';
                                $row.='<td class="digit-val">'.$value1.'</td>';
                                $row2.='<td class="digit-val">'.$value2.'</td>';                                                                
                             }
                        }
                       $row .= '</tr>';
                       $row2.='</tr>';
                       if (!in_array('DataAverageSalaryAll', $this->dataunits)) {
                            $row=$row.$row2;
                       } else {
                           $row = $row2;
                       }
                       $first_index = true;
                       continue;
                       
                    }
                    if ($cName == 'Virtual' && $all) {
                        $row_text = l('ALL','words');                                              
                    } 
                    if (key_exists('Region', $this->data[0][$outerIdx])) {
                        if ($this->data[0][$outerIdx]['Region'] && !$row_text) {
                            $row_text = $this->data[0][$outerIdx]['Region'];
                        }
                    }
                    $row_text = $row_text ?? l('ALL','words');
                    $row.='<td>'.$row_text.'</td>';
                }
            }
            if (!$first_index) {
            for ($innerIdx = 0;$innerIdx < $colsCount;$innerIdx++ )
            {
                foreach ($this->dataunits as $unit)
                {
                    $period = $this->data[$innerIdx][$outerIdx]['Period'];
                    $val = $this->data[$innerIdx][$outerIdx][$unit];
                    if (!empty ($val)) {
                        $nullFlag = false;
                    }
                    if (is_numeric($val) && !stristr($period, 'proportion')) 
                    {
                        $val = round($val);
                        $value = Tools::addSpaces($val);
                    } else {
                        $value = !stristr($period, 'proportion') ? $val : ( $val ? $this->percentConvert(round($val,2)) : '-');
                    }
                    $value = !is_demo() ? $value : '';
                    $row.='<td class="digit-val">'.$value.'</td>';
                }
            }            
            $row .= '</tr>';
        }
        else {
            $first_index = false;
        }
            if ($this->nonulls && empty($all) && !($outerIdx == 0)) {
                if (empty($nullFlag)) {
                    $sResult.=$row;
                }
            } else {
                $sResult.= $row;
            }            
        }
   
        
      $this->tpl_vars['data_source'] = l('DATA_SOURCE','report');
      $this->tpl_vars['data_source_val'] = $this->source;
      $this->tpl_vars['report_type'] = l('REPORT_TYPE','report');
      $this->tpl_vars['report_type_name'] = $this->name;                      
      $this->tpl_vars['classifier_section'] = (is_array($this->classifierid) && count($this->classifierid) > 1) ? 
              l('CLASSIFIER_SECTIONS','report') :
              l('CLASSIFIER_SECTION','report');
      $this->tpl_vars['classifier'] = $this->classifier;
      $this->tpl_vars['data_count'] = count($this->dataunits);
      $this->tpl_vars['units'] = l('UNIT','report');      
      foreach ($this->dataunits as $unit) {
      $this->tpl_vars['data_units'][] = l($unit,'words');
      $this->tpl_vars['colspan'] = $this->tpl_vars['subheader'] ? ' colspan='.$dataCount : '';
      $this->tpl_vars['table_data'] = $sResult;
      if (!empty($filter_months_str)) {
        $this->tpl_vars['user_filter_enable'] = $this->userfilterenable;
        $this->tpl_vars['user_filter_month'] = $filter_months_str;
      }
      
      }
      
      
      
      
        ob_start();
        $viewHelper = new ViewHelper(_REPORTS_OUT_TEMPLATES_DIR_,$alias,$this->tpl_vars);        
        return $viewHelper->getRenderedTemplate();

    }        
}