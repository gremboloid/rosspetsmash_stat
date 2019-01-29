<?php


namespace app\stat\admin;


use app\stat\ViewHelper;

/**
 * Description of AdminForm
 *
 * @author kotov
 */
abstract class AdminForm extends AdminRoot
{
    /** @var string */
    protected $_head_text;
    protected $form_name = 'form-for-config';
    protected $_model_name = 'config';
    /** @var string javaScript метод вызывающийся при обработки кнопки submit */
    protected $submit_button_method = 'saveConfig';
    /* @var string Отображать кнопку submit на форме по умолчанию  */
    protected $submit_button = true;
    protected $_is_ajax = false;
    protected $_form_data = null;


    public function __construct() {
        parent::__construct();        
    }
    public function prepare()
    {
        $this->formConfigure();
        parent::prepare();
    }

    /**
     * Конфигурирование формы ввода
     */   
    protected function formConfigure() {  
        $btns = l('BTN_ACTIONS');
        $this->form_elements['main_form']['form_id'] = $this->form_name;
        $this->form_elements['submit_button'] = true;
        $this->form_elements['submit_button_method'] = $this->submit_button_method;
        $this->form_elements['submit_button_text'] = $btns['save'];
        $this->form_elements['main_form']['block_head_text'] = $this->name;
        $this->form_elements['model_name'] = $this->_model_name;
          //  $this->form_template['form_type'] = 'modal';
       // }
       // $this->form_elements['main_form']['form_id'] = $this->_form_id;
			
        
    }
    protected function getFormData($ajax=true) {
        $this->_is_ajax = $ajax;
        $helper = new ViewHelper(_MODAL_TEMPLATES_DIR_,'object_form',$this->form_elements);
        if ($ajax) {
            $form_data['form_html'] = $helper->getRenderedTemplate();
            $this->form_data = json_encode($form_data);         
        } else {
            $this->_form_data = $helper->getRenderedTemplate();
        }
    } 
    public function display() {
        $this->getFormData(false);
        return $this->_form_data;
    }
    
    public function displayAjax() {
        $this->getFormData(true);
        return $this->_form_data;
    }
}
