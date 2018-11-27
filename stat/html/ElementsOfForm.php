<?php

namespace app\stat\html;

/**
 * Абстрактный класс для элементов формы
 *
 * @author kotov
 */
abstract class ElementsOfForm extends LineElement
{
    protected $label;
   /**
     * Базовый конструктор любого элемента формы
     * @param string $type тип элемента
     * @param array $params массив аттрибутов
     */
    public function __construct($type,array $params = array()) 
    {
        parent::__construct($type, $params);
        if (key_exists('name', $params)) {
            if (is_string($params['name'])) {
                $this->attributes['name'] = $params['name'];
            }
        }
        if (key_exists('value', $params)) {
            if (is_string($params['value'])) {
                $this->attributes['value'] = $params['value'];
            }
        }
    }
    /**
     * Добавление метки к элементу формы
     * @param type $text Текст метки
     * @param type $position Расположение метки
     */
    public function addLabel($text,$position='left')
    {
        $this->label = (object) [];
        $this->label->text = $text;
        $this->label->pos = $position;
    }
    public function getHtml() 
    {
        $sResult = parent::getHtml();
        return $this->wrapWidthLabel($sResult);     
    }
    public function wrapWidthLabel($string)
    {
        if (!is_object($this->label)) {
            return $string;
        }
        switch ($this->label->pos) {
        case 'right':
            return '<label>'.$string.$this->label->text.'</label>';
        default:
            return '<label>'.$this->label->text.$string.'</label>';
        }
        
    }
        
}
