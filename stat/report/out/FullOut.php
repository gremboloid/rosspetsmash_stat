<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\report\out;

use app\stat\ViewHelper;
use app\stat\helpers\ExcelHelper;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Description of FullOut
 *
 * @author kotov
 */
class FullOut extends ReportCreator
{
    protected $contractors_list;  
    protected $periods_list;


    public function getExcelData(Spreadsheet $xls) 
    {
        // 
        $foot_note = '*';
        $foot_note_text = 'Тракторы с мощностью более 300 л.с., с 4 ведущими колесами равного размера, с поворотной или жесткой рамой';
        $foot_note_element = 'Полноприводные';
        $one_table_list = [
            'Тракторы',
            'Техника и оборудование для животноводства',
            'Техника и оборудование для приготовления кормов для животных',
            'Оборудование для производства молока',
            'Мелиоративная техника и оборудование'
        ];
        $first_table_exept_elements = [
          //  'Насосные станции'
        ];
        
        // Создание заголовка
        
        $activeSheet = $xls->getActiveSheet();
        $this->periods_list = $this->data['PeriodsList'];
        unset ($this->data['PeriodsList']);       
        $header_array = $this->createExcelTableHeader();
        $this->contractors_list = $this->data['Contractors'];
        unset ($this->data['Contractors']);
        $cols = count ($this->data);
        $all_cols = $cols;
        $periods_count = count ($this->data) / 3;
        
        // заголовок таблицы
        
        
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
                        'value' => l('DATA_SOURCE','report').': '. mb_convert_case($this->source,MB_CASE_LOWER)
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
                        'value' => l('REPORT_TYPE','report').': '. mb_convert_case($this->name,MB_CASE_LOWER)
                    ]
                ],
                [
                    [
                        'type' => 'text',
                        'size' => 13,
                        'style' => [
                            'font' => ['bold'],
                            'horizontal_align' => 'center'
                        ],
                        'cols' => $all_cols,
                        'value' => 'Раздел классификатора: '. mb_convert_case($this->classifierfull,MB_CASE_LOWER)
                    ]
                ],[[ 'type' => 'text','value' => '']]
            ];
        foreach ($this->dataunits as $idx => $unit) {
            $one_table = false;
            $firstTableRoot = false; 
            $sub_text = '';
            switch ($unit)
            {
                case 'DataAmount':
                    $sub_text = 'штуки';                  
                    $res_array[$idx][0] = 
                            [ 
                                [ 
                                    'type' => 'text',
                                    'cols' => $all_cols,
                                    'value' => l('UNIT','report').': ('.l('DATAAMOUNT_UNIT','words').')' 
                                ] 
                            ]; 
                break;
                case 'DataPrice':
                    $sub_text = $this->multiplier.' '.$this->currency;
                    $res_array[$idx][0] = 
                            [ 
                                [ 
                                    'type' => 'text',
                                    'cols' => $all_cols,                                    
                                    'value' => l('UNIT','report').': ('.$this->multiplier.' '.$this->currency.')' 
                                ] 
                            ];
                break;            
            }
            // Первый этап (разбивка по типу производство/отгрузка)
            $type_index = 1;
             // индекс таблицы разбиение по видам техкики
            foreach ($this->data[0] as $type => $list_of_type) {
                $ft_idx = 0;
                $st_idx = 0;                
                $first_table = array();
                $second_table = array();
                switch ($type) {
                    case 'Производство':
                      //  switch ($this->)
                        switch ($this->classifierid) {
                            case 42:
                                $type_text = 'Производство предприятий сельскохозяйственного машиностроения';
                                break;
                            case 43:
                                $type_text = 'Производство предприятий, производящих строительно-дорожную технику';
                                break;
                        }
                        break;
                    case 'Отгрузка':
                        switch ($this->classifierid) {
                            case 42:
                            $type_text = 'Отгрузка на внутренний рынок предприятиями сельскохозяйственного машиностроения';
                                break;
                            case 43:
                            $type_text = 'Отгрузка на внутренний рынок предприятиями, производящми строительно-дорожную технику';
                                break;
                        }
                        break;
                    default:
                        break;                  
                }
                $first_table[$ft_idx] = 
                      [ 
                          [
                            'type' => 'text',
                            'cols' => $all_cols, 
                            'color' => '287ba0',
                            'size' => 14,
                            'value' => $type. ' по видам техники, '.$sub_text
                          ]
                      ];
                $first_table[] = [[]];
                $first_table = array_merge($first_table,$header_array);
                $second_table[$st_idx] = 
                      [
                           [
                            'type' => 'text',
                            'cols' => $all_cols, 
                            'size' => 14,
                            'color' => '287ba0',
                            'value' => $type. ' по предприятиям, '.$sub_text
                          ]
                  
                      ];

              $ft_idx = count ($first_table);
              $res_array[$idx][][] = 
                      [
                          'type' => 'text',
                          'cols' => $all_cols, 
                          'color' => '287ba0',
                          'size' => 12,
                          'value' => $type_index++ .'. '. $type_text
                      ];
              $res_array[$idx][][] = [];
              $rootEl = $list_of_type['Root'];
              unset($list_of_type['Root']);
              
              foreach ($list_of_type as $h_text => $sub_class) {
                $one_table = false;  
                if (in_array($h_text, $one_table_list)) {
                        $one_table = true;
                }
                  $contractor = [];
                  $c_idx = 0;
                  $total1 = [];
                  $firstTableRoot = false;                  
                  $rootEl = $sub_class['Root'];
                  if ($h_text == 'Тракторы' or $h_text == 'Оборудование для производства молока') {
                //      $ft_idx = count($first_table);
                    if ($h_text == 'Тракторы') {
                        $f_text = $h_text ;
                    } else {
                        $f_text = 'Установки доильные';
                    }
                      $first_table[$ft_idx] = [ [
                          'type' => 'text',
                          'value' => $f_text,
                          'size' => 11,
                          'style' => [ 
                                    'border' => ['solid'],
                                    'font' => ['bold']
                            ] 
                      ] ];
                      $firstTableRoot = true;
                  }                 
                  $total1[] = [
                      [
                          'type' => 'text',
                          'size' => 11,
                          'style' => [ 'border' => ['solid'], 'font' => ['bold'] ],
                          'value' => 'ИТОГО:'
                      
                      ]
                    ];
                  
                $index = 0;
                for ($i=0;$i< $cols; $i++) {
                    $data = $this->data[$i][$type][$h_text]['Root'][$unit];  
                    $data = str_replace( ',', ' ',$data);
                    $data = str_replace( '.', ',',$data);
                    $data = preg_replace('/\,$/', '.',$data);
                    if (($i+1)%3 ==0) {
                        $data_el = [
                            'size' => 10,
                            'style' => [ 'horizontal_align' => 'center',
                                         'font' => ['bold']   ],
                            'type' => 'special_number',
                            'value' => $data
                            ];
                        if ($periods_count > 1 && $index != $periods_count-1) {
                            $data_el['style']['border'] = 'dotted';
                            $index++;
                        }
                        
                    } else {
                        $data_el = [
                            'size' => 10,
                            'style' => [ 'horizontal_align' => 'center', 'font' => ['bold'] ],
                            'type' => 'number',
                            'value' => $data
                        ];
                      }
                      $data_total = $data_el;
                      $data_total['size'] = 11;
                      $total1[0][] = $data_total;                      
                      if ($firstTableRoot) {
                        $first_table[$ft_idx][] = $data_el;
                      }
                      
                  }
                  if ($firstTableRoot) {
                      $ft_idx++;
                  }
                $second_table[] = [[]];
                if (count ($sub_class) != 1) {
                $second_table[] = 
                    [
                        [
                            'type' => 'text',
                            'cols' => $all_cols,
                            'style' => ['font' => ['italic'] ],
                            'size' => 14,
                            'value' => $h_text

                        ]
                    ];
                $second_table[] = [[]];
                 $second_table = array_merge($second_table,$header_array);                 
                }
                 
                
                 
                 
                  // Разбивка на подуровни
                foreach ($sub_class as $sub_head => $sub_elem) {
                   // $ft_idx = count($first_table);
                    $contractor[] = [[]];
                    $st_idx = count ($second_table);
                    $firstTableExept = false;
                    if (in_array($sub_head,$first_table_exept_elements )) {
                      $firstTableExept = true;
                    }
                    
                    $c_idx = count ($contractor);
                    if ($sub_head != 'Root') {
                        $ft_head = ( $sub_head != $foot_note_element ) ? $sub_head : $sub_head.$foot_note;
                          $contractor[$c_idx] = [[
                            'type' => 'text',
                            'size' => 12,
                            'value' => $sub_head,
                            'cols' => $all_cols,
                            'style' => [                             
                            'font' => ['italic'] ]
                          ] ];
                          $second_table[$st_idx] = [[
                                'type' => 'text',
                                'value' => $sub_head,
                                'style' => [ 
                                    'border' => ['solid'],
                                    'font' => ['bold']
                                    ] 
                              ] ];
                          if (!$firstTableRoot) {
                            if (!$firstTableExept) {
                                $first_table[$ft_idx] = [[
                                  'type' => 'text',
                                  'value' => $sub_head,
                                  'style' => [ 
                                      'border' => ['solid'],
                                      'font' => ['bold']
                                      ] 
                                ] ];
                              }
                          }
                          else {
                              $ft_idx++;
                              $first_table[$ft_idx] = [[
                                'type' => 'text',
                                'value' => $ft_head,
                                'size' => 10,
                                'style' => [ 
                                    'border' => ['solid'],
                                    ],
                                'align' => 1
                              ] ];
                          }
                          $index = 0;
                          
                          $style = $firstTableRoot ? 
                                  ['font' => 'normal','size' => 10] : 
                                  ['font' => 'bold', 'size' => 10];
                          for ($i=0;$i< $cols; $i++) {
                                $data = $this->data[$i][$type][$h_text][$sub_head]['Root'][$unit];
                                $data = str_replace( ',', ' ',$data);
                                $data = str_replace( '.', ',',$data);
                                $data = empty(trim($data)) ? 0 : $data;
                                $data = preg_replace('/\,$/', '.',$data);
                              if (($i+1)%3 ==0) {
                                if (!$firstTableExept) {   
                                    $first_table[$ft_idx][] = [
                                        'size' => $style['size'],
                                        'style' => [ 'horizontal_align' => 'center',  'font' => [ $style['font'] ] ],
                                        'type' => 'special_number',
                                        'value' => $data                               
                                    ];  
                                }
                                $second_table[$st_idx][] = [
                                    'size' => 10,
                                    'style' => [ 'horizontal_align' => 'center',  'font' => ['bold'] ],
                                    'type' => 'special_number',
                                    'value' => $data
                                ];
                                if ($periods_count > 1 && $index != $periods_count-1) {
                                    $first_table[$ft_idx][count($first_table[$ft_idx]) - 1]['style']['border'] = 'dotted';
                                    $second_table[$st_idx][count($second_table[$st_idx]) -1]['style']['border'] = 'dotted';
                                    $index++;
                                }
                                  
                              }
                              else {
                                  if (!$firstTableExept) { 
                                        $first_table[$ft_idx][] = [
                                            'size' => $style['size'],
                                            'style' => [ 'horizontal_align' => 'center', 'font' => [ $style['font'] ]  ],
                                            'type' => 'number',
                                            'value' => $data
                                        ];
                                  }
                                $second_table[$st_idx][] = [
                                    'size' => 10,
                                    'style' => [ 'horizontal_align' => 'center',  'font' => ['bold'] ],
                                    'type' => 'number',
                                    'value' => $data
                                ];
                                
                              }
                          }
                          if (!$firstTableExept) {
                            $ft_idx++;
                          }

                      
                        if (count($sub_elem) > 1) {
                            $contractor[] = [[]];
                            $contractor = array_merge($contractor,$header_array);
                            $c_idx = count($contractor);
                            $contractor[$c_idx] = [[
                            'size' => 10,
                            'type' => 'text',
                            'value' => 'Всего',
                            'style' => [                             
                                    'font' => ['bold'],
                                    'border' => ['solid']
                                ]
                          ]];
                            $index = 0;
                            for ($i=0;$i< $cols; $i++) {
                                $data = $this->data[$i][$type][$h_text][$sub_head]['Root'][$unit];
                                $data = str_replace( ',', ' ',$data);
                                $data = str_replace( '.', ',',$data);
                                $data = empty(trim($data)) ? 0 : $data;
                                $data = preg_replace('/\,$/', '.',$data);
                              //  $data = ($data == 0) ? '0,0' : $data;
                                if (($i+1)%3 ==0) {
                                    $contractor[$c_idx][] = [
                                        'size' => 10,
                                        'style' => [ 'horizontal_align' => 'center',  'font' => ['bold'] ],
                                        'type' => 'special_number',
                                        'value' => $data                               
                                        ]; 
                                    if ($periods_count > 1 && $index != $periods_count-1) {
                                        $contractor[$c_idx][count($contractor[$c_idx]) -1]['style']['border'] = 'dotted';
                                        $index++;
                                    }
                                } else {
                                    $contractor[$c_idx][] = [
                                        'size' => 10,
                                        'style' => [ 'horizontal_align' => 'center' ,  'font' => ['bold']],
                                        'type' => 'number',
                                        'value' => $data                                  
                                        ];
                                }                                 
                            }
                            $c_idx++; 
                            $st_idx++;
                            $ft_idx++;
                            
                            foreach ($sub_elem as $sub2_head => $sub2_elem) { 

                              if ($sub2_head != 'Root' ) {
                                  $leaf = false;
                                 // $conv_head = $sub2_head;
                                  $conv_head = $this->transformClassifierName($sub2_head, $sub_head);
                                  if (count($sub2_elem) == 1 && key($sub2_elem) == 'Root') { 
                                      $leaf = true;
                                      
                                  }
                                  $first_table[$ft_idx] = [[
                                      'type' => 'text',
                                      'size' => 10,
                                      'value' => $conv_head,
                                      'style' => [ 
                                          'border' => ['solid'],
                                          ],
                                       'align' => 1
                                    ] ];
                                    $second_table[$st_idx] = [[
                                      'type' => 'text',
                                       'size' => 10,
                                      'value' => $conv_head,
                                      'style' => [ 
                                          'border' => ['solid'],
                                          ],
                                       'align' => 1
                                    ] ];
                                    $contractor[$c_idx] = [[
                                      'type' => 'text',
                                        'size' => 10,
                                      'value' => $conv_head,
                                      'style' => [ 
                                          'border' => ['solid'],
                                          'font' => ['bold']
                                          ],
                                       'align' => 1
                                    ] ];
                                    $index = 0;
                                    for ($i=0;$i< $cols; $i++) {
                                        $data = $this->data[$i][$type][$h_text][$sub_head][$sub2_head]['Root'][$unit] == '' ? '–' : $this->data[$i][$type][$h_text][$sub_head][$sub2_head]['Root'][$unit];
                                        $data = str_replace( ',', ' ',$data);
                                        $data = str_replace( '.', ',',$data);
                                        $data = empty(trim($data)) ? 0 : $data;
                                        $data = preg_replace('/\,$/', '.',$data);
                                   //     $data = ($data == 0) ? '0,0' : $data;
                                        if (($i+1)%3 ==0) {
                                        $first_table[$ft_idx][] = [
                                            'size' => 10,
                                            'style' => [ 'horizontal_align' => 'center'],
                                            'type' => 'special_number',
                                            'value' => $data                               
                                            ];
                                         $second_table[$st_idx][] = [
                                             'size' => 10,
                                             'style' => [ 'horizontal_align' => 'center'],
                                            'type' => 'special_number',
                                            'value' => $data                          
                                            ]; 
                                        $contractor[$c_idx][] = [
                                            'size' => 10,
                                            'style' => [ 'horizontal_align' => 'center',  'font' => ['bold']],
                                            'type' => 'special_number',
                                            'value' => $data,
                                            ]; 
                                            if ($periods_count > 1 && $index != $periods_count-1) {
                                                $first_table[$ft_idx][count($first_table[$ft_idx]) - 1]['style']['border'] = 'dotted';
                                                $second_table[$st_idx][count($second_table[$st_idx]) -1]['style']['border'] = 'dotted';
                                                $contractor[$c_idx][count($contractor[$c_idx]) -1]['style']['border'] = 'dotted';
                                                $index++;
                                            }
                                        
                                        } else {
                                        $first_table[$ft_idx][] = [
                                            'size' => 10,
                                            'style' => [ 'horizontal_align' => 'center'],
                                            'type' => 'number',
                                            'value' => $data                                  
                                            ];
                                        $contractor[$c_idx][] = [
                                            'size' => 10,
                                            'style' => [ 'horizontal_align' => 'center',  'font' => ['bold']],
                                            'type' => 'number',
                                            'value' => $data                                  
                                            ];
                                        $second_table[$st_idx][] = [
                                            'size' => 10,
                                            'style' => [ 'horizontal_align' => 'center'],
                                            'type' => 'number',
                                            'value' => $data                                  
                                            ];
                                        }                                         
                                    }
                                    if ($leaf) {
                                        $c_list = $this->getContractorsArrayForExcel($sub2_head, $type, $idx,2);
                                        $contractor = array_merge($contractor,$c_list);                                        
                                    }
                                    if (count($sub2_elem) > 1) {

                                        foreach ($sub2_elem as $sub3_head => $sub3_elem) {

                                            if ($sub3_head != 'Root' ) {                                    
                                                if (count($sub3_elem) == 1 && key($sub3_elem) == 'Root') {
                                                    $leaf2 = true;
                                                    $conv2_head = $this->transformClassifierName($sub3_head,$sub2_head);
                                                }
                                                $ft_idx++;
                                                $st_idx++;
                                                $c_idx++;
                                                $first_table[$ft_idx] = [[
                                                    'type' => 'text',
                                                    'size' => 10,
                                                    'value' => $conv2_head,
                                                    'style' => [ 
                                                        'border' => ['solid'],
                                                    ],
                                                    'align' => 2
                                                ] ];
                                                $second_table[$st_idx] = [[
                                                    'type' => 'text',
                                                    'size' => 10,
                                                    'value' => $conv2_head,
                                                    'style' => [ 
                                                        'border' => ['solid'],
                                                    ],
                                                    'align' => 2
                                                ] ];
                                                $contractor[$c_idx] = [[
                                                  'type' => 'text',
                                                    'size' => 10,
                                                  'value' => $conv2_head,
                                                    'style' => [ 
                                                        'font' => ['bold'],
                                                        'border' => ['solid'],
                                                    ],
                                                    'align' => 2
                                                ] ];
                                        $index = 0;
                                        for ($i=0;$i< $cols; $i++) {
                                            $data = $this->data[$i][$type][$h_text][$sub_head][$sub2_head][$sub3_head]['Root'][$unit] == '' ? '–' : $this->data[$i][$type][$h_text][$sub_head][$sub2_head][$sub3_head]['Root'][$unit];
                                            $data = str_replace( ',', ' ',$data);
                                            $data = str_replace( '.', ',',$data);
                                            $data = empty(trim($data)) ? 0 : $data;
                                            $data = preg_replace('/\,$/', '.',$data);
                                           // $data = ($data == 0) ? '0,0' : $data;
                                            if (($i+1)%3 ==0) {
                                                $first_table[$ft_idx][] = [
                                                    'size' => 10,
                                                    'style' => [ 'horizontal_align' => 'center'],
                                                    'type' => 'special_number',
                                                    'value' => $data                               
                                                    ];
                                                 $second_table[$st_idx][] = [
                                                     'size' => 10,
                                                     'style' => [ 'horizontal_align' => 'center'],
                                                    'type' => 'special_number',
                                                    'value' => $data                          
                                                    ]; 
                                                $contractor[$c_idx][] = [
                                                    'size' => 10,
                                                    'style' => [ 'horizontal_align' => 'center'],
                                                    'type' => 'special_number',
                                                    'value' => $data,
                                                    ]; 
                                                if ($periods_count > 1 && $index != $periods_count-1) {
                                                    $first_table[$ft_idx][count($first_table[$ft_idx]) - 1]['style']['border'] = 'dotted';
                                                    $second_table[$st_idx][count($second_table[$st_idx]) -1]['style']['border'] = 'dotted';
                                                    $contractor[$c_idx][count($contractor[$c_idx]) -1]['style']['border'] = 'dotted';
                                                    $index++;
                                                }
                                        } else {
                                        $first_table[$ft_idx][] = [
                                            'size' => 10,
                                            'style' => [ 'horizontal_align' => 'center'],
                                            'type' => 'number',
                                            'value' => $data                                  
                                            ];
                                        $contractor[$c_idx][] = [
                                            'size' => 10,
                                            'style' => [ 'horizontal_align' => 'center'],
                                            'type' => 'number',
                                            'value' => $data                                  
                                            ];
                                        $second_table[$st_idx][] = [
                                            'size' => 10,
                                            'style' => [ 'horizontal_align' => 'center'],
                                            'type' => 'number',
                                            'value' => $data                                  
                                            ];
                                        } 
                                       
                                        
                                    }
                                     $c_list = $this->getContractorsArrayForExcel($sub3_head, $type, $idx,3);
                                        $contractor = array_merge($contractor,$c_list); 
                                        $c_idx = count($contractor);
                                                
                                                
                                            }
                                            
                                        }
                                        
                                    }
                                    $ft_idx++;
                                    $st_idx++;
                                    $c_idx = count($contractor);
                                }
                            }
                        }
                        // Для каждого подуровня таблица производителей 
                        if (count($sub_elem) == 1 && key($sub_elem) == 'Root') {
                            $contractor[] = [[]];
                            $contractor = array_merge($contractor,$header_array);
                            $c_idx = count($contractor);
                            $contractor[$c_idx] = [ [
                                'type' => 'text',
                                'value' => 'Всего',
                                'style' => [ 
                                    'border' => ['solid'],
                                    'font' => ['bold'] ]
                                ] ];
                            $index = 0;
                            for ($i=0;$i< $cols; $i++) {
                                $data = $this->data[$i][$type][$h_text][$sub_head]['Root'][$unit];
                                $data = $this->data[$i][$type][$h_text][$sub_head]['Root'][$unit] == '' ? '–' : $this->data[$i][$type][$h_text][$sub_head]['Root'][$unit];
                                 //$this->data[$i][$type][$h_text][$sub_head][$sub2_head][$sub3_head]['Root'][$unit];
                                $data = str_replace( ',', ' ',$data);
                                $data = str_replace( '.', ',',$data);
                                $data = empty(trim($data)) ? 0 : $data;
                                $data = preg_replace('/\,$/', '.',$data);
                                if (($i+1)%3 ==0) {
                                    $contractor[$c_idx][] = [
                                         'style' => [ 
                                             'horizontal_align' => 'center',
                                             'font' => ['bold']
                                             ],
                                        'type' => 'special_number',
                                        'value' => $data                               
                                        ];
                                    if ($periods_count > 1 && $index != $periods_count-1) {
                                        $contractor[$c_idx][count($contractor[$c_idx]) -1]['style']['border'] = 'dotted';
                                        $index++;
                                    }
                                } else {
                                    $contractor[$c_idx][] = [
                                            'style' => [ 
                                            'horizontal_align' => 'center',
                                            'font' => ['bold']
                                        ],
                                        'type' => 'number',
                                        'value' => $data                                  
                                        ];
                                }                               
                            }
                            $lvl = $one_table ? 1 : 2;
                            $c_list = $this->getContractorsArrayForExcel($sub_head, $type, $idx,$lvl);
                             
                            if (!$one_table) {
                                $contractor = array_merge($contractor,$c_list);
                                
                            } else {
                                $second_table = array_merge($second_table,$c_list); 
                            }

                        }
                        //$c_list = $this->getContractorsArrayForExcel($sub_head, $type, $idx,1);
                       // $second_table = array_merge($second_table,$total1);

                    }
                    if (count($sub_class) == 1) {    
                        $total1 = array();
                        $contractor[] = [[
                            'type' => 'text',
                            'size' => 14,
                            'value' => $h_text,
                            'cols' => $all_cols,
                            'style' => [                             
                            'font' => ['italic'] ]
                          ]];
                        
                        $contractor = array_merge($contractor,$header_array);
                        $c_idx = count($contractor);
                        $contractor[$c_idx] = [[
                            'type' => 'text',
                            'value' => 'Всего',
                            'style' => [ 
                                'border' => ['solid'],
                                'font' => ['bold'] ]
                                ]   
                            ];
                        $index = 0;
                        for ($i=0;$i< $cols; $i++) {
                            $data = $this->data[$i][$type][$h_text]['Root'][$unit];                      
                            if (($i+1)%3 ==0) {
                                $data_el = [
                                    'style' => [ 'font' => ['bold'],
                                         'horizontal_align' => 'center'],
                                    'type' => 'special_number',
                                    'value' => $data
                                ];
                                if ($periods_count > 1 && $index != $periods_count-1) {
                                        $data_el['style']['border'] = 'dotted';
                                        $index++;
                                }
                            } else {
                                $data_el = [
                                    'style' => [ 'font' => ['bold'],
                                    'horizontal_align' => 'center',],
                                    'type' => 'number',
                                    'value' => $data
                                ];
                            }
                        $contractor[$c_idx][] = $data_el;                        
                      
                        }

                    }                                                
                    
                }    
                
                        $c_list = $this->getContractorsArrayForExcel($h_text, $type, $idx,1);
                        $contractor = array_merge($contractor,$c_list);
                        if ($one_table && ($h_text != 'Оборудование для производства молока')) {
                            $contractor = [];
                        }
                        $second_table = array_merge($second_table,$total1,$contractor);
               // $total1 = array();
            }
            if ($this->classifierfullid == 42) {
              $first_table[] = [
                  [
                        'type' => 'text',
                        'size' => 8,
                        'style' => [
                            'font' => ['italic'],
                            'horizontal_align' => 'center'
                        ],
                        'cols' => $all_cols,
                        'value' => $foot_note.' - '.$foot_note_text
                    ]
                  
              ];
            }
              $first_table[] = [[ ]]; // добавление пустой строки
              $second_table[] = [[ ]];
              $res_array[$idx] = array_merge($res_array[$idx],$first_table,$second_table);
              
            }
            
        }
        
        foreach ($res_array as $res_elem) {
            $rows_list = array_merge($rows_list, $res_elem);
        }
        
        
        // Формирование таблицы
        
        
        // Предварительная настройка листа        
        $activeSheet->getColumnDimension('B')->setWidth(40);
        $activeSheet->getStyle('B')->getAlignment()->setWrapText(true);
       // $activeSheet->getStyle('B')->getAlignment()->setIndent(5);
        $commonStyles = [ 
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
         ];

        
        for ($i = 0;$i<$all_cols;$i++) {
            $activeSheet->getColumnDimensionByColumn(2 + $i)->setWidth(15);
        }
        
        $xlsHelper = new ExcelHelper($activeSheet);
        
        $xlsHelper->formatExcelSheetFromArray($rows_list,$commonStyles);
        
    }

    public function getWebTableData() 
    {
        
                // 
        $foot_note = '*';
        $foot_note_text = 'Тракторы с мощностью более 300 л.с., с 4 ведущими колесами равного размера, с поворотной или жесткой рамой';
        $foot_note_element = 'Полноприводные';
        $one_table_list = [
            'Тракторы',
            'Техника и оборудование для животноводства',
            'Техника и оборудование для приготовления кормов для животных',
            'Оборудование для производства молока',            
            'Мелиоративная техника и оборудование'
        ];
        $first_table_exept_elements = [
          //  'Насосные станции'
        ];
      
        $dataCount = count($this->dataunits);
        $sResult = '';
      
        if (!$this->ready) {
            return $sResult;
        }
        // Создание заголовка  
        $this->periods_list = $this->data['PeriodsList'];     
        unset ($this->data['PeriodsList']);
        $header_text = $this->createTableHeader();
        $this->contractors_list = $this->data['Contractors'];
        unset ($this->data['Contractors']);
        // количество столбцов
        $cols = count ($this->data);   
    
        foreach ($this->dataunits as $idx => $unit) {
            $one_table = false;
            $firstTableRoot = false; 
            $sub_text = '';
            switch ($unit)
            {
                case 'DataAmount':
                    $sub_text = 'штуки';                  
                    $this->tpl_vars['result'][$idx]['additional_data'] = ' ('.l('DATAAMOUNT_UNIT','words').')';
                break;
                case 'DataPrice':
                    $sub_text = $this->multiplier.' '.$this->currency;
                    $this->tpl_vars['result'][$idx]['additional_data'] = ' ('.$this->multiplier.' '.$this->currency.')';
                break;            
            }                      
            $sResult = '';          
            // Вывод содержимого таблицы  
            // Отчет строится по одной ед. измерения

          // Первый этап (разбивка по типу производство/отгрузка)
            $type_index = 1;

            foreach ($this->data[0] as $type => $list_of_type) {
                switch ($type) {
                  case 'Производство':
                      //  switch ($this->)
                        switch ($this->classifierfullid) {
                            case 42:
                                $type_text = 'Производство предприятий сельскохозяйственного машиностроения';
                                break;
                            case 43:
                                $type_text = 'Производство предприятий, производящих строительно-дорожную технику';
                                break;
                        }
                        break;
                    case 'Отгрузка':
                        switch ($this->classifierfullid) {
                            case 42:
                            $type_text = 'Отгрузка на внутренний рынок предприятиями сельскохозяйственного машиностроения';
                                break;
                            case 43:
                            $type_text = 'Отгрузка на внутренний рынок предприятиями, производящми строительно-дорожную технику';
                                break;
                        }
                        break;
                    default:
                        break;                          
                }
                $firstTable = '';
                $firstTable ='<h3 class="full-rep-subhead2">'.$type.' по видам техники, <span>'.$sub_text.'</span></h3>';
                $firstTable.='<table class="full_report"><thead>';

                $firstTable.=$header_text;
                $sResult.='<h3 class="full-rep-subhead2">'.$type.' по предприятиям, <span>'.$sub_text.'</span></h3>';


              // Второй этап разбивка по разделам классификатора
              // Выбираем подытог
            $rootEl = $list_of_type['Root'];
            unset($list_of_type['Root']); 
              
            foreach ($list_of_type as $h_text => $sub_class) {
                $one_table = false;
                $firstTableRoot = false;
                if (in_array($h_text, $one_table_list)) {
                    $one_table = true;
                }
                $rTable = '';
                $contractor = '';                
                $rootEl = $sub_class['Root']; 
                if ($h_text == 'Тракторы' or $h_text == 'Оборудование для производства молока') {
                    if ($h_text == 'Тракторы') {
                        $f_text = $h_text ;
                    } else {
                        $f_text = 'Установки доильные';
                    }
                    $firstTable.='<tr class="sub_element"><td class="total"><div class="elem-level1">'.$f_text.'</div></td>';
                    $firstTableRoot = true;                      
                }
                $total1 = '<tr><td class="total"><div class="elem-level1">ИТОГО: </div></td>';
                for ($i=0;$i< $cols; $i++) {
                    $data = $this->data[$i][$type][$h_text]['Root'][$unit];
                    $data = str_replace( ',', ' ',$data);
                    $data = str_replace( '.', ',',$data);
                    $data = preg_replace('/\,$/', '.',$data);
                    if (($i+1)%3 ==0) {
                        if ($data === '0') {
                            $data = '0,0';
                        }
                    }
                   // $data = ($data == 0) ? '0,0' : $data;
                    $total1.='<td style="white-space: nowrap;text-align:center"'.((($i+1)%3 ==0 && $i != $cols-1)  ? ' class="delimiter"' :'').'>'.$data.'</td>' ;
                    if ($firstTableRoot) {
                        $firstTable.='<td style="white-space: nowrap;text-align:center"'.((($i+1)%3 ==0 && $i != $cols-1)  ? ' class="delimiter"' :'').'>'.$data.'</td>' ;
                    }
                }
                $total1.= '</tr>';
                if ($firstTableRoot) {
                    $firstTable.='</tr>';
                }                
                $rTable.='<h2 class="root_element">'.$h_text.'</h2>';                 
                $rTable.='<table class="full_report"><thead>';
                $rTable.=$header_text;                  
                $rTable.='</thead>';
                if (count($sub_class) == 1 && key($sub_class) == 'Root') {
                     /*   $rTable.='<td class="total"><div class="elem-level1">Всего: </div></td>';
                        $rTable.= $this->showContractors($h_text, $type,$idx);
                        $rTable.= '</table>';*/
                    $contractor.='<h2 class="sub_root_element">'.$h_text.'</h2>';    
                    $contractor.='<table class="full_report"><thead>';
                    $contractor.=$header_text;
                    $contractor.= $this->showContractors($h_text, $type, $idx);
                    $contractor.='</table>';
                    $sResult.=$contractor;
                    continue;
                }                    
                // Разбивка на подуровни
                foreach ($sub_class as $sub_head => $sub_elem) {
                    
                    $firstTableExept = false;
                    if (in_array($sub_head,$first_table_exept_elements )) {
                      $firstTableExept = true;
                    }
                    if ($sub_head != 'Root') {
                        $ft_head = ( $sub_head != $foot_note_element ) ? $sub_head : $sub_head.$foot_note;
                        if (!$one_table) {
                            $contractor.='<h2 class="sub_root_element">'.$sub_head.'</h2>';                        
                        }
                          // вывод таблицы производителей							
                        $rTable.='<tr class="sub_element"><td class="total"><div class="elem-level1">'.$sub_head.'</div></td>';
                        if (!$firstTableRoot) {
                            if (!$firstTableExept) {
                                $firstTable.='<tr class="sub_element"><td class="total"><div class="elem-level1">'.$sub_head.'</div></td>';
                            }
                        }
                        else {
                              $firstTable.='<tr><td><div class="elem-level2">'.$ft_head.'</div></td>';
                        }
                        for ($i=0;$i< $cols; $i++) {
                            $data = $this->data[$i][$type][$h_text][$sub_head]['Root'][$unit];
                            $data = str_replace( ',', ' ',$data);
                            $data = str_replace( '.', ',',$data);
                            $data = preg_replace('/\,$/', '.',$data);
                           if (($i+1)%3 ==0) {
                                if ($data === '0') {
                                    $data = '0,0';
                                }
                            }
                       //     $data = ($data == 0) ? '0,0' : $data;
                            $rTable.='<td style="white-space: nowrap;text-align:center"'.((($i+1)%3 ==0 && $i != $cols-1)  ? ' class="delimiter"' :'').'>'.$data.'</td>' ;
                            if (!$firstTableExept) {
                                $firstTable.='<td style="white-space: nowrap;text-align:center"'.((($i+1)%3 ==0 && $i != $cols-1)  ? ' class="delimiter"' :'').'>'.$data.'</td>' ;
                            }
                        }
                        $rTable.= '</tr>';
                        $firstTable.= '</tr>';
                        if (count($sub_elem) > 1) {
                          // подуровни для производителей
                            $contractor.='<table class="full_report"><thead>';                
                            $contractor.=$header_text;
                            $contractor.='</thead>';
                            $contractor.='<td class="total"><div class="elem-level1">Всего </div></td>';
                            for ($i=0;$i< $cols; $i++) {
                                $data = $this->data[$i][$type][$h_text][$sub_head]['Root'][$unit];
                                $data = str_replace( ',', ' ',$data);
                                $data = str_replace( '.', ',',$data);
                                $data = preg_replace('/\,$/', '.',$data);
                            //    $data = ($data == 0) ? '0,0' : $data;
                                $contractor.='<td style="white-space: nowrap;text-align:center"'.((($i+1)%3 ==0 && $i != $cols-1)  ? ' class="delimiter"' :'').'>'.$data.'</td>' ;
                            }	

                            foreach ($sub_elem as $sub2_head => $sub2_elem) {
                                if ($sub2_head != 'Root' ) {
                                      //начальная инициализация
                                    $leaf = false;
                                    //$conv_head = $sub2_head;
                                    $conv_head = $this->transformClassifierName($sub2_head,$sub_head);
                                    if (count($sub2_elem) == 1 && key($sub2_elem) == 'Root') { 
                                        $leaf = true;
                                        $conv_head = $this->transformClassifierName($sub2_head,$sub_head);
                                    }
                                    $rTable.='<tr><td><div class="elem-level2">'.$conv_head.'</div></td>' ;
                                    $firstTable.='<tr><td><div class="elem-level2">'.$conv_head.'</div></td>' ;
                                    $contractor.='<tr><td class="total"><div class="elem-level1">'.$conv_head.'</div></td>' ;
                                    for ($i=0;$i< $cols; $i++) {
                                        $data = $this->data[$i][$type][$h_text][$sub_head][$sub2_head]['Root'][$unit] == '' ? '–' : $this->data[$i][$type][$h_text][$sub_head][$sub2_head]['Root'][$unit];
                                        $data = str_replace( ',', ' ',$data);
                                        $data = str_replace( '.', ',',$data);
                                        $data = preg_replace('/\,$/', '.',$data);
                                        if (($i+1)%3 ==0) {
                                           if ($data === '0') {
                                                $data = '0,0';
                                            }
                                        }
                                 //       $data = ($data == 0) ? '0,0' : $data;
                                        $contractor.='<td style="white-space: nowrap;text-align:center"'.((($i+1)%3 ==0 && $i != $cols-1)  ? ' class="delimiter"' :'').'>'.$data.'</td>' ;
                                        $rTable.='<td style="white-space: nowrap;text-align:center"'.((($i+1)%3 ==0 && $i != $cols-1)  ? ' class="delimiter"' :'').'>'.$data.'</td>' ;
                                        $firstTable.='<td style="white-space: nowrap;text-align:center"'.((($i+1)%3 ==0 && $i != $cols-1)  ? ' class="delimiter"' :'').'>'.$data.'</td>' ;
                                    }
                                    $rTable.='</tr>';
                                    $contractor.='</tr>';
                                    if ($leaf) {
                                        $contractor.= $this->showContractors($sub2_head, $type, $idx);						
                                    }
                                    if (count($sub2_elem) > 1) {

                                        foreach ($sub2_elem as $sub3_head => $sub3_elem) {
                                            if ($sub3_head != 'Root' ) {
                                                $conv2_head = $this->transformClassifierName($sub3_head,$sub2_head);
                                                //$conv2_head = $this->transformClassifierName($sub3_head,$sub2_head);
                                                    if (count($sub3_elem) == 1 && key($sub3_elem) == 'Root') {
                                                        $leaf2 = true;
                                                        $conv2_head = $this->transformClassifierName($sub3_head,$sub2_head);
                                                    }
                                                  $rTable.='<tr><td><div class="elem-level3">'.$conv2_head.'</div></td>' ;
                                                  $firstTable.='<tr><td><div class="elem-level3">'.$conv2_head.'</div></td>' ;
                                                  $contractor.='<tr><td class="total"><div class="elem-level2">'.$conv2_head.'</div></td>' ;
                                                  for ($i=0;$i< $cols; $i++) {
                                                      $data = $this->data[$i][$type][$h_text][$sub_head][$sub2_head][$sub3_head]['Root'][$unit] == '' ? '–' : $this->data[$i][$type][$h_text][$sub_head][$sub2_head][$sub3_head]['Root'][$unit];
                                                      $data = str_replace( ',', ' ',$data);
                                                      $data = str_replace( '.', ',',$data);
                                                      $data = preg_replace('/\,$/', '.',$data);
                                                    if (($i+1)%3 ==0) {
                                                       if ($data === '0') {
                                                            $data = '0,0';
                                                        }       
                                                    }
                                             //         $data = ($data == 0) ? '0,0' : $data;
                                                      $contractor.='<td style="white-space: nowrap;text-align:center"'.((($i+1)%3 ==0 && $i != $cols-1)  ? ' class="delimiter"' :'').'>'.$data.'</td>' ;
                                                      $rTable.='<td style="white-space: nowrap;text-align:center"'.((($i+1)%3 ==0 && $i != $cols-1)  ? ' class="delimiter"' :'').'>'.$data.'</td>' ;
                                                      $firstTable.='<td style="white-space: nowrap;text-align:center"'.((($i+1)%3 ==0 && $i != $cols-1)  ? ' class="delimiter"' :'').'>'.$data.'</td>' ;
                                                  }
                                                  $rTable.='</tr>';
                                                  $contractor.='</tr>';
                                                  if (count($sub3_elem) == 1 && key($sub3_elem) == 'Root') {
                                                      $contractor.= $this->showContractors($sub3_head, $type,$idx,3);
                                                  }                                    
                                              }
                                          }
                                      }
                                  }
                              }
                              $contractor.='</table>';
                          }
                      // Для каждого подуровня таблица производителей 
                        if (!$one_table) {
                            if (count($sub_elem) == 1 && key($sub_elem) == 'Root') {
                                $contractor.='<table class="full_report"><thead>';                
                                $contractor.=$header_text;
                                $contractor.='</thead>';
                                $contractor.='<td class="total">Всего </td>';
                                for ($i=0;$i< $cols; $i++) {
                                    $data = $this->data[$i][$type][$h_text][$sub_head]['Root'][$unit];
                                    $data = str_replace( ',', ' ',$data);
                                    $data = str_replace( '.', ',',$data);
                                    $data = preg_replace('/\,$/', '.',$data);
                                   if (($i+1)%3 ==0) {
                                        if ($data === '0') {
                                            $data = '0,0';
                                        }
                                    }
                                 //   $data = ($data == 0) ? '0,0' : $data;
                                    $contractor.='<td style="white-space: nowrap;text-align:center"'.((($i+1)%3 ==0 && $i != $cols-1)  ? ' class="delimiter"' :'').'>'.$data.'</td>' ;
                                }
                                $contractor.= $this->showContractors($sub_head, $type, $idx);
                                $contractor.='</table>';
                            }
                        }
                        else {
                            $rTable.= $this->showContractors($sub_head, $type, $idx);
                        }
                      }
                  }                
                  $sResult.=$rTable.$total1;
                  $rTable= '';
                  $total1='';
                  $sResult.='</table>';
                  
                  $sResult.=$contractor;
                  
              }

              $firstTable.='</table>';
              if ($this->classifierfullid == 42) {
                  $firstTable.='<p>'.$foot_note . ' - '.$foot_note_text.'</p>';
              }
              $sResult= '<h2 class="full-rep-subhead">'. ($type_index++) .'. '.$type_text.'</h2>'.$firstTable .$sResult;
              $finalRes.= $sResult;
              $sResult = '';
          }              

        $this->tpl_vars['result'][$idx]['data_units'] = l($unit,'words');       
        $this->tpl_vars['result'][$idx]['table_data'] = $finalRes;
      }
        $this->tpl_vars['data_source'] = l('DATA_SOURCE','report');
        $this->tpl_vars['data_source_val'] = mb_convert_case($this->source,MB_CASE_LOWER);
        $this->tpl_vars['report_type'] = l('REPORT_TYPE','report');
        $this->tpl_vars['report_type_name'] = $this->name;
        $this->tpl_vars['classifier'] = $this->classifierfull;
        $this->tpl_vars['data_count'] = count($this->dataunits);
        $this->tpl_vars['units'] = l('UNIT','report');
        $this->tpl_vars['colspan'] = $this->tpl_vars['subheader'] ? ' colspan='.$dataCount : '';
      
      
      
    $viewHelper = new ViewHelper(_REPORTS_OUT_TEMPLATES_DIR_,'full',$this->tpl_vars);
    return $viewHelper->getRenderedTemplate();

    }
    /**
     * 
     * @param type $key
     * @return type
     */
    protected function showContractors($key,$type,$idx,$level=2,$excel = false) 
    {
        
        $sResult = '';        
        $data_count = count ($this->data);
        $first_col = $this->contractors_list[0][$type];
        if (array_key_exists($key, $first_col)) {            
            foreach ($first_col[$key] as $pos => $value) {
                $nullsFlag = true;
                $string ='';
                $string.='<tr><td><div class="elem-level'.$level.'">'.$value['Contractor'].'</div></td>';
                for ($i=0 ; $i< $data_count; $i++) {
                    $data = $this->contractors_list[$i][$type][$key][$pos][$this->dataunits[$idx]];
                    $data = str_replace( ',', ' ',$data);
                    $data = str_replace( '.', ',',$data);  
                    $data = preg_replace('/\,$/', '.',$data);
                    $val = trim ($data);
                    if ($val) {
                        $nullsFlag = false;
                    }
                    $val = ($val !== '') ? $val : '–';
                    if (!$excel && (($i+1)%3 == 0) ) {
                        if ($val === '0') {
                            $val = '0,0';
                        }
                    }
                    /*if ($val) {                        
                        $nullsFlag = false;
                    }
                    else { 
                        $val = $val === 0 ? $val : '–';
                    }*/
                    $string .= '<td style="white-space: nowrap;text-align:center"'.((($i+1)%3 ==0 && $i != $data_count-1)  ? ' class="delimiter"' :'').'>'.$val.'</td>';
                }
                $string.='</tr>';
                if ($this->nonulls) {
                    if (!$nullsFlag) {
                        $sResult.=$string;
                    }
                } else {
                    $sResult.=$string;
                }
            }
            
        }
        return $sResult;

    }
    
    /**
     * Преобразует название классификатора (отбрасывает полное имя, оставляя окончание без заглавной буквы)
     * @param type $classifierName
     * @return type
     */
    protected function transformClassifierName($classifierName,$parentName='')
    {
        if ($classifierName == 'Опрыскиватели-разбрасыватели самоходные') {
            return 'Самоходные опрыскиватели-разбрасыватели';
        }
        if (!empty($parentName) && strpos($classifierName,$parentName) === 0) {
            return rs_mb_ucfirst(trim(substr($classifierName,strlen($parentName)+1)));
        
        }
        else {
            return $classifierName;
        }
        
    }
    
    /**
     * Создание шапки таблицы
     */
    protected function createTableHeader ()
    {
        $header_text='<tr><td></td>';
        
        if (key_exists('Periods', $this->data)) {
            $header_text2 = '<tr><td></td>';
            $dp_count = count($this->data['Periods']);
            $idx = 1;
            foreach ($this->data['Periods'] as $pair) {
                $header_text.='<td class="head-element'.($idx == $dp_count ? '' : ' delimiter').'" colspan="3">'.$pair['Month'].'</td>' ;
              //  $header_text.='</tr><tr><td></td>';
                $header_text2.='<td class="head-element">'.$pair['Years'][0].'</td>' ;
                $header_text2.='<td class="head-element">'.$pair['Years'][1].'</td>' ;
                $header_text2.='<td class="head-element'.($idx == $dp_count ? '' : ' delimiter').'">Изм., %</td>' ;
                $idx++;
            }
            $header_text2.= '</tr>';
            $header_text.='</tr>'.$header_text2;
            /*
            $idx=1;
            $interval_idx = 1;
            $interval = $this->realperiodscount / 2; 
            for ($i=0;$i < $interval; $i++) {
                $header_text.='<td class="head-element'.(($i <  ($interval -1) && $interval !=1) ? ' delimiter' : '').'" colspan="3">'.$this->data['Period']['Months'].'</td>';
            }            
            $header_text.='</tr><tr><td></td>';
            $first = true;
            foreach ($this->data['Period']['Years'] as $year) {
                $header_text.='<td class="head-element">'.$year.'</td>';
                if ($idx == 2) {
                    $header_text.='<td class="head-element'.( $interval_idx < ($interval) ? ' delimiter' : '' ).'">Изм %</td>';
                    $idx = 1;
                    $interval_idx++;
                    $first = false;
                }
                else {
                    $idx++;
                }
                
            }
            
            $header_text.='</tr>';*/
            unset ($this->data['Periods']);
        }
        else {
            $cnt = count ($this->periods_list);
            $i = 1;
            foreach ($this->periods_list as $period) {
                $header_text.='<td class="head-element min'.( trim( $period ) == 'Изм., %' && $i != $cnt ? ' delimiter' : '').'">'.$period.'</td>';
                $i++;
            }
            
        }
        return $header_text;
        
    }
    /**
     * Создание шапки таблицы для Excel
     */
    protected function createExcelTableHeader () 
    {
        // индекс разделителя
        $index = 0;
         // заголовок
        if (key_exists('Periods', $this->data)) {
            $header_array = [
                [
                    [
                        'type' => 'text',
                        'value' => '',
                        'rows' => 1,
                        'style' => [ 'border' => ['solid'] ]
                        
                    ]                    
                ]               
            ];
            $dp_count = count($this->data['Periods']);
            $h_idx = 1;
            $y_idx = 1;
            
            $header_array[1][0] = [  'type' => 'text','value' => ''];
            foreach ($this->data['Periods'] as $pair) {
                $header_array[0][$y_idx] = [
                     'type' => 'text',
                     'cols' => 2,
                     'value' => $pair['Month'],
                     'style' => [
                            'font' => ['bold'],
                            'horizontal_align' => 'center'
                        ],
                ];
                $header_array[1][$h_idx++] = 
                       [
                        'type' => 'text',                     
                        'value' => $pair['Years'][0],
                        'style' => [
                            'font' => ['bold'],
                            'horizontal_align' => 'center'
                            ]                     
                        ];
                $header_array[1][$h_idx++] = 
                        [
                        'type' => 'text',                     
                        'value' => $pair['Years'][1],
                        'style' => [
                            'font' => ['bold'],
                            'horizontal_align' => 'center'
                            ]                     
                        ];
                $header_array[1][$h_idx] = 
                        [
                        'type' => 'text',                     
                        'value' => 'Изм., %',
                        'style' => [
                            'font' => ['bold'],
                            'horizontal_align' => 'center'
                            ]
                        ];                    
                if ($dp_count > 1 && $index != $dp_count-1) {
                        $header_array[1][$h_idx]['style']['border'] = 'dotted';
                        $header_array[0][$y_idx]['style']['border'] = 'dotted';
                }
                $index++;
                $h_idx++;
                $y_idx +=3;
             }
             unset ($this->data['Periods']);
            
        } else {
            $cnt = count ($this->periods_list);
            $i = 1;
            $header_array[0][0] = [
                        'type' => 'text',
                        'value' => '',
                        'style' => [ 'border' => ['solid'] ]                        
                    ];
            $index = 0;
            foreach ($this->periods_list as $period) {
                
                $header_array[0][] = [
                     'type' => 'text',
                     'value' => $period,
                     'style' => [
                            'font' => ['bold'],
                            'horizontal_align' => 'center'
                        ],
                ];
                if ($index < $cnt-1 && (($index + 1) % 3) === 0) {
                            $header_array[0][count($header_array[0]) - 1]['style']['border'] = 'dotted';
                            
                        }
                $index++;
            }
            
        }
         return $header_array;         
    }
    function getContractorsArrayForExcel($key,$type,$idx,$level=0) {
        $aResult = array();    
        $data_count = count ($this->data);
        $periods_count = count ($this->data) / 3;
        $first_col = $this->contractors_list[0][$type];
        if (array_key_exists($key, $first_col)) {            
            foreach ($first_col[$key] as $pos => $value) {
                $nullsFlag = true;
                $row = array();
                $row[0][0] = [
                        'type' => 'text',
                        'size' => 10,
                        'align' => $level,
                        'value' => $value['Contractor'],
                        'style' => [ 'border' => ['solid'] ] 
                ];
                $index = 0;
                for ($i=0 ; $i< $data_count; $i++) { 
                     $data = $this->contractors_list[$i][$type][$key][$pos][$this->dataunits[$idx]];
                    $data = str_replace( ',', ' ',$data);
                    $data = str_replace( '.', ',',$data);
                    $data = preg_replace('/\,$/', '.',$data);

                     $val = trim ($data);
                     if ($val) {
                        $nullsFlag = false;
                    }
                    else { 
                        $val = $val === 0 ? $val : '–';
                    }
                    if (($i+1)%3 ==0) {
                        $row[0][] = [
                            'size' => 10,
                            'style' => [ 'horizontal_align' => 'center'],
                            'type' => 'special_number',
                            'value' => $val
                        ];
                        if ($periods_count > 1 && $index != $periods_count-1) {
                            $row[0][count($row[0]) - 1]['style']['border'] = 'dotted';
                            $index++;
                        }
                        
                      } else {
                         $row[0][] = [
                             'size' => 10,
                            'style' => [ 'horizontal_align' => 'center'],
                            'type' => 'number',
                            'value' => $val
                        ];
                    }                                        
                 }
                 if ($this->nonulls) {
                     if (!$nullsFlag) {
                         $aResult = array_merge($aResult,$row);
                     }
                 } else {
                     $aResult = array_merge($aResult,$row);
                 }
                 
            }
            
        }
        return $aResult;
    }
    
    protected  function enterDataField ($data)  {
        $periods_count = count ($this->data) / 3;
        $index = 0;
        $elem = array();
        for ($i=0;$i< $cols; $i++) {                             
            if (($i+1)%3 ==0) {
                $data_el = [
                    'style' => [ 'horizontal_align' => 'center'],
                    'type' => 'special_number',
                    'value' => $data[$i]
                ];            
            if ($periods_count > 1 && $index != $periods_count-1) {
                $data_el['style']['border'] = 'dotted';
                $index++;
                }
            } else {
                $data_el = [
                    'style' => [ 'horizontal_align' => 'center'],
                    'type' => 'number',
                    'value' => $data[$i]
                ];
            } 
            $elem[] = $data_el;
        }
        return $elem;
    }
}
