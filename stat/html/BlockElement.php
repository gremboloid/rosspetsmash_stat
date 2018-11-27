<?php

namespace Html;

/**
 * Определяет блочные элементы (могут содержать в себе другие элементы).
 *
 * @author kotov
 */
class BlockElement extends HtmlElement
{
    protected $childElements;
    /**
     * Конструктор любого блочного элемента
     * @param type $type тип элемента
     * @param array $params массив аттрибутов
     */
    public function __construct($type,array $params = array()) 
    {
        parent::__construct($type,$params);
        $this->childElements = array();
    }

    public function getHtml() 
    {
        $sResult ='';
        $this->getOpenTagString();
        if (empty($this->elementType)) {
            return '';
        }
        $sResult.= $this->openTagString.'>';
      //  if (!$this->singleTag) {
            if (count($this->childElements) != 0) {
                foreach ($this->childElements as $element)
                {
                    if (is_string($element)) {
                        $sResult .= $element;
                    }
                    elseif (is_subclass_of($element, get_root_class($this))) {
                        $sResult .= $element->getHtml();
                    }
                }
            }
        
        $sResult.= $this->text . '</'.$this->elementType.'>';
     //   }
        
        return $sResult;
    }
    /**
     * Добавление дочернего элемента внутрь блочного элемента
     * @param type $elem длемент или массив элементов
     */
    public function addChildElement($elem)
    {
        if (is_array($elem))
        {
            foreach ($elem as $val)
            {
                $this->childElements[] = $val;
                
            }
        }
        else {
           $this->childElements[] = $elem;
        }
    }
}
