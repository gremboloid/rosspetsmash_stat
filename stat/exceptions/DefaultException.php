<?php

namespace app\stat\exceptions;

use yii\base\ExitException;
use \Yii;
use app\stat\helpers\ViewHelper;
/**
 * Стандартное исключение проекта
 *
 * @author kotov
 */
class DefaultException extends ExitException
{
    protected $userMessage = 'Внутренняя ошибка сервера';
        /**
     * Конструктор
     * @param string $name Название (выведем в качестве названия страницы)
     * @param string $message Подробное сообщение об ошибке
     * @param int $code Код ошибки
     * @param int $status Статус ответа
     * @param \Exception $previous Предыдущее исключение
     */
    public function __construct($name, $message, $code = 0, $status = 500, \Exception $previous = null){
        $view = Yii::$app->getView();        
        $response = yii::$app->getResponse();
        $vars = ViewHelper::getVarsForCommonController();        
        
        if (YII_DEBUG === false) {
            $vars['message'] = $this->userMessage;
            $logMessage = 'Произошла ошибка: '.$name;
            Yii::error($logMessage);
            $response->data = $view->renderFile('@app/views/error.twig', $vars);
        } 
        else {
            $vars['name'] = $name;
            $vars['message'] = $message;
            $vars['file'] = $this->getFile();
            $vars['code'] = $this->getCode();
            $vars['line'] = $this->getLine();
            $response->data = $view->renderFile('@app/views/error_debug.twig', $vars);
        }
        
        # Возвратим нужный статус (по-умолчанию отдадим 500-й)
        $response->setStatusCode($status);
        parent::__construct($status, $message, $code, $previous);
    }
    
}
