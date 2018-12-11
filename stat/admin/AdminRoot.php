<?php

namespace app\stat\admin;


use app\stat\ViewHelper;

abstract class  AdminRoot {
    
    protected $parentElement;
    protected $name;
    protected $link;
    protected $breadcrumbs = array();    
    protected $tpl_vars = array();
    /** @var array список модальных окон */
    public $modals_list = array();
    protected $left_block_vars = array();
    protected $template_file = "admin";
    /** присутствует левый сайдбар */
    protected $left_block = true;
    protected $left_block_template = "admin_left_block";
    /** @var string имя ассоциированной с разделом таблицей, если имеется */
    public $table;  
    


    public function __construct() {        
        
        $this->setBreadcrumbs();
        $this->prepare();
        $this->setLeftBlockVars();
        $this->setTemplateVars();
        
     //   
    }
    protected function setBreadcrumbs() {
        $this->breadcrumbs[] = ['name' => $this->name,'link' => $this->link ];
        $cur_class = $this;
        while ($cur_class->parentElement) {
            $cur_class_name = '\Admin\Admin' .  $cur_class->parentElement;
            if (class_exists($cur_class_name)) {
                $cur_class = new $cur_class_name();
                $this->breadcrumbs[] = ['name' => $cur_class->name,'link' => $cur_class->link ];
            } else {
                break;
            }
        }
        $this->breadcrumbs = array_reverse($this->breadcrumbs);
    }
    protected function prepare() {
        
    //   
        
    }
    public function getName() {
        return $this->name;
    }
    public function getBreadcrumbs() {
        return $this->breadcrumbs;
    }

    public function getParentName() {
        if ($this->parentElement == null) {
            return '';
        }
        $pClass = 'Admin'.$this->parentElement;
        if (!class_exists($pClass)) {
            return '';
        }
        $cl = new $pClass();
        return $cl->getName();        
    }
    
    protected function setTemplateVars() {        
        $this->tpl_vars['element_link'] = $this->link;
        $this->tpl_vars['modals_list'] = $this->modals_list;
    }
    protected function setLeftBlockVars() {
        $this->left_block_vars['chpu'] = true;
        // Инициализация меню быстрого перехода        
        $this->left_block_vars['blocks_list'][0] = [
            'block_alias' => 'fast_link',
            'block_name' => l('FAST_LINK', 'admin'),
            'type' => 'ref_list',
            'refs' => [
                ['name' => 'Классификатор', 'link' => 'classifier'],
                ['name' => 'Реестр производителей', 'link' => 'contractors'],
                ['name' => 'Реестр пользователей', 'link' => 'users'],
                ['name' => 'Реестр моделей', 'link' => 'models'],
            ]
        ];
    }
    
    public function display() {
        //$this->_setLeftBlockVars();
        $view_helper = new ViewHelper(_ADMIN_TEMPLATES_DIR_, $this->template_file,$this->tpl_vars);
        return $view_helper->getRenderedTemplate();
        
    } 
    public function displayLeftBlock() {
        if ($this->left_block == false ) {
            return;
        }       
        $view_helper = new ViewHelper(_ADMIN_TEMPLATES_DIR_, $this->left_block_template,$this->left_block_vars);
        return $view_helper->getRenderedTemplate();
        
    }
    public function isLeftBlockExist() {
        return $this->left_block;
    }
    public function setTpllVar($name,$value) {
        $this->tpl_vars[$name] = $value;
    }
    public function setLeftBlockVar($name,$value) {
        $this->left_block_vars[$name] = $value;
    }

    public function getTestData() {
        return ;
    }
    
    
    
}
