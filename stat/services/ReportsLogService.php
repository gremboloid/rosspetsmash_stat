<?php

namespace app\stat\services;

/**
 * Description of ReportsLogService
 *
 * @author kotov
 */
class ReportsLogService 
{
    public static function getDetailLogInfo($request_str) {
        parse_str($request_str,$params);
        $dateS = $params['date1'];
        $dateE = $params['date2'];
        $contractor_id = (int) $params['contractor'];
        $pattern = '/^\d{1,2}\.\d{1,2}\.\d{4}$/';
        if (preg_match($pattern, $dateS) && preg_match($pattern, $dateE) && preg_match('/^\d*$/', $contractor_id)) {
            if (empty($contractor_id)) {
                $result = getDb()->querySelect('SELECT td."Name",tl."ReportType", COUNT(td."Name") "Count" FROM TBLREPORTSLOG tl, TBLDATASOURCE td
                    WHERE td."Id"=tl."DataSourceTypeId"
                    AND tl."Date" BETWEEN TO_DATE(\'00:00:00/'.$dateS.'\',\'HH24:Mi:SS/DD.MM.YYYY\') AND TO_DATE(\'23:59:59/'.$dateE.'\',\'HH24:Mi:SS/DD.MM.YYYY\')
                    GROUP BY tl."ReportType", td."Name" ORDER BY td."Name",tl."ReportType"');
            } else {
                $result = getDb()->querySelect('SELECT td."Name",tl."ReportType", COUNT(td."Name") "Count" FROM TBLREPORTSLOG tl, TBLCONTRACTOR tc,TBLUSER tu, TBLDATASOURCE td
                    WHERE tu."Id" = tl."UserId" AND tu."ContractorId"=tc."Id" AND td."Id"=tl."DataSourceTypeId"
                    AND tl."Date" BETWEEN TO_DATE(\'00:00:00/'.$dateS.'\',\'HH24:Mi:SS/DD.MM.YYYY\') AND TO_DATE(\'23:59:59/'.$dateE.'\',\'HH24:Mi:SS/DD.MM.YYYY\')
                    AND tc."Id"= ' .$contractor_id. ' GROUP BY tl."ReportType", td."Name" ORDER BY td."Name",tl."ReportType"');
            }
        $sResult =  '<table class="inner_tab"><tr class="inner_tab_h1"><th>Источник данных</th><th>Параметры отчета</th><th>Количество</th></tr>';
        foreach ($result as $str) {
        $sResult.= '<tr><td style="padding-left:4%">'.$str['Name'].'</td><td style="padding-left:3%">'.$str['ReportType'].'</td><td style="text-align:center">'.$str['Count'].'</td></tr>';
        }
        $sResult.= '</table>';  
        } else {
            $sResult = "Неверные параметры запроса";
        }
        return $sResult;
                
        
    }        
}
