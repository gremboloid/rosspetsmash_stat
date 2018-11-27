<?php

$currentDir = dirname(__FILE__);
define('__BASE_URI__','' );
define('_SITE_ROOT_URL_','/');
define('_FILES_URL_',_SITE_ROOT_URL_ . 'files/');
define('_IMG_DIR_','/img/');
define('_ROOT_DIR_',realpath($currentDir.'/..').'/');
define('_MODULES_DIR_',realpath($currentDir.'/..').'/modules/');
define('_MODULES_URI_', __BASE_URI__.'modules/');
define('_CACHE_DIR_', _ROOT_DIR_.'cache/');
define('_REPORTS_TEMPLATES_DIR_', _ROOT_DIR_.'views/reports/');
define('_FORMS_TEMPLATES_DIR_', _ROOT_DIR_.'views/forms/');
define('_MODAL_TEMPLATES_DIR_', _ROOT_DIR_.'views/modal/');
define('_REPORTS_OUT_TEMPLATES_DIR_', _ROOT_DIR_.'views/reports/table/');
define('_MODAL_TEMPLATES_DIR_', _ROOT_DIR_.'views/modal/');
define('_ADMIN_TEMPLATES_DIR_', _ROOT_DIR_.'views/admin/');
define('_INFORM_TEMPLATES_DIR_', _ROOT_DIR_.'views/inform/');
define('_DEFAULT_LANG_' , 'ru');

// статус валидации формы
define('FORM_VALID_OK',1); // валидация пройдена
define('FORM_VALID_ERROR',2); // ошибка валидации
define('FORM_SAVE_ERROR',3); // ошибка сохранения формы
define('FORM_VALID_UNDEFINED',null); // статус не определен
// статус информационного блока
define('INFO_BLOCK_OK', 1); // статус ОК
define('INFO_BLOCK_NOT_EXIST', 2); // информационный блок не существует
// статус сохранениея объекта в БД
define('OBJECT_MODEL_SAVED',1); // успешно
define('OBJECT_MODEL_SAVE_ERROR',2); // ошибка записи
define('OBJECT_MODEL_NOT_CHANGED',3); // данные не были изменены
// статус формы модели
define('FORM_OK', 1); // статус ОК
define('FORM_NOT_EXIST', 2); // форма не существует
define('FORM_EXIST', 4); // форма существует
//статус проверки email
define('EMAIL_NOT_EXIST',2);
define('EMAIL_NOT_VALIDATE',2);
define('EMAIL_STATUS_VALID',1);
// дополнительные константы
define('DEFAULT_NAMESPACE_FOR_MODELS','app\\stat\\model\\');

