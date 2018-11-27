<?php

namespace app\stat\html;

/**
 * Базовый класс HTML-элементов
 *
 * @author kotov
 */
abstract class HtmlElement {
    protected $id;
    protected $className;
    protected $elementType;
    protected $openTagString;
    protected $attributes;
    protected $text;

    /**
     * Конструктор HTML-эдемента по умолчанию
     * @param string $type тип элемента
     * @param array $params массив аттрибутов
     */
    public function __construct($type,array $params=[])
    {
        if (!is_string($type)) {
            return null;
        }
        $this->elementType = $type;
        $this->id = '';
        $this->className = array();
        $this->attributes = array();
        
        if (key_exists('id', $params)) {
            if (is_string($params['id'])) {
                $this->id = $params['id'];
            }
        }        
        if (key_exists('class', $params)) {
            if (is_string($params['class'])) {
                $this->className[] = $params['class'];
            }
            elseif  (is_array($params['class'])) {
                $this->className = $params['class'];
            }
        }
        if (key_exists('attr', $params)) {
            if  (is_array($params['attr'])) {
                $this->attributes = $params['attr'];
            }
        }
        $this->text = '';
        if (key_exists('text', $params)) {
            if (is_string($params['text'])) {
                $this->text = $params['text'];
            }
        }                        
    }
    public function addId($id)
    {
        if (is_string($id)) {
                $this->id = $id;
        }
    }
    public function addClass($class)
    {
        if (is_string($class)) {
            $this->className[] = $class;
        }
        if (is_array($class)) {
            $this->className = array_merge($this->className, $class);
        }
    }
    public function removeClass($class)
    {
        while (($key = array_search($class, $this->className)) !== FALSE)
        {
            unset($this->className[$key]);
        }
    }
/*
 * Получить HTML содержимое элемента
 */
    abstract public function getHtml() ;

    /**
     * Добавление аттрибута к элементу
     * @param string $attr имя аттрибута
     * @param string $value значение аттрибута
     */
    public function addAttributes($attr,$value=null)
    {
        if (!empty($value)) {
            $this->attributes[$attr] = $value;  
        }
        elseif (is_array($attr)) {
            foreach ($attr as $key => $val)
            {
               $this->attributes[$key] = $val; 
            }
        }
    }
    protected function getIdString()
    {
        if (empty($this->id)) {
            return '';
        }
        return ' id="'.$this->id.'"';
    }
    protected function getClassString()
    {
        if (count($this->className) == 0) {
            return '';
        }
        $sReturn = \implode(' ', $this->className);
        return ' class="'.$sReturn.'"';
    }
    protected function getAttributesString()
    {
        $sReturn = '';
        if (count($this->attributes) == 0) {
            return $sReturn;
        }
        foreach ($this->attributes as $key => $value)
        {
            if (is_string($key)) {
                $sReturn .= ' '.$key.'="'.$value.'"';
            }
        }
        return $sReturn;
    }
/**
 * Получить строку с открывающим тэгом элемента со всеми его классами и аттрибутами
 */
    protected function getOpenTagString() {
        $this->openTagString = '<'.$this->elementType.$this->getIdString().
                $this->getClassString().$this->getAttributesString();
    }
    /**
     * Добавить текстовое содержимое элемента
     * @param string $string добавляемое содержимое
     */
    public function addText($string)
    {
        $this->text = $string;
    }
}
