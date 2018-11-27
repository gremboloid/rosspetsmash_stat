<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Html;

/**
 * Класс Html-элемента Select
 */
class Select extends ElementsOfForm
{
    protected $selectedOption;
    protected $options;
/**
 * Конструктор класса HTML-элемента Select
 * @param type $params
 */
    public function __construct($params =[]) 
    {
        parent::__construct('select',$params);

    }
    public function getHtml() 
    {
        $this->getOpenTagString();
        if (empty($this->elementType)) {
            return '';
        }
        $sResult = $this->openTagString.'>';
        if (count($this->options) != 0) {
            foreach ($this->options as $element)
            {
                $sResult .= $element;
            }
        }
        if ($this->text) {
            $sResult.= $this->text;
        }
        $sResult.= '</'.$this->elementType.'>'; 
        $sResult = $this->wrapWidthLabel($sResult);
        return $sResult;
    }
    /**
     * Добавление параметра в список выбора к элементу Select
     * @param array $elem эссоциативный массив с аттрибутами элемента Option
     */
    public function addOption (array $elem) 
    {
        $selected = key_exists('selected', $elem) ? " selected" : "";
        $this->options[] = '<option value="'.$elem['value'].'"'.$selected.'>'.$elem['text'].'</option>';
    }
    /**
     * Добавление массива параметров в список выбора Options
     * @param array $options массив элементов Option
     */
    public function addOptions (array $options)
    {
        foreach ($options as $option)
        {
            $this->addOption($option);
        }
    }
    public function deleteFirstOption() {
        if (count ($this->options) > 0)
            array_shift($this->options);    
    }
    public function deleteLastOption() {
        if (count ($this->options) > 0)
            array_pop($this->options);    
    }
}
