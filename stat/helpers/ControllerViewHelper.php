<?php

namespace app\stat\helpers;

use \Yii;
use app\stat\services\ModelRequestService;
use yii\helpers\Url;

/**
 * Вспомрогательный класс для подготовки данных к отображению в видах
 *
 * @author kotov
 */
class ControllerViewHelper
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
       $resArray['root'] = is_root();
       $resArray['analytic'] = is_analytic();
       if (is_root()) {
           $requestService = new ModelRequestService();
           $resArray['models_request_count'] = $requestService->getUnprcessedRequestsCount();
           $resArray['unprocessed_requests'] = l('UNPROCESSED_REQUESTS_PRESENTS','messages');
           $resArray['url'] = Url::base(true) . '/admin/requests';
       }
       return $resArray;
    }
    
}
