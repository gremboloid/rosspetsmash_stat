<?php


namespace app\stat\html;

/**
 * Определяет строчные элементы
 *
 * @author kotov
 */
class LineElement extends HtmlElement
{
    //put your code here
    protected $singleTag;    
    /**Конструктор
     * Конструктор любого строчного элемента
     * @param type $type тип элемента
     * @param array $params массив аттрибутов
     */
    function __construct($type,array $params = array()) {

        parent::__construct($type,$params);                
    }
        public function getHtml() 
    {

        $sResult ='';
        $this->getOpenTagString();
        if (empty($this->elementType)) {
            return '';
        }
        $sResult.= $this->openTagString.'>';
        if (!$this->singleTag) {
            
        $sResult.= $this->text.'</'.$this->elementType.'>';
       }    
        return $sResult;
    }

}
