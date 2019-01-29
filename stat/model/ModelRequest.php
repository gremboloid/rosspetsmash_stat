<?php

namespace app\stat\model;

use app\stat\Convert;
use app\stat\model\Contractor;
use app\stat\model\Brand;
use app\stat\helpers\ModelHelper;
use app\stat\services\ClassifierService;
use app\stat\services\ModelRequestService;
use app\stat\model\PerfAttrRequest;
use app\stat\ViewHelper;
use Yii;
/**
 * Объектное представление запросов на внесение новой модели
* @property string $name Description
 * @property string $fullName Description
 * @property string $internationalName Description
 * @property int $classifierId 
 * @property int $brandId
 * @property int $userId
 * @property int $year
 * @property int $localizationLevel
 * @property int $modelTypeId
 * @property int $isPrototype
 * @property int $publicated
 * @property string $comment
 * @author kotov
 */
class ModelRequest extends ObjectModel
{
    protected $name;
    protected $classifierId;
    protected $brandId;
    protected $fullName;
    protected $year;
    protected $localizationLevel;
    protected $comment;
    protected $userId;
    protected $date;
    protected $internationalName;
    protected $modelTypeId;
    protected $isPrototype;
    protected $publicated;
    protected $form_exist = true;
    protected $form_template_head = 'MODEL_REQUEST';
    protected $model_name = 'ModelRequest';
    protected $info_block_exist = true;
    
