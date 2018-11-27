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
class ModelsContractorOut extends ReportCreator
{

    public function __construct(array $data, $columnName = "Period") {
        parent::__construct($data, $columnName);
        $this->othercolumns=array('Model');
    }

    public function getExcelData(Spreadsheet $xls) 
    {
        if (!$this->ready) {
            return ;
        }
        $activeSheet = $xls->getActiveSheet();
        $nds = ( $this->source_id == 4 ) ? 'NO_NDS' : 'NDS';
        $default_row_type = 'number';
        $proportions = l('PROPORTIONS','report');
        $colsCount = count($this->data);
        $duCount = count ($this->dataunits);
        $otherColumnsCount = count ($this->othercolumns);
        $otherColumnsText = array();
        $units = l('UNIT','report');
        $dataUnits = array();
        $header_array = array();
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
            }                                
               $otherColumnsText[] = $headtext;               
        }
        
        
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
        
        
        if (!$subheader) {
            $header_array = [];
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
            foreach ($otherColumnsText as $col) {
                $header_array[0] = [
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
                $header_array[0][] =                     [
                          'type' => 'text',
                          'value' => $value,
                          'bgcolor' => 'dbdbdb',
                        
                          'style' => [ 
                                    'font' => ['bold']
                            ] 
                      ];
            }         
        } else {
            $r_idx = count ($header_array);
            foreach ($otherColumnsText as $col) {
                $header_array[$r_idx] = [
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
                $header_array[$r_idx][$h_idx] = [
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
                    $header_array[$r_idx+1][$h_idx2++] = [
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
            $header_array[$r_idx][] = [];
        }        
        
        
        // Вывод содержимого таблицы   
        
        // Вывод производителей
      //  $all_cols= count ($this->tpl_vars['head_columns']) * $dataCount + count($this->othercolumns);
        $ctr_idx = 0;
        $all_parts = array();
        $models_list = array();
        $ctr_count = count($this->data);
        $contractors_table = array();
        for ($ctr_idx;$ctr_idx<$ctr_count;$ctr_idx++) {
            if ($models_list) {
                //TODO  
                
            }
            $elem = current($this->data[$ctr_idx]);
            $contractors_header = [
                   [
                        'type' => 'text',
                        'cols' => $all_cols,                    
                        'style' => [
                            'font' => ['bold'],
                            'horizontal_align' => 'center'
                        ],
                        'value' => $elem[0][0]['Contractor']
                    ]                                               
            ];
            $contractors_table_index = 1;
            // По источнику данных
            $mdl_flag = false;
            $nulls_flag = true;
            $all_nulls_flag = true;
            $models_list = [];
            foreach ($this->data[$ctr_idx] as $dtc => $ctr)
            {
                if (!empty($models_list)) {
                    $contractors_table[$ctr_idx] = array_merge($contractors_table[$ctr_idx],$models_list);
                    $contractors_table_index = count ($contractors_table[$ctr_idx]);
                    $models_list = [];
                }
                $contractors_table[$ctr_idx][$contractors_table_index++] = [
                    [
                        'type' => 'text',
                        'cols' => $all_cols,                    
                        'style' => [
                            'font' => ['bold'],
                            'horizontal_align' => 'center'
                        ],
                        'value' => $dtc
                        
                    ]
                ];
                
                // список моделей
                $cnt = count ($ctr);
                $rows_count = count($ctr[0]);
                for ($row_idx=0;$row_idx< $rows_count;$row_idx++) {
                    if (!empty($models_list)) {
                        if ($new_mb) {
                            $contractors_table[$ctr_idx][$contractors_table_index++] = $all_parts;
                            $new_mb = false;                        
                        }
                        $contractors_table[$ctr_idx] = array_merge($contractors_table[$ctr_idx],$models_list);
                        $contractors_table_index = count ($contractors_table[$ctr_idx]); 
                        $models_list = [];
                    }
                    $nulls_flag = true;
                    $model = $ctr[0][$row_idx]['Model'];
                    $classifier = $ctr[0][$row_idx]['Classifier'];
                    if (!$model) {
                        $mdl_flag = false;
                        $all_nulls_flag = true;
                        if (!empty($models_list)) {
                            $contractors_table[$ctr_idx] = array_merge($contractors_table[$ctr_idx],$models_list);
                            $contractors_table_index = count ($contractors_table[$ctr_idx]);
                            $models_list = [];
                        }
                        if ($classifier) {
                            $new_mb = true;
                            $contractors_table[$ctr_idx][$contractors_table_index++] = [
                                  [
                                    'type' => 'text',
                                    'style' => [
                                        'font' => ['bold'],
                                        'horizontal_align' => 'left'
                                    ],
                                    'value' => $classifier .', всего'
                        
                                ]
                            ];                            
                        }
                        else {
                            continue;
                        }
                    }
                    else {
                        $mdl_flag = true;
                        $models_list[] = [
                                            [
                            'type' => 'text',
                            'align' => 1,
                            'style' => [
                                'font' => ['italic']
                                ],
                            'value' => $model
                            ]                                                        
                        ];
                    }
                    $models_idx = count ($models_list);  
                    
                    for ($col_idx=0;$col_idx<$cnt;$col_idx++) 
                    {
                        
                        foreach ($this->dataunits as $unit)
                        {
                            $row_type = $default_row_type;
                            $period = $ctr[$col_idx][$row_idx]['Period'];
                            $val = $ctr[$col_idx][$row_idx][$unit];
                            if (!empty($val)) {
                                $nulls_flag = false;
                                $all_nulls_flag = false;
                            }
                            if (is_numeric($val) && !stristr($period, 'proportion')) 
                            {
                                $value = round($val);
                            } else {
                                $value = !stristr($period, 'proportion') ? $val : $this->percentConvert(round($val,2),true);
                                $row_type = 'special_number';
                            }
                            if ($mdl_flag) { 
                                $models_list[$models_idx - 1][] = [
                                        'type' => $row_type,
                                        'value' => $value
                                ];
                            }
                            else {
                                $contractors_table[$ctr_idx][$contractors_table_index - 1][] = [
                                    'type' => $row_type,
                                    'style' => [
                                    'font' => ['bold']
                                ],
                                    'value' => $value
                                ];    
                            }
                        }
                    }                    
                    if ($mdl_flag) {
                        $models_idx++;
                    } else 
                        {
                        $contractors_table_index++;
                    }
                    if ($new_mb) {
                        $all_parts = [
                                  [
                                    'cols' => $all_cols,
                                    'type' => 'text',
                                    'style' => [
                                        'font' => ['bold'],
                                        'horizontal_align' => 'left'
                                    ],
                                    'value' => 'В том числе:'
                        
                                ]
                            ];     
                        
                    }
                    if ($this->nonulls) {
                        if ($nulls_flag) {
                            $models_list=[];
                        }
                    }
                    
                }

                if (!empty($models_list)) {
                    $contractors_table[$ctr_idx] = array_merge($contractors_table[$ctr_idx],$models_list);
                    $contractors_table_index = count ($contractors_table[$ctr_idx]);
                    $models_list = [];                    
                }
            }
            $rows_list[] = [[]];
            $rows_list[] = $contractors_header;
            $rows_list = array_merge($rows_list,$header_array);
            $rows_list = array_merge($rows_list,$contractors_table[$ctr_idx]);
             
        }

       // $rows_list = array_merge($rows_list,$header_array);
        
        
        
        
        $activeSheet->getRowDimension(1)->setRowHeight(18);
        $activeSheet->getRowDimension(2)->setRowHeight(18);
        $activeSheet->getRowDimension(3)->setRowHeight(18);
        $activeSheet->getRowDimension(4)->setRowHeight(18);
        $activeSheet->getColumnDimension('B')->setWidth(40);
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
      //  return print_ar($this->data,true);
        //$this->tpl_vars
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
              $this->tpl_vars['additional_data'] = ' ('.$this->multiplier.' '.$this->currency.')';
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
        $sResult = array();
                // Вывод содержимого таблицы   
        
        // Вывод производителей
        $all_cols= count ($this->tpl_vars['head_columns']) * $dataCount + count($this->othercolumns);
        $ctr_idx = 0;
        $ctr_count = count($this->data);
        $all_parts = '';
        $models_list = '';
        for ($ctr_idx;$ctr_idx<$ctr_count;$ctr_idx++)
        {
            if ($models_list) {
                $sResult[$ctr_idx-1]['table_data'].=$models_list;
                $models_list = '';
            }
            $elem = current($this->data[$ctr_idx]);
          // return $ctr_idx;
            $sResult[$ctr_idx]['contractor_name'] = key_exists('Contractor',$elem[0][0]) ? $elem[0][0]['Contractor'] : '';
            // По источнику данных   
            $mdl_flag = false;
            $nulls_flag = true;
            $all_nulls_flag = true;
            $models_list= '';
            foreach ($this->data[$ctr_idx] as $dtc => $ctr)
            {                
                if ($models_list) {
                    $sResult[$ctr_idx]['table_data'].=$models_list;
                    $models_list = '';
                }
                
                $sResult[$ctr_idx]['table_data'].='<tr><td class="head-element" colspan="'.$all_cols.'">'.$dtc.'</td></tr>';
                // Список моделей
                $cnt = count ($ctr);
                $rows_count = count($ctr[0]);
                for ($row_idx=0;$row_idx< $rows_count;$row_idx++) {
                    if ($models_list) {
                        if ($add_tr) {
                               $sResult[$ctr_idx]['table_data'].=$all_parts; 
                               $add_tr = false;
                        }
                            $sResult[$ctr_idx]['table_data'].=$models_list;
                            $models_list = '';                             
                    }
                    $nulls_flag = true;
                    
                  //  
                    $model = $ctr[0][$row_idx]['Model'];
                    $classifier = $ctr[0][$row_idx]['Classifier'];
                    
                    if (!$model) {
                        
                        $mdl_flag = false;                        
                        $all_nulls_flag = true;
                        if ($models_list) {
                            $sResult[$ctr_idx]['table_data'].=$models_list;
                        }
                        $models_list = '';
                        if ($classifier) {                            
                            $add_tr = true;
                            $sResult[$ctr_idx]['table_data'].='<tr><td class="first-col main-element">'.$classifier.',всего</td>';
                        }
                        else {
                            continue;
                        }
                    }
                    else {                        
                        $mdl_flag = true;
                        $models_list.='<tr><td class="first-col sub-element">'.$model.'</td>';
                        //$sResult[$ctr_idx]['table_data'].='<tr><td class="first-col sub-element">'.$model.'</td>';
                    }
                    for ($col_idx=0;$col_idx<$cnt;$col_idx++) 
                    {
                        foreach ($this->dataunits as $unit)
                        {
                            $period = $ctr[$col_idx][$row_idx]['Period'];
                            $val = $ctr[$col_idx][$row_idx][$unit];
                            if (!empty($val)) {
                                $nulls_flag = false;
                                $all_nulls_flag = false;
                            }
                            if (is_numeric($val) && !stristr($period, 'proportion'))
                            {
                                $val = round($val);
                                $value = Tools::addSpaces( $val);
                            } else {
                                $value = !stristr($period, 'proportion') ? $val : $this->percentConvert(round($val,2));
                            }                             
                            $value = $value!==null ? $value : '-';
                            if ($mdl_flag) {
                                $models_list.='<td>'.$value.'</td>';
                            }
                            else {
                                $sResult[$ctr_idx]['table_data'].='<td>'.$value.'</td>';
                            }
                        }
                        
                    }
                    if ($mdl_flag) {
                        $models_list.=  '</tr>';
                    }
                    else {
                        $sResult[$ctr_idx]['table_data'].=  '</tr>';
                    }
                    if ($add_tr) {
                        $all_parts = '<tr><td class="main-element" colspan="'.$all_cols.'">В том числе:</td></tr>';
                     //  $sResult[$ctr_idx]['table_data'].=  '<tr><td class="main-element" colspan="'.$all_cols.'">В том числе:</td></tr>';
                    }
                    if ($this->nonulls) {
                        if ($nulls_flag) {
                            $models_list='';
                        }
                    }
                }
                if ($models_list) {
                            $sResult[$ctr_idx]['table_data'].=$models_list;
                            $models_list = '';
                }
                
            }
        }
        /*
         
        for ($outerIdx = 0;$outerIdx < $this->rowscount;$outerIdx++ )
        {
            $sResult .= '<tr>';
            if ($otherColumnsCount > 0) {
                foreach ($this->othercolumns as $cName)
                {
                    $sResult.='<td>'.$this->data[0][$outerIdx][$cName].'</td>';
                }
            }
            for ($innerIdx = 0;$innerIdx < $colsCount;$innerIdx++ )
            {
                foreach ($this->dataunits as $unit)
                {
                    $val = $this->data[$innerIdx][$outerIdx][$unit];
                    if (is_numeric($val)) 
                    {
                        $value = \Rosagromash\Tools::addSpaces((int) $val);
                    } else {
                        $value = $val;
                    }                                        
                    $sResult.='<td>'.$value.'</td>';
                }
            }
            $sResult .= '</tr>';
        } */
   
        
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
      
      
      
      
    ob_start();
    $viewHelper = new ViewHelper(_REPORTS_OUT_TEMPLATES_DIR_,'models_contractor',$this->tpl_vars);
    return $viewHelper->getRenderedTemplate();
     

    }
        /**
     * Создание шапки таблицы
     */
    protected function createExcelTableHeader ()
    {
        return $header_array;
    }
        

}
