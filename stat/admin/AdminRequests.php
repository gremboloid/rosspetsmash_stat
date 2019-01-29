<?php
namespace app\stat\admin;


use app\stat\model\ModelRequest;
use app\stat\Tools;


class AdminRequests extends AdminDirectory
{
     public function __construct()
     {
        $this->link = 'requests';
        $this->name = l('GLOB_MODEL_REQUESTS_SETTINGS','admin');
        $this->parentElement = 'Index';
        $this->directory_model = new ModelRequest();
        $this->model_name = 'ModelRequest'; 
        $this->table_cols_headers = [ 'Number' => '№',
                                      'Name' => l('MODELS_NAME','admin'),
                                      'ContractorName' => 'Производитель',
                                      'Date' => 'Дата публикации',
                                   //   'Publicated' => l('PUBLICATED','admin'),
                                    ];
        $this->cols_to_sort = ['Name','ContractorName','Date'];
        $this->filter_array = [
            'publicated' => 0
        ];
        parent::__construct(); 
        
     }
     protected function setLeftBlockVars()
     {
         parent::setLeftBlockVars();
         $publicatedList = [
            ['text' => 'Обработанные запросы', 'value' => 1],
            ['text' => 'Необработанные запросы', 'value' => 0]
         ];
        $this->left_block_vars['blocks_list'][1]['hide'] = false;
        $this->left_block_vars['blocks_list'][1]['elements'] = [
            //        [ 'type' => 'test','header_text' => 'Тест'],
                    [   'type' => 'select',
                        'header_text' => 'Фильтр запросов',
                        'class_name' => 'rs-form-control',
                        'id' => 'select-publicated',
                        'name' => 'publicated',
                        'options' => $publicatedList,
                        'selected' => $this->filter_array['publicated']
                        
                    ]
                ];
        $this->left_block_vars['blocks_list'] = array_reverse($this->left_block_vars['blocks_list']);
     }

     protected function prepare()
     {
         parent::prepare();
         $this->action_list = [
             [
                'action' => 'rowDetailInfo',
                'name' => l('ROW_DETAIL_INFO'),
                'image' => 'info32.png'
            ],
            'not_publicated' => [
                'action' => 'addToPublicated',
                'name' => l('ROW_DETAIL_INFO'),
                'image' => 'plus32.png',
                'condition' => [
                    'param' => 'Publicated',
                    'value' => 0
                ]
            ]             
         ];
     }
    protected function loadFilters() {
        parent::loadFilters();
        if ($val = (int)Tools::getValue('publicated','GET')) {
            $this->filter_array['publicated'] = $val;
        }
        if ($this->filter_array['publicated'] == 0) {
           $this->action_list['not_publicated']['hide'] = true;
        }
    }
}

