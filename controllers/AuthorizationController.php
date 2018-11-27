<?php

namespace app\controllers;

use app\models\forms\UserLoginForm;
use \app\models\user\LoginUser;
use \Yii;

/**
 * Description of AuthorizationController
 *
 * @author kotov
 */
class AuthorizationController extends FrontController
{    
    public function actionIndex() {
        if (Yii::$app->request->isPost) {
            return $this->actionLoginPost();
        }
        return $this->render('authorization.twig', $this->tpl_vars);
    }
    
    protected function actionLoginPost() {
        $userLoginForm = new UserLoginForm();
        if ($userLoginForm->load(Yii::$app->request->post())) {
            if ($userLoginForm->validate()) {
                $userLoginForm->login();
                return $this->redirect('/');
            }
        } 
        $this->tpl_vars['userLoginForm'] = $userLoginForm;
        return $this->render('authorization.twig', $this->tpl_vars);
    }
    
    protected function initVars() {
        parent::initVars();
        $this->tpl_vars['title'] = $this->tpl_vars['title'] .' - '. l('TITLE_AUTH_PAGE'); 
        $vars = [
            'auth_page_head',
            'auth_page_subhead',
            'auth_page_email',
            'password',
            'forgot_password',
            'enter',
            'contact_information',
            'restore_password_message',
            'auth_page_email',
            'send_email',
        ];
        foreach ($vars as $key) {
            $this->tpl_vars[$key] = l($key);
        }
        $loginForm = new UserLoginForm();
        $this->tpl_vars['userLoginForm'] = $loginForm;
    }
}
