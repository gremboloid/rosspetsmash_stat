<?php

namespace app\stat\admin;

use app\stat\Module;

class AdminModules extends AdminRoot {
    
    
    	/** @var array */
    private $map = array(
            'install' => 'install',
            'uninstall' => 'uninstall',
            'configure' => 'getContent',
            'delete' => 'delete'
	);
    /**
     *
     * @var array список установленных модулей
     */
    private $modulesArray;
  //  public $xml_modules_list = 'modules_list.xml';
    
    
    /** @var string файл со списком доступных модулей */
  //  private $_moduleCacheFile = '/config/modules_list.xml';
    
    public function __construct()
    {
        $this->link = 'modules';
        $this->template_file = 'admin_modules';
        $this->name = l('MODULES_SETTINGS','admin');
        $this->parentElement = 'Index';              
        parent::__construct();
    }
    protected function prepare() {
        parent::prepare();
        $this->modulesArray = Module::getModulesList();
        
    }

    public function getTestData() {
     //   return print_ar($this->modulesArray,true);
        //parent::getTestData();t
    }
    protected function setTemplateVars() {
        parent::setTemplateVars();
        $this->tpl_vars['installed_modules'] = $this->modulesArray['installed'];
        $this->tpl_vars['not_installed_modules'] = $this->modulesArray['not_installed'];
    }
    
}
