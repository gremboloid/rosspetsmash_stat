<?php

namespace app\controllers;

use app\stat\model\ObjectModel;
/**
 * Description of FormManagementController
 *
 * @author kotov
 */
abstract class FormManagementController extends FrontController
{
    protected $formId = 'form-for-config';
    protected $modelClassNamespace = 'app\\stat\\model\\';
    /* модель бд для отображения */
    protected $modelName;
    protected $submitButton = true;
    protected $isAjax = false;
    protected $formData = null;
    /**
     * @var ObjectModel  Модель базы данных 
     */
    protected $model;   
    
    public function initVars() {
        parent::initVars();
        if (!is_demo()) {
            $form = $this->model->displayForm(false);
            $this->tpl_vars['template'] = $form['HTML_DATA'];
        } else {
            $this->tpl_vars['template'] = '<p>Не доступно в демо-режиме</p>';
        }
    }
}