    const MODEL_FROM_RUSSIAN = 0;
    const MODEL_FROM_OTHER_REGION = 1;   

    
    protected static $table = 'TBLMODELREQUEST';
    
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->directory_rules = [
            'select' => [
                ['mr','Id'],
                ['mr','Name'],
                ['mr','Publicated'],
                ['tb','ContractorId'],
                ['tc','Name','ContractorName'],
                ['name' => 'Date', 'textValue' => 'TO_CHAR("mr"."Date",\'DD.MM.YYYY\')'],
            ],
            'from' => [
                [self::$table, 'mr'],
                ['TBLBRAND', 'tb'],
                [ 'TBLCONTRACTOR' , 'tc'],
            ],
            'where' => [  
                ['param' => 'tb.Id','staticValue' => 'mr.BrandId'],
                ['param' => 'tb.ContractorId','staticValue' => 'tc.Id']
            ]
        ];
    }

    protected function formConfigure($specialParams = [])
    {     
        $btns = l('BTN_ACTIONS');
        $modelHelper = new ModelHelper();
        $yearsList = $modelHelper->getYearsListForSerialProductionSelector();
        parent::formConfigure($specialParams);
        $this->form_elements['submit_button_text'] = $btns['send'];        
        $brands = Brand::getRowsArray([
            ['TBLBRAND','Id','value'],
            ['TBLBRAND','Name','text']
        ],[['param' => 'ContractorId','pureValue' => Yii::$app->user->getIdentity()->contractorId]],[['name' => 'Name','sort' => 'ASC']]);
	
        $model_types = [
            [
                'value' => self::MODEL_FROM_RUSSIAN,
                'text' => l('MODEL_REQUEST_RUSSIAN')
            ],
            [
                'value' => self::MODEL_FROM_OTHER_REGION,
                'text' => l('MODEL_REQUEST_IMPORT')
            ]
        ];
        $this->form_elements['main_form']['elements_list']['name'] = [
            'label' => l('MODELS_ELEMENT_NAME'),
            'type' => 'text',
            'required' => true,
            'size' => 300,
            'value' => $this->name ? $this->name : ''
        ];         
        $this->form_elements['main_form']['elements_list']['fullName'] = [
            'label' => l('MODELS_ELEMENT_FULLNAME'),
            'required' => true,
            'type' => 'text',
            'size' => 300,
            'value' => $this->fullName ? $this->fullName : ''
        ];
        $this->form_elements['main_form']['elements_list']['internationalName'] = [
            'label' => l('MODELS_ELEMENT_INTERNATIONALNAME'),         
            'type' => 'text',
            'size' => 300,
            'value' => $this->internationalName ? $this->internationalName : ''
        ];
        $this->form_elements['main_form']['elements_list']['classifierId'] = [
            'label' => l('CLASSIFIER_SECTION'),
            'type' => 'modal_select',
            'size' => 300,
            'js_add_id'=> 'add_classifier_id',
            'required' => true,
            'button_text' => 'Выбрать',
            'description' => l('SELECT_CLASSIFIER')
        ];
        $this->form_elements['main_form']['elements_list']['brandId'] = [
            'label' => l('MODELS_BRAND'),
            'type' => 'select',
            'size' => 300,
            'hide' => true,
        //    'selected' => $brand_id,
            'elements' => $brands            
        ];        
        $this->form_elements['main_form']['elements_list']['isPrototype'] = [
            'label' => l('MODELS_PROTOTYPE'),
            'type' => 'radio',    
            'onchange' => 'isModelPrototype',
            'value' => 0          
        ];        
        $this->form_elements['main_form']['elements_list']['year'] = [
            'label' => l('MODELS_ELEMENT_YEAR'),
            'type' => 'select',
	    'elements' => $yearsList,
            'size' => 60,
            'selected' => 2018
        ];
        $this->form_elements['main_form']['elements_list']['import'] = [
            'label' => l('MODEL_REQUEST_TYPE'),
            'type' => 'select',
            'size' => 500,  
            'selected' => 0,
            'onchange' => 'isLocalizationExist',
            'elements' => $model_types
        ];
        $this->form_elements['main_form']['elements_list']['localizationLevel'] = [
            'label' => l('MODEL_LOCALIZATION_LEVEL') .' (0-100)',
            'type' => 'text',
            'size' => 30,
            'validateParams' => 
            [
                'min' => 0,
                'max' => 100
            ],
            'value' => '',
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['comment'] = [
            'label' => l('MODELS_COMMENT'),
            'type' => 'textarea', 
            'size' => 300,
            'value' => ''
        ];     
        $this->form_elements['main_form']['elements_list']['userId'] = [     
            'type' => 'hidden',            
            'value' => Yii::$app->user->getId()     
        ];
    }
    protected function informBlockConfigure() 
    {    
        if (!$this->isExistInDb()) {
            $this->info_elements['elements_list'] = array();
            return;
        }
        parent::informBlockConfigure();
        $id = $this->getId();          
        $this->info_elements['elements_list']['name'] = [
            'name' => l('MODELS_ELEMENT_NAME'),
            'value' => $this->name
        ];
        $this->info_elements['elements_list']['fullName'] = [
            'name' => l('MODELS_ELEMENT_FULLNAME'),
            'value' => $this->fullName
        ];
        $this->info_elements['elements_list']['internationalName'] = [
            'name' => l('MODELS_ELEMENT_INTERNATIONALNAME'),
            'value' => $this->internationalName ?? 'Нет'
        ];
        $brand = new Brand($this->brandId);
        $this->info_elements['elements_list']['contractorName'] = [
            'name' => l('BRAND_CONTRACTOR'),
            'value' => (new Contractor($brand->contractorId))->name
        ];
       // $classifier = new Classifier($this->classifierId);
        $this->info_elements['elements_list']['classifierName'] = [
            'name' => l('CLASSIFIER_SECTION'),
            'value' => ClassifierService::getParentClassifierString($this->classifierId)
        ];
        $this->info_elements['elements_list']['isPrototype'] = [
            'name' => l('MODELS_PROTOTYPE'),
            'value' => $this->isPrototype ? 'Да' : 'Нет'
        ];
        if (!$this->isPrototype) {
            $this->info_elements['elements_list']['year'] = [
              'name' => l('MODELS_ELEMENT_YEAR'),
                'value' => $this->year ?? 'Не указано'
            ];
        }
        $model_types = [ self::MODEL_FROM_RUSSIAN => l('MODEL_REQUEST_RUSSIAN'),
                         self::MODEL_FROM_OTHER_REGION => l('MODEL_REQUEST_IMPORT')            
        ];
        $this->info_elements['elements_list']['modelTypes'] = [
            'name' => l('MODEL_REQUEST_TYPE'),
            'value' => $model_types[(int) $this->modelTypeId]
        ];          
        $this->info_elements['elements_list']['comment'] = [
            'name' => l('MODELS_COMMENT'),
            'value' => $this->comment
        ];                                
    }

    public function saveModelObject($data, $form_elements = null, $return_json = true)
    {
        parse_str($data['frm_data'],$form_elements); 
        $user = new User ($form_elements['userId']);
        $tplVars['user_email'] = $user->email;
        $viewHelper = new ViewHelper(_MESSAGES_TEMPLATES_DIR_,'save_model',$tplVars);
        $message = $viewHelper->getRenderedTemplate();
        $this->setFlashMessageOk($message);
        $res = parent::saveModelObject($data,$form_elements,false);
        if ($res['STATUS'] !== OBJECT_MODEL_SAVED) {
            if ($return_json) {
                return json_encode($res);
            } else {
                return $res;
            }
        }        
        if (key_exists('tech_data', $data)) {
            $id = $res['NEW_ID'];
            parse_str($data['tech_data'], $elements);
            foreach ($elements as $key => $value) {
                if (!$value) {
                    continue;
                }
                $perf_obj = [
                'performanceAttrId' => Convert::getNumbers($key),
                'value' => $value,
                'modelId' => intval($id)
                ];
                $new_obj = PerfAttrRequest::getInstance($perf_obj);
                $new_obj->addToDb();
            
            }
        }        
        return json_encode([
            'STATUS' => OBJECT_MODEL_SAVED,
            'MESSAGE' => $this->flashMessageOk ?: l('WRITE_TO_DB','messages')
        ]);
        //parent::saveModelObject($data, $form_elements, $return_json);
    }
    public function afterInsert()
    {
        parent::afterInsert();
        Yii::info('Добавлен запрос № '.$this->id.' на создание модели '.$this->name.'.'); 
        $this->sendEmailToAdministrator();
    }
    protected function sendEmailToAdministrator()
    {
        $modelRequestService = new ModelRequestService();
        $modelRequestService->sendEmailAfterRequest($this);
    }
    
    
    
    
}
