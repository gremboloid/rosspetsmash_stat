<?php

namespace app\stat;


use app\stat\db\QuerySelectBuilder;
use app\stat\helpers\FileHelper;
use app\stat\lang\Language;


abstract class Module 
{    
    /** @var integer идентификатор модуля */
    public $id = null;    
    /** @var string уникальное имя */
    public static $name;
    /** @var string отображаемое имя */
    public static $displayName = null;
    /** @var string Небольшое описание модуля */
    public static $description;
    /** @var string автор модуля */
    public static  $author;
    /** @var boolean статус */
    public $active = false;
    /** @var boolean виджет */
    public $is_widget = false;
    /** @var boolean где размещать */
    public $widget_position = ['page' => 'index', 'column' => 1];
    public static $def_lang;
    /** @var array переменные для подстановки в шаблон представления */
    public static $template_vars = array();
    
    protected static $_init;
    
    
    protected static $table = 'TBLMODULE';
    /** @var array кэш установленных модулей */
    protected static $modulesCache;
    protected static $modulesCacheFile = 'modules.dat';    
    protected static $_LANGUAGE_CACHE = array();
    public static $modulesList = null;
    protected $_path = null;
    protected $_templatePath = null;
    
    public function __construct() {
        if (static::$name == null) {
            static::$name = $this->id;
        } 
        if (static::$name != null) {
            if (self::$modulesCache === null) {
                self::loadModulesList();         
            }
            if (isset(self::$modulesCache[static::$name])) {
                $this->active = true;
                $this->id = self::$modulesCache[static::$name]['Id'];
                foreach (self::$modulesCache[static::$name] as $key => $value) {
                    if (property_exists($this, $key)) {
                        $this->{$key} = $value;
                    }
                }
                $this->_path = _MODULES_DIR_ . static::$name .'/';
                $this->_templatePath = _MODULES_DIR_ . static::$name .'/templates/';
            }
        }
    }
    public function init() {
        $this->initStylesAndScripts();
        $this->configure();
        if ($this->is_widget) {
            if (key_exists('page', $this->widget_position)) {
                static::$template_vars[$this->widget_position['page']][$this->widget_position['column']][] = $this->renderTemplate();
            }
        }
        static::$_init = true;

    }
    
    protected function initStylesAndScripts() { }
    protected function configure() { }

    public static function loadModulesList() {
        if (!empty(self::$modulesCache)) {
            return;
        }
        self::$modulesCache = array();
        $db = getDb();
        $queryBuilder = new QuerySelectBuilder();
        $queryBuilder->select = '*';
        $queryBuilder->from = self::$table;            
        $result = $db->getRows($queryBuilder);
        foreach ($result as $result_row) {
            self::$modulesCache[$result_row['Name']] = $result_row;
        }                             
    }
    public static function cleanModulesCache () {
        self::$modulesCache = array();
    }

    /**
    * Добавить модуль в БД
    */
    public function install() {
        if (!Validate::isModuleName(static::$name)) {
            return false;
        }
        $result = getDb()->getRow('Id', static::$table,[['param' => 'Name' ,'staticText' => static::$name]]);
        if ($result) {
            return false;
        }
        $result = getDb()->insert(static::$table, array(
            ['field' => 'Name','value' => static::$name, 'type' => 'varchar2'],
            ['field' => 'Active','value' => 1, 'type' => 'number']
        ));
        if (!$result) {
            return false;
        }
        static::deleteCacheFile();
        $this->id = $result;
        
        return $result;                
    }
      /**
    * Добавить модуль в БД
    */
    public function uninstall() {
       // return 'oк';
        if (!Validate::isModuleName(static::$name)) {
            return false;
        }
        $result = getDb()->getRow('Id', static::$table,[['param' => 'Name' ,'staticText' => static::$name]]);
        if (!$result) {
            return false;
        }
        $result = getDb()->delete(static::$table, [[ 'param' => 'Id' , 'staticNumber' => $result['Id']]]);
        if (!$result) {
            return false;
        }
        static::deleteCacheFile();        
        return true;                
    }
    
