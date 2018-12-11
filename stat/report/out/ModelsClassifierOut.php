<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\report\out;


use app\stat\ViewHelper;
use app\stat\Tools;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use app\stat\helpers\ExcelHelper;
/**
 * Description of DefaultOut
 *
 * @author kotov
 */
class ModelsClassifierOut extends ReportCreator
{


    public function getExcelData(Spreadsheet $xls) 
    {
        if (!$this->ready) {
          return ;
        }
        $default_row_type = 'number';
        $this->othercolumns = array('Denomination');
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
        foreach ($this->othercolumns as $other_column)
        {
            $headtext='';
            switch ($other_column)
            {
                case 'Contractor':
                    $headtext = l('CONTRACTOR_SECTION','report');
                    break;

                case 'Model':
                    $headtext = l('MODEL_SECTION','report');
                    break;
                case 'Denomination':
                        $headtext = l('DENOMINATION_SECTION','report');
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
        $rows_list = [ [],
                [
                    [
                        'type' => 'text',
                        'cols' => $all_cols,
                        'size' => 14,
                        'style' => [
                            'font' => ['bold'],
                            'horizontal_align' => 'center'
                        ],
                        'value' => l('DATA_SOURCE','report').': '. rs_mb_lcfirst($this->source )
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
                        'cols' => $all_cols,
                        'value' => l('REPORT_TYPE','report').': '. rs_mb_lcfirst($this->name)
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
                        'cols' => $all_cols,
                        'value' => l('CLASSIFIER_SECTION').': '. rs_mb_lcfirst($this->classifier)
                    ]
                ]
            
        ];
        // пустая строка
        $rows_list[] = [[]];
        if (!$subheader) {
            switch ($this->dataunits[0])
            {
                case 'DataAmount':
                    $additional_data = $units.': '.$dataUnits[0].  ', '.l('DATAAMOUNT_UNIT','words');
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
                        'cols' => $all_cols,
                        'size' => 12,
                        'style' => [
                            'font' => ['bold'],
                            'horizontal_align' => 'center'
                        ],
                        'value' => $additional_data
                    ]
            ];
            $r_idx = count ($rows_list);
            foreach ($otherColumnsText as $col) {
                $rows_list[$r_idx] = [
                    [
                          'type' => 'text',
                          'value' => $col,
                          'bgcolor' => 'dbdbdb',
                        
                          'style' => [ 
                                    'font' => ['bold']
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
                $rows_list[$r_idx][] =                     [
                          'type' => 'text',
                          'value' => $value,
                          'bgcolor' => 'dbdbdb',
                        
                          'style' => [ 
                                    'font' => ['bold']
                            ] 
                      ];
            }
            $r_idx++;
        } else {
            $r_idx = count ($rows_list);
            foreach ($otherColumnsText as $col) {
                $rows_list[$r_idx] = [
                    [
                          'type' => 'text',
                          'value' => $col,
                          'bgcolor' => 'dbdbdb',
                          'rows' => 1,
                          'style' => [ 
                                    'font' => ['bold']
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
                                $headText.= ''.$this->multiplier.' '.$this->currency;
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
        
        
        
        for ($outerIdx = 0;$outerIdx < $this->rowscount;$outerIdx++ )
        {
            $row_type = $default_row_type;
            $nulls_flag = true;
            $model = false;
            $text='';
            $class='';
            $padding=0; 
            $tmp_val = array();
            if (!$this->data[0][$outerIdx]['Contractor']) {
              //  $class='main-element';
                $padding = $this->data[0][$outerIdx]['Level'];
                $row_style =  [
                    'font' => ['bold']
                ];
                $text = $this->data[0][$outerIdx]['Classifier'] .','.mb_convert_case (l('ALL','words'),MB_CASE_LOWER);
            }
            else {
                $model = true;
              //  $class = 'sub-element';
                $row_style =  [
                    'font' => ['italic']
                ];
                
                $text = $this->data[0][$outerIdx]['Model'];
                $padding = $this->data[0][$outerIdx]['Level']+1;
            }
            $tmp_val = [
                [
                    'type' => 'text',
                    'align' => $padding,
                    'style' => $row_style,
                    'value' => $text
                    ]
                ];
            $zero = false; // нудевое значение ячейки
            for ($innerIdx = 0;$innerIdx < $colsCount;$innerIdx++ )
            {
                foreach ($this->dataunits as $unit)
                {
                    $period = $this->data[$innerIdx][$outerIdx]['Period'];
                    
                    $val = $this->data[$innerIdx][$outerIdx][$unit] ?? 0;
                    if (!empty($val)) {
                        $nulls_flag = false;
                    } elseif (!stristr($period, 'proportion')) {
                        $zero = true;
                    }
                    if (is_numeric($val) && !stristr($period, 'proportion')) 
                    {
                        $value = round($val);
                    } else {
                        if (!stristr($period, 'proportion')) {
                            $value = $val;
                        } else {
                            $value = $this->percentConvert(round($val,2),true,$zero);
                            $zero = false;
                        }
                        $row_type = 'special_number';
                    } 
                    
              $tmp_val[] = 
                [
                    'type' => $row_type,
                    'value' => $value
                    ];                                        
                }
            }
            
             if (( !$model || !$this->nonulls) || !$nulls_flag) {
                 $rows_list[] = $tmp_val;
            }
            
            
        }
        
        $activeSheet->getColumnDimension('B')->setWidth(40);
        $activeSheet->getRowDimension(1)->setRowHeight(18);
        $activeSheet->getRowDimension(2)->setRowHeight(18);
        $activeSheet->getRowDimension(3)->setRowHeight(18);
        $activeSheet->getRowDimension(4)->setRowHeight(18);        
        $activeSheet->getStyle('B')->getAlignment()->setWrapText(true);
        
        $cnt = $colsCount * $duCount;
        for ($j = 0; $j < $cnt; $j++) {
                $activeSheet->getColumnDimensionByColumn(2 + $j)->setWidth(16);
                        //->getAlignment()->setWrapText(true);
        }
        
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
      // Переопределение стандартных значений для вывода
      $this->othercolumns = array('Denomination');
      $proportions = l('PROPORTIONS','report');
      $colsCount = count($this->data);
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
                $this->tpl_vars['additional_data'] = ' ('.l('DATAAMOUNT_UNIT','words').')';
            break;
            case 'DataPrice':
              $this->tpl_vars['additional_data'] = ' ('.$this->multiplier.' '.$this->currency.' )';
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
                        $headtext = l('CONTRACTOR_SECTION','report');
                        break;
                    case 'Classifier':
                        $headtext = l('CLASSIFIER_SECTION','report');
                        break;
                    case 'Model':
                        $headtext = l('MODEL_SECTION','report');
                        break;
                    case 'Denomination':
                        $headtext = l('DENOMINATION_SECTION','report');
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
                    $headText = l($dataunit,'words').'<br>';
                    if (!stristr($this->headarray[$i], 'proportion')) {                            
                        switch ($dataunit)
                        {
                            case 'DataAmount':
                                $headText.= ' ('.l('DATAAMOUNT_UNIT','words').')';
                                break;
                            case 'DataPrice':
                                $headText.= ' ('.$this->multiplier.' '.$this->currency.')';
                                break;
                        }
                    }
                    $this->tpl_vars['header2_columns'][] = $headText;
                } 
            }
        }
        $sResult = '';
                // Вывод содержимого таблицы        
        for ($outerIdx = 0;$outerIdx < $this->rowscount;$outerIdx++ )
        {
            $nulls_flag = true;
            $model = false;
            $text='';
            $class='';
            $padding=0;  
            $tmp_val = '<tr>';
            if (!$this->data[0][$outerIdx]['Contractor']) {
                $class='main-element';
                $padding = $this->data[0][$outerIdx]['Level'];
                $text = $this->data[0][$outerIdx]['Classifier'] .','.mb_convert_case (l('ALL','words'),MB_CASE_LOWER);
            }
            else {
                $model = true;
                $class = 'sub-element';
                $text = $this->data[0][$outerIdx]['Model'];
                $padding = $this->data[0][$outerIdx]['Level']+1;
            }

                $tmp_val.='<td class="'.$class.'" style="padding-left:'.$padding.'em">'.$text.'</td>';
            for ($innerIdx = 0;$innerIdx < $colsCount;$innerIdx++ )
            {
                foreach ($this->dataunits as $unit)
                {
                    $period = $this->data[$innerIdx][$outerIdx]['Period'];
                    $val = $this->data[$innerIdx][$outerIdx][$unit];
                    if (!empty($val)) {
                        $nulls_flag = false;
                    }
                    if (is_numeric($val) && !stristr($period, 'proportion')) 
                    {
                        $value = Tools::addSpaces(round($val));
                    } else {
                        $value = !stristr($period, 'proportion') ? $val : ( $val ? $this->percentConvert(round($val,2)) : '-');
                    }                   
                    $tmp_val.='<td>'.$value.'</td>';
                }
            }
            $tmp_val.= '<tr>';
            if ( (!$model || !$this->nonulls) || !$nulls_flag) {
                $sResult.=$tmp_val;
            }
        }
   
        
      $this->tpl_vars['data_source'] = l('DATA_SOURCE','report');
      $this->tpl_vars['data_source_val'] = mb_convert_case($this->source,MB_CASE_LOWER);
      $this->tpl_vars['report_type'] = l('REPORT_TYPE','report');
      $this->tpl_vars['report_type_name'] = $this->name;
      $this->tpl_vars['classifier'] = $this->classifier;
      $this->tpl_vars['classifier_section'] = l('CLASSIFIER_SECTION');
      $this->tpl_vars['data_count'] = count($this->dataunits);
      $this->tpl_vars['units'] = l('UNIT','report');
      foreach ($this->dataunits as $unit) {
        $this->tpl_vars['data_units'][] = l($unit,'words');
        $this->tpl_vars['colspan'] = $this->tpl_vars['subheader'] ? ' colspan='.$dataCount : '';
        $this->tpl_vars['table_data'] = $sResult;      
      }
      
      
      
            
        $viewHelper = new ViewHelper(_REPORTS_OUT_TEMPLATES_DIR_,'default',$this->tpl_vars);
        return $viewHelper->getRenderedTemplate();          
    }
        

}
