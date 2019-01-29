<?php

namespace app\controllers;

use yii\web\Controller;
use Yii;
use app\models\user\LoginUser;
use app\stat\Module;
use app\stat\helpers\ControllerViewHelper;
use app\stat\Configuration;
use app\stat\model\Contractor;
use app\stat\Sessions;
use app\stat\Application;

/**
 * Шлавный контпроллер портала Росспецмаш стат
 *
 * @author kotov
 */
class FrontController extends Controller
{
    /**
     * Для удобного доступа к залогиненному пользователю
     * @var LoginUser|null
     */
    protected $user;
    /**
     * Производитель относящийся к текущему пользователю
     * @var Contractor
     */
    protected $contractor;
    /*
     * @var array текст для сообщений сервера
     */
    public $messages = array();
    /** @var array переменные для подстановка в шаблон */
    public $tpl_vars = array();
    /** @var string имя контролллера */
    public $controllerName;
    
    public function init() {
        parent::init();
        $this->messages = array(
           'ok'  => l('BUTTON_OK','messages'),
           'cancel'  => l('BUTTON_CANCEL','messages'),
           'save_q' => l('ATTENTION_SAVE','messages'),
           'attention' => l('ATTENTION','messages'),
           'save_model' => l('WRITE_TO_DB','messages'),
           'no_changes' => l('NO_CHANGE','massages'),
           'write_error'  => l('WRITE_ERROR','messages'),
           'unknown_server_error'  => l('UNKNOWN_SERVER_ERROR','messages'),
           'all_countries_select' => l('INPUT_FORM_EXPORT_ALL')
         );  
        $this->enableCsrfValidation = false;
        if (!Yii::$app->user->isGuest) {
            Sessions::sessionInit();
            $this->user = Yii::$app->user->getIdentity();          
            $this->contractor = new Contractor($this->user->getContractorId());
          //  $resArray['contractor_name'] = $contractor->name;
        }
        Configuration::loadConfiguration();
        $this->viewPath = '@app/views/';
    }
    public function beforeAction($action) {
        if (!Yii::$app->request->isAjax) {
            $this->setParams();
            $this->initVars();
        }
        return parent::beforeAction($action);
        
    }

    /**
    * Предварительная установка различных параметров
    */
    public function setParams() {
        Module::load();
    }
    
    /**
     * Инициализация переменных для представления
     */
    protected function initVars() {
        if ($this->contractor) {
            $contractorName = $this->contractor->name;
        } else {
            $contractorName = '';
        }
        $this->tpl_vars['controller'] = $this->controllerName;
       $this->tpl_vars = ControllerViewHelper::getVarsForCommonController($this->controllerName, $contractorName);
       $this->tpl_vars['messages'] = $this->messages;
       $this->tpl_vars['messagesJSON'] = json_encode($this->messages);
               // обработка флеш сообщений
        if ($modal_flash = Sessions::getVarSession('FLASH_SAVE_MODAL')) {
            $modal_flash = json_decode($modal_flash);
            $flash_msg = [];
            if (key_exists('status', $modal_flash)) {                
                switch ($modal_flash->status) {
                    case 1: 
                        $flash_msg['className'] = 'success';
                        break;
                    case 2: 
                        $flash_msg['className'] = 'message-error';
                }                                
            }
            if (key_exists('message', $modal_flash)) {
                $flash_msg['message'] = $modal_flash->message;
            } else {
                $flash_msg['message'] = l('MESSAGE_NOT_AVAILABLE','messages');
            }
            $this->tpl_vars['modal_flash'] = true;
            $this->tpl_vars['modal_flash_message'] = $flash_msg;
            Sessions::unsetVarSession('FLASH_SAVE_MODAL');
        }
        $this->tpl_vars['css_files'] = Application::$css_files;
        $this->tpl_vars['js_files'] = Application::$js_files;
       
    }
    
    
}
