<?php


namespace app\stat;



/**
 * Показ различных диалоговых окон и прочих страничек (мини-фронт-контроллер)
 *
 * @author kotov
 */
class ViewHelper {
    public $twig;
    public $template_name;
    public $template_vars;

    /**
     * 
     * @param type $templateDir папка шаблона
     * @param type $templateName имя шаблона (имя файла без расширения html)
     * @param type $template_vars для подстановки в шаблон
     */
    public function __construct($templateDir,$templateName,$template_vars=array()) {
        
        $loader = new \Twig_Loader_Filesystem($templateDir);
        $this->template_vars = $template_vars;
        $this->template_name = $templateName;
        $this->twig = new \Twig_Environment($loader, array (
            'cache' => false
         ));

    }
    /**
     * Показ шаблона 
     */
    public function Show()
    {
        $this->twig->render($this->template_name.'.twig',$this->template_vars);
    }
/**
 *  возврат шаблона
 * @return string
 */
    public function getRenderedTemplate()
    {
        $res = $this->twig->render($this->template_name.'.twig',$this->template_vars);
        return $res;
    }
    
}
