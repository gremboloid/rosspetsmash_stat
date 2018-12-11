<?php

namespace app\stat\helpers;

use app\stat\Convert;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
/**
 * Description of ExcelHelper
 *
 * @author User
 */
class ExcelHelper 
{
    /**
     * @var Worksheet
     */
    protected $activeSheet;
    
    public function __construct(Worksheet $workSheet) 
    {
        $this->activeSheet = $workSheet;
    }
    
    public function formatExcelSheetFromArray(array $rowsList, $commonStyles = null) 
    {
        $rowsCount = count ($rowsList);
        for ($row = 1;$row < $rowsCount;$row++) {
            foreach ($rowsList[$row] as $idx => $cell) {
                $colC = 0;
                $rowC = 0; 
                if (isset ($cell['value'])) {
                    $val = $cell['value'];
                } else {
                    $val = '';
                }
                if (isset($cell['wrap'])) {
                    $this->activeSheet->getStyleByColumnAndRow($idx + 1, $row)
                            ->getAlignment()
                            ->setWrapText(true);                    
                }
                if (isset ($cell['cols']) || isset ($cell['rows'])) {
                    $colC = isset($cell['cols']) ? $cell['cols'] : 0;
                    $rowC = isset($cell['rows']) ? $cell['rows'] : 0;
                    $this->activeSheet->mergeCellsByColumnAndRow($idx + 1,$row,$idx + 1 + $colC,$row + $rowC);
                }
                if (key_exists('style', $cell)) {
                    $style = $cell['style'];
                    $styleArr = array();
                    if (key_exists('border', $style)) {
                        switch ($style['border']) {
                            case 'dotted':
                                $borderType = Border::BORDER_MEDIUMDASHED;
                                break;
                            case 'thin':
                                $borderType = Border::BORDER_THIN;
                                break;
                            case 'thik':
                            default :
                                $borderType = Border::BORDER_MEDIUM;
                            break;
                        }
                        if (!key_exists('border_position', $style)) {// по умолчанию справа
                            $styleArr['borders'] = 
                            [
                                'right' => [
                                    'borderStyle' => $borderType,
                                    'size' => 1
                                ]
                            ];
                        } else {
                            switch ($style['border_position']) {
                                case 'all':
                                    $styleArr['borders'] = [
                                        'allBorders' => [
                                        'borderStyle' => $borderType,
                                        ]
                                    ];
                                    break;
                            }
                        }                        
                    }
                    if (key_exists('font', $style)) {
                        foreach ($style['font'] as $font) {
                            $styleArr['font'][$font] = true;
                        }
                    }
                    if (key_exists('horizontal_align', $style)) {
                        switch ($style['horizontal_align']) {
                            case 'center':
                                $styleArr['alignment']['horizontal'] = Alignment::HORIZONTAL_CENTER;
                                break;
                        }
                    }
                    $this->activeSheet->getStyleByColumnAndRow($idx + 1, $row, $idx + 1 + $colC, $row + $rowC)
                            ->applyFromArray($styleArr);                                      
                }
                if (key_exists('size',$cell)) {
                    $this->activeSheet->getStyleByColumnAndRow($idx + 1, $row)
                            ->getFont()
                            ->setSize($cell['size']);
                }
                if (key_exists('color', $cell)) {
                     $this->activeSheet->getStyleByColumnAndRow($idx + 1, $row, $idx + 1 + $colC, $row + $rowC)
                             ->getFont()
                             ->getColor()
                             ->setRGB($cell['color']);
                }
                if (key_exists('bgcolor', $cell)) {
                    $this->activeSheet->getStyleByColumnAndRow($idx + 1, $row, $idx + 1 + $colC, $row + $rowC)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID);
                    $this->activeSheet->getStyleByColumnAndRow($idx + 1, $row, $idx + 1 + $colC, $row + $rowC)
                            ->getFill()
                            ->getStartColor()->setRGB($cell['bgcolor']);
                }
                if (key_exists('align', $cell)) {
                   $this->activeSheet->getStyleByColumnAndRow($idx + 1, $row, $idx + 1 + $colC, $row + $rowC)
                           ->getAlignment()->setIndent($cell['align']);
                }
                if (key_exists('type', $cell)) {
                    if ($cell['type'] == 'number') {
                        $val = Convert::getNumbers($val); 
                        $val = !$val ? 0 : $val;
                        $this->activeSheet->getStyleByColumnAndRow($idx + 1,$row)
                                ->getNumberFormat()
                                ->setFormatCode('#,##0');
                    }
                    if ($cell['type'] == 'text' || $cell['type'] == 'space_text') {
                        $this->activeSheet->getStyleByColumnAndRow($idx + 1, $row)
                                ->getNumberFormat()
                                ->setFormatCode(NumberFormat::FORMAT_TEXT);
                        $val = $cell['type'] == 'space_text' ? ' '.trim($val) : trim($val);
                       // $val = trim($val);
                    }
                    if ($cell['type'] == 'special_number') {
                        //$val = \Rosagromash\Convert::getNumbers($val);                            
                        $this->activeSheet->getStyleByColumnAndRow($idx + 1,$row)
                                ->getNumberFormat()
                                ->setFormatCode('# ##0.0');                     
                    }
                }
                $this->activeSheet->setCellValueByColumnAndRow( $idx + 1, $row, $val);
                $this->activeSheet->getStyleByColumnAndRow($idx + 1, $row, $idx + 1 + $colC, $row + $rowC)
                        ->applyFromArray($commonStyles);                       
            }
        }
    }
}