    public function disableModule() {
        if (!Validate::isModuleName(static::$name)) {
            return false;
        }
        $result = getDb()->getRow('Id', static::$table,[['param' => 'Name' ,'staticText' => static::$name]]);
        if (!$result) {
            return false;
        }
        $up_data = [
            'field' => 'Active',
            'value' => 1
        ];
        $where = [['param' => 'Id', 'staticNumber' => $result['Id']] ];
        $res = getDb()->update(static::$table, $up_data, $where);
        if ($res == 1) {
            
        }
    }


    
    /**
     * Получить перевод текста модуля
     */
    public static function l($string,$id_lang = null) { 
        $string = strtoupper($string);
        if ($id_lang == null) {
            $lang = Configuration::get('lang');
            $iso = $lang ? $lang : _DEFAULT_LANG_;            
        } else {
            $iso = Language::getIsoById($id_lang);
        }
        if (empty (self::$_LANGUAGE_CACHE)) {
            if (file_exists(_MODULES_DIR_.static::$name.'/lang/'.$iso.'.php')) {
                include_once(_MODULES_DIR_.static::$name.'/lang/'.$iso.'.php');
            } else {
                if (file_exists(_MODULES_DIR_.static::$name.'/lang/'.self::$def_lang.'.php')) {
                     include_once(_MODULES_DIR_.static::$name.'/lang/'.self::$def_lang.'.php');
                }
            }
            self::$_LANGUAGE_CACHE = $_MODULE;            
        }
        if (key_exists($string, self::$_LANGUAGE_CACHE)) {
            return self::$_LANGUAGE_CACHE[$string];
        }
        return $string;
    }
    /**
     * Получить список установленных модулей
     * @return array
     */
    public static function getModulesInstalled()
    {
        return Db::getInstance()->querySelect('SELECT * FROM "TBLMODULE" m');
    }
    /**
     * Получить все модули в системе
     * @return array
     */
    public static function getModulesList() {
        if (!is_null(self::$modulesList)) {
            return self::$modulesList;
        } 
        if (self::$modulesCache === null) {
            self::loadModulesList();         
        }
        
        $cache_file = new FileHelper(_CACHE_DIR_.self::$modulesCacheFile);
        if ($cache_file->isFileExist()) {
            self::$modulesList = unserialize($cache_file->getFileData());
            return self::$modulesList;
        }
        $aResult = array(
            'installed' => [],
            'not_installed' => []
        );
        $flist = scandir(_MODULES_DIR_);
        $mdl_idx = 0;
        $mdl_inst_idx = 0;
        foreach ($flist as $fname) {
            if ($fname ==='.' or $fname === '..') {
                continue;
            }
            $full_path = _MODULES_DIR_.$fname;
            $full_path = is_dir($full_path) ? $full_path.'/'.$fname.'.php' : $full_path.'.php';            
            if (file_exists($full_path)) {
                include_once $full_path;
                $class_full_name = 'app\\modules\\'.str_replace('_','',ucwords($fname,'_'));
                if (class_exists($class_full_name) && is_subclass_of($class_full_name, 'app\\stat\\Module')) {
                    $mdl_params = [
                      'className'  => $class_full_name,
                      'moduleName' => $fname ,
                      'path' => $full_path,                      
                      'displayName' => call_user_func([$class_full_name,'getDisplayName'])
                    ];
                    if (key_exists($fname, self::$modulesCache)) {
                        $mdl_params['active'] = (self::$modulesCache[$fname]['Active'] != 0 ) ? true : false;
                       $aResult['installed'][$mdl_inst_idx++] = $mdl_params;                       
                    } else {
                       $aResult['not_installed'][$mdl_idx++] = $mdl_params; 
                    }
                }                                                
            }

                   // $aResult[] = str_replace('_','',ucwords($fname,'_'));           
        }
        self::$modulesList = $aResult;
        $cache_file->putOrReplaceData(serialize($aResult));
        return $aResult;
    }
    public static function getDisplayName() {
        
        if (is_null(self::$displayName)) {
            return self::l('MODULE_NAME');
        }
        else {
            return self::$displayName;
        }
    }
    
    public static function isInstall($mdl_name) {        
        self::loadModulesList();
        return (key_exists($mdl_name, self::$modulesCache));
        
    }
    /**
     * Установка указанного модуля
     * @param type $mdl_name имя модуля
     * @return strins JSON-строка, статус (0 - ОК, 1 - уже был установлен, 
     * 2 - указанный модуль не существует, 3 - ошибка установки )
     */
    public static function installByName ($mdl_name) {
        
        if ( self::isInstall($mdl_name)) {
            return json_encode(['status' => 1]);  
        };
        $all_modules = self::getModulesList();
        $not_installed_modules = $all_modules['not_installed'];
        if (empty($not_installed_modules)) {
            return json_encode(['status' => 2]);
        }
        foreach ($not_installed_modules as $module) {
            if ($mdl_name == $module['moduleName']) {
                include_once $module['path'];
                $className = $module['className'];
                $mdl_class = new $className();
                if ( $mdl_class->install()) {
                    return json_encode(['status' => 0,
                                        'text' => 'Модуль '.$mdl_name.' успешно установлен']);
                } else {
                    return json_encode(['status' => 3,
                                        'text' => 'Ошибка установки модуля '.$mdl_name]);
                }
            }
        }
      //  return json_encode(self::getModulesList()); 
        return json_encode(['status' => 2]);         
    }
     /**
     * Установка указанного модуля
     * @param type $mdl_name имя модуля
     * @return strins JSON-строка, статус (0 - ОК, 1 - не установлен, 
     * 2 - указанный модуль не существует, 3 - ошибка удаления )
     */
    public static function uninstallByName ($mdl_name) {
        
        if ( !self::isInstall($mdl_name)) {
            return json_encode(['status' => 1]);  
        };
        $all_modules = self::getModulesList();
        $installed_modules = $all_modules['installed'];
        if (empty($installed_modules)) {
            return json_encode(['status' => 2]);
        }
        foreach ($installed_modules as $module) {
            if ($mdl_name == $module['moduleName']) {
                include_once $module['path'];
                $className = $module['className'];
                $mdl_class = new $className();
                if ($mdl_class->uninstall()) {
                    return json_encode(['status' => 0,
                                        'text' => 'Модуль '.$mdl_name.' успешно удален']);
                }
            }
        }
      //  return json_encode(self::getModulesList()); 
        return json_encode(['status' => 2]);         
    }
    public static function deleteCacheFile() {
        $cache_file = new FileHelper(_CACHE_DIR_.self::$modulesCacheFile);
        $cache_file->deleteFile();
        
    }
    /**
     * Проверяет загружены ли модули
     * @return boolean
     */
    public static function isInit() {
        return static::$_init;
    }
    /**
     * Получить данные иодуля для представления
     * @return string данные
     */
    public function renderTemplate() {
        $view_helper = new ViewHelper($this->_templatePath,static::$name, static::$template_vars);
        return $view_helper->getRenderedTemplate();
     //  
    }
    /**
     * Запуск модулей
     */
    public static function load() {
        static::loadModulesList();
        $modules = static::getModulesList();
        $installed_modules = $modules['installed'];
        foreach ($installed_modules as $module) {
            include_once $module['path'];
            $mdl = $module['className'];
            $mdlClass = new $mdl();
            $mdlClass->init();
            
            
        }
    }
    
}
