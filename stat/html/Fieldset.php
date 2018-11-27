<?php


namespace Html;

/**
 * Класс элемента группировки элементов формы Fieldset
 *
 * @author kotov
 */
class Fieldset extends BlockElement
{
    /** @var string заголовог блока  */
    protected $legend;
    
    public function __construct($title,$params=array()) {
        parent::__construct('fieldset',$params);
        $this->legend = $title;
        if ($title != '') {
            $legend = new LineElement('legend',['text' => $title]);
            $this->addChildElement($legend);
        }
    }
}
