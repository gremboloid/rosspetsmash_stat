<?php

namespace app\stat\helpers;

use \Yii;
use app\stat\model\Contractor;

/**
 * Вспомрогательный класс для подготовки данных к отображению в видах
 *
 * @author kotov
 */
class ViewHelper
{
    /**
     * 
     * @param string $controllerName
     * @return array
     */
    public static function getVarsForCommonController( $controllerName = 'index',$contractorName = '') {
        $resArray = [
           'words' => [
                'search' => l('SEARCH','words'),
                'edit' => l('EDIT','words'),
                'other' => l('OTHER','words'),
                'from' => l('FROM','words'),
                'to' => l('TO', 'words'),
                'months' => l('MONTHS','words')
            ],
           'title' => l('TITLE')
        ];
       $vars = array(
           'profile_settings',
           'header_user_profile',
           'header_company_profile',
           'administration',
           'user_block_exit'
       );
        foreach ($vars as $key) {
            $resArray[$key] = l($key);
        }
       if (!Yii::$app->user->isGuest) {           
            $user = Yii::$app->user->getIdentity();
            $resArray['contractor_name'] = $contractorName;
            $main_menu = array ( ['name' => l('MAIN_PAGE','menu'),'link' => 'home','controller' => 'index'] );
            if (!is_analytic()) {
                $main_menu[] =  ['name' => l('INPUT_FORMS','menu'),'link' => 'forms', 'controller' => 'forms'] ;
            }
            $main_menu[] = ['name' => l('REPORT_CONSTRUCTOR','menu'),'link' => 'reports','controller' => 'reports'] ;
            $main_menu[] = ['name' => l('INPFORMATION_PAGE','menu'),'link' => 'information', 'controller' => 'information'];
            $resArray['fio'] = $user->getFIO();
            $resArray['user_role'] = $user->roleId;
            $resArray['controller'] = $controllerName;
        } else {
           $main_menu = array();
        }
        $resArray['main_menu'] = array_map( function($val) use ($controllerName) {
           if ($val['controller'] == $controllerName) {
               $val['current'] = 1;               
           } 
           return $val;
        }, $main_menu);        
       $resArray['base_uri'] = Yii::$app->request->baseUrl;
       $resArray['reports_uri'] = Yii::$app->request->baseUrl . '/reports';
       $resArray['forms_uri'] = Yii::$app->request->baseUrl . '/forms';
       $resArray['admin'] = is_admin();
       $resArray['analytic'] = is_analytic();
       
       return $resArray;
    }
    
}
