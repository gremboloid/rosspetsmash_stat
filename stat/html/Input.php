<?php


namespace app\stat\html;

/**
 * Базовый ккласс элемента формы Input
 */
class Input extends ElementsOfForm
{
    protected $type;
    /**
     * Базовый конструктор элемента формы Input
     * @param string $type тип элемента
     * @param array $params массив аттрибутов
     */
    public function __construct($params =[]) 
    {
        parent::__construct('input',$params);
         if (key_exists('type', $this->attributes)) {
             if (is_string($this->attributes['type'])) {
                $this->type = $this->attributes['type'];
             }
            unset($this->attributes['type']);
            }     
        if (key_exists('type', $params)) {
            if (is_string($params['type'])) {
                $this->type = $params['type'];
           }
        }
        $this->singleTag = true;
    }
    protected function getTypeString()
    {
         if (empty ($this->type)) {
            return '';
         }
        return ' type="'.$this->type.'"';
    }
    protected function getOpenTagString()
    {
        parent::getOpenTagString();
        $this->openTagString.= $this->getTypeString();
    }

    final function setTypeString($type)
    {
        if (get_class($this)!= 'Input') {
            return false;
        }
        $this->type = $type;
    }
}
