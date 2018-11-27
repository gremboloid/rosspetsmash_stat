<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Html;

/**
 * Description of Form
 *
 * @author kotov
 */
class Form extends BlockElement
{
    protected $method;
    protected $action;

    /**
     * Конструктор элемента Form
     * @param array $params массив аттрибутов
     */
    public function __construct($params =[]) 
    {
        parent::__construct('form',$params);
        if (key_exists('method', $this->attributes)) {
            $this->method = $this->attributes['method'];
            unset($this->attributes['method']);
            }
        
        if (key_exists('method', $params)) {
            if (is_string($params['method'])) {
                $this->method = $params['method'];
           }
        }
        if (key_exists('action', $this->attributes)) {
            $this->action = $this->attributes['action'];
            unset($this->attributes['action']);
            }
        
        if (key_exists('action', $params)) {
            if (is_string($params['action'])) {
                $this->action = $params['action'];
           }
        }
    }
    protected function getMethodString()
    {
        $method = strtoupper($this->method);
        if (!in_array($method,['GET','POST'])) {
            return '';
        }
        return ' method="'.$method.'"';
    }
    protected function getActionString()
    {
        return ' action="'.$this->action.'"';
    }
    protected function getOpenTagString() 
    {
        parent::getOpenTagString();
        $this->openTagString.= $this->getMethodString().$this->getActionString();
    }

    public function getHtml() 
    {
        return parent::getHtml();
    }
    
}
