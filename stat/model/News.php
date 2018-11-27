<?php



namespace app\stat\model;

class News extends ObjectModel
{
    protected $name;
    protected $content;
    protected $anons;
    protected $date;
    protected $publishDate;
    protected $unpublishDate;
    protected $publicate;
    protected $form_exist = true;
    protected $form_template_head = 'NEWS';
    protected $model_name = 'News';
    
    protected static $table = "TBLNEWS";
    
    public function __construct($id = null) {
        parent::__construct($id);
        $this->directory_rules = [
            'select' => [
                ['n','Id'],
                ['n','Name'],
                ['n','Content'],
                ['n','Anons'],
                ['n','Date'],
                ['name' => 'StrDate', 'textValue' => 'TO_CHAR ("n"."Date",\'DD.MM.YYYY\')'],
                ['n','PublishDate'],
                ['n','UnpublishDate'],
                ['n','Publicate'],
            ],
            'from' => [
                [ self::$table , 'n']
            ],
            'where' => [                
            ]
        ];
    }
    protected function formConfigure() {
        parent::formConfigure();
        if (is_resource($this->content) ) {
            $content = stream_get_contents($this->content);
        } else {
            $content = '';
        }
        if ($this->publishDate) {
            $publishDate = \DateTime::createFromFormat('j-M-Y', $this->publishDate);
            $this->publishDate = $publishDate->format('d.m.Y');
        }
        if ($this->unpublishDate) {
            $unpublishDate = \DateTime::createFromFormat('j-M-Y', $this->unpublishDate);
            $this->unpublishDate = $unpublishDate->format('d.m.Y');
        }
        if ($this->date) {
            $createDate = \DateTime::createFromFormat('j-M-Y', $this->date);
            $this->date = $createDate->format('d.m.Y');
        }
            $publicate = $this->getId() ? $this->publicate : 0;

        $this->form_elements['main_form']['elements_list']['name'] = [
            'label' => l('NEWS_ELEMENT_HEAD'),
            'type' => 'text',
         //   'required' => true,
            'size' => 250,
            'value' => $this->name ? $this->name : ''
        ];
        $this->form_elements['main_form']['elements_list']['date'] = [
            'label' => l('NEWS_ELEMENT_CREATION_DATE'),
            'type' => 'date',
            'size' => 80,
            'value' => $this->date ? $this->date : date('d.m.Y')
        ];
        $this->form_elements['main_form']['elements_list']['publishDate'] = [
            'label' => l('NEWS_ELEMENT_PUBLISH_DATE'),
            'type' => 'date',
            'size' => 80,
            'value' => $this->publishDate ? $this->publishDate : ''
        ];
        $this->form_elements['main_form']['elements_list']['unpublishDate'] = [
            'label' => l('NEWS_ELEMENT_UNPUBLISH_DATE'),
            'type' => 'date',
            'size' => 80,
            'value' => $this->unpublishDate ? $this->unpublishDate : ''
        ];
        
        $this->form_elements['main_form']['elements_list']['anons'] = [
            'label' => l('NEWS_ANONS'),
            'type' => 'textarea',
            'class' => 'mce-editable',
            'value' => $this->anons ? $this->anons : ''
        ];
        
        $this->form_elements['main_form']['elements_list']['content'] = [
            'label' => l('NEWS_FULL_TEXT'),
            'type' => 'textarea',
            'class' => 'mce-editable',
            'value' => $content
        ];
        $this->form_elements['main_form']['elements_list']['publicate'] = [
            'label' => l('NEWS_ELEMENT_PUBLICATED'),
            'type' => 'radio', 
            'value' => $publicate
        ];
    }
    
}
