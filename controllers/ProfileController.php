<?php

namespace app\controllers;

use app\stat\model\Contractor;
use app\stat\model\User;
use Yii;

/**
 * Description of ProfileController
 *
 * @author kotov
 */
class ProfileController extends FormManagementController
{
    public $controller_name = 'profile';
    
    protected $formId = 'form-for-config';

    public function actionUser() 
    {
        $this->setVarsForActionUser();
        return $this->displayPage();
    }
    public function actionCompany()
    {
        $this->setVarsForActionCompany();
        return $this->displayPage();
    }
    protected function displayPage() {        
         return $this->render('profiles.twig', $this->tpl_vars);
    }
    protected function setVarsForActionUser() {
        
        $this->tpl_vars['title'] = $this->tpl_vars['title'] . ' - ' . l('HEADER_USER_PROFILE');
        $this->tpl_vars['head_text'] = l('HEADER_USER_PROFILE');    
        
    }
    protected function setVarsForActionCompany() {
        $this->tpl_vars['title'] = $this->tpl_vars['title'] . ' - ' . l('HEADER_COMPANY_PROFILE');
        $this->tpl_vars['head_text'] = l('HEADER_COMPANY_PROFILE');
    }   
    
    public function setParams() {
        parent::setParams();
        $action = $this->action->id;       
        $this->tpl_vars['template'] = $action;
         if ($action == 'user') {
             $this->model = new User(Yii::$app->user->getIdentity()->getId());
         }
         if ($action == 'company') {
             $this->model = $this->contractor;
         }
        
    }
}
