<?php

namespace app\stat\model;

use app\stat\Tools;
use app\stat\Convert;
use app\stat\Sessions;
use app\stat\services\ClassifierService;
use app\stat\model\ValuesPerfAttr;
use app\stat\helpers\TechCharacteristicFormHelper;
use app\stat\helpers\ModelHelper;
use app\stat\services\ModelService;
/**
 * Description of Models
 * @property string $name Description
 * @property string $fullName Description
 * @property string $internationalName Description
 * @property int $classifierId Description
 * @property int $brandId Description
 * @property int $year Description
 * @property int $modelTypeId
 * @property int $isPrototype
 * @property string $commemt Description
 * @author kotov
 */
class Models extends ObjectModel implements IChangeClassifier
{
    protected $name;
    protected $classifierId;
    protected $brandId;
    protected $approved;
    protected $fullName;
    protected $year;
    protected $actuality;
    protected $comment;
    protected $internationalName;
    protected $modelTypeId;
    protected $isPrototype;
    protected $form_exist = true;
    protected $form_template_head = 'MODELS';
    protected $model_name = 'Models';
    protected $info_block_exist = true;

    protected static $table = "TBLMODEL";       
    
    const FORM_SOURCE_USER_REQUEST = 'Request';
	const MIN_YEAR = 1950;
    
    public function __construct($id = null) {
        parent::__construct($id);
        $this->directory_rules = [
            'select' => [
                ['tm','Id'],
                ['tm','Name'],
                ['tm','ClassifierId'],
                ['tm','ModelTypeId'],
                ['tb','ContractorId'],
                ['tc','Name','ContractorName'],
                ['tc','Present']
            ],
            'from' => [
                [ self::$table , 'tm'],
                [ 'TBLBRAND' , 'tb'],
                [ 'TBLCONTRACTOR' , 'tc'],
            ],
            'where' => [  
                ['param' => 'tb.Id','staticValue' => 'tm.BrandId'],
                ['param' => 'tb.ContractorId','staticValue' => 'tc.Id']
            ]
        ];
    }
    protected function formConfigure($specialParams = []) {
        parent::formConfigure($specialParams);
        $modelHelper = new ModelHelper();
        $yearsList = $modelHelper->getYearsListForSerialProductionSelector(true);
       // extract($data_obj);
        $brands = Brand::getRowsArray([
            ['TBLBRAND','Id','value'],
            ['TBLBRAND','Name','text']
        ],null,[['name' => 'Name','sort' => 'ASC']]);
        $model_types = ModelType::getRowsArray([
            ['TBLMODELTYPE','Id','value'],
            ['TBLMODELTYPE','Name','text']
        ],null,
                [['name' => 'Name','sort' => 'ASC']]);
        $id = $this->getId();
        if ($id) {  
            $brand = new Brand($this->brandId);
            $contractor_id = $brand->contractorId;
            $brand_id = $this->brandId;
            $classifier_id = $this->classifierId;
            $model_types_id = $this->modelTypeId;
            $prototype = $this->isPrototype;
        } else {
            $classifier_id = $this->classifierId ?? Convert::getNumbers(Sessions::getClassifierId());
            if ($this->brandId) {
                $brand = new Brand($this->brandId);
                $contractor_id = $brand->contractorId;
                $brand_id = $brand->getId();
            }
            elseif (!$contractor_id = Convert::getNumbers(Sessions::getContractorId())) {
                $def_brnd = new Brand($brands[0]['value']);
                $contractor_id = $def_brnd->contractorId;
                $brand_id = $def_brnd->getId();
            } else {
               $brand_id = Brand::getFieldByValue('ContractorId', $contractor_id);
            }
            $model_types_id = 1;
            $prototype = $this->isPrototype ?? 0;
        }
        if (!$classifier_id) {
            $changeClassifierText = 'Выбрать';
            $changeClassifierDescription = l('SELECT_CLASSIFIER');
        } else {
            $changeClassifierText = 'Изменить';
            $changeClassifierDescription = l('CHANGE_CLASSIFIER') . ' ' . (new Classifier($classifier_id))->name;
        }
        $contractor = new Contractor($contractor_id);
        $def_contracror_name = $contractor->name;
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
            'value' => $this->fullName ? html_entity_decode($this->fullName) : ''
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
            'description' => $changeClassifierDescription,
            'button_text' => $changeClassifierText,
            'value' => $classifier_id
        ];
        $this->form_elements['main_form']['elements_list']['brandId'] = [
            'label' => l('MODELS_BRAND'),
            'type' => 'select',
			'size' => 300,
            'class' => 'ajax_changed',
            'selected' => $brand_id,
            'elements' => $brands            
        ];
        $this->form_elements['main_form']['elements_list']['contractor'] = [
            'label' => l('MODELS_CONTRACTOR'),
            'type' => 'simple',
            'class' => 'contractor_position',
            'text' => $def_contracror_name
        ];        
        $this->form_elements['main_form']['elements_list']['isPrototype'] = [
            'label' => l('MODELS_PROTOTYPE'),
            'onchange' => 'isModelPrototype',
            'type' => 'radio',            
            'value' => $prototype          
        ];        
        $this->form_elements['main_form']['elements_list']['year'] = [
            'label' => l('MODELS_ELEMENT_YEAR'),
            'type' => 'select',            
            'size' => 60,
            'selected' => $this->year ? $this->year : (!$id ? date('Y') : ''),
            'elements' => $yearsList,
            'hide' => (bool) $prototype
        ];                
        $this->form_elements['main_form']['elements_list']['modelTypeId'] = [
            'label' => l('MODELS_TYPE'),
            'type' => 'select',
            'size' => 300,          
            'selected' => $model_types_id,
            'elements' => $model_types
        ];
        
        $this->form_elements['main_form']['elements_list']['comment'] = [
            'label' => l('MODELS_COMMENT'),
            'type' => 'textarea', 
            'size' => 300,
            'value' => $this->comment ? $this->comment : ''
        ];
        
        if (!$this->getId()) {
            $this->form_elements['main_form']['elements_list']['approved'] = [
                'type' => 'hidden',
                'value' => 1
            ];
        }
        
        if (key_exists('requestId', $specialParams)) {
            $requestId = $specialParams['requestId'];
        }
        $techCharasteristicServive = new TechCharacteristicFormHelper($classifier_id, $this->id,$requestId);
        try {
            $this->form_elements['dop_block'][0] = $techCharasteristicServive->getHtmlForm(); 
        } catch (\DomainException $e) {
            
        }
    }
        
    protected function informBlockConfigure() {
        parent::informBlockConfigure();
        $id = $this->getId();
        $classifier_group_names = l('CLASSIFIER_GROUPS');
        $classifier_parents = array_reverse(ClassifierService::getClassifierParents($this->classifierId));
        array_shift($classifier_parents);
        $idx = 0;
        $pidx = 2;
        foreach ($classifier_parents as $parent) {
            if ($idx < count($classifier_group_names)) {
                $group_name = $classifier_group_names[$idx];
            } else {
                $group_name = 'Подгруппа уровень '.$pidx++;
            }
            $this->info_elements['elements_list'][$idx++] = 
                [
                    'name' => $group_name ,
                    'value' => $parent['Name']
                ];
        }        
    }

    public static function changeClassifier($models_list, $classifier_id) 
    {
        $classifier_id = Convert::getNumbers($classifier_id);        
        if (is_string($models_list)) {
            $models_list = explode(',', $models_list);
        }
        if (empty($classifier_id)) {
            return Tools::getErrorMessage('request_error');
        }
        if (count($models_list) > 0) {
            $classifier = new Classifier($classifier_id);
            if (!$classifier->isLeaf()) {
                return Tools::getErrorMessage('request_error');
            }
            foreach ($models_list as $element) {
                $model_id = Convert::getNumbers($element);
                if (empty($model_id)) {
                    continue;
                }
                $model_object = new Models($model_id);
                if (empty($model_object->getId())) {
                    continue;
                }
                $model_object->set('classifierId' , $classifier_id);
                $model_object->updateDb();
                unset($model_object);
                
              // $model_object->classifierId = 
            }
            return Tools::getMessage('Перенос моделей выполнен успешно');
        }
        
    }
    public function saveModelObject($data,$form_elements=null,$return_json=true) 
    {
        $id = $this->getId(); 
        parse_str($data['frm_data'],$form_elements);
        $form_elements['internationalName'] = empty($form_elements['internationalName']) ? $form_elements['fullName'] : $form_elements['internationalName'];
        $res = parent::saveModelObject($data,$form_elements,false);
        if ($res['STATUS'] !== OBJECT_MODEL_SAVED) {
            return $res;
        }
        if (key_exists('tech_data', $data)) {
            if (!$id) {
                $id = $res['NEW_ID'];
            }
            parse_str($data['tech_data'], $elements);
            foreach ($elements as $key => $value) {
                $perf_obj = [
                'performanceAttrId' => Convert::getNumbers($key),
                'value' => $value ? $value : ' ',
                'modelId' => intval($id)
                ];
                $exist_field = ValuesPerfAttr::getRowsArray(['Id'],[
                    ['param' => 'PerformanceAttrId','staticNumber' => $perf_obj['performanceAttrId']],
                    ['param' => 'ModelId','staticNumber' => $perf_obj['modelId']]
                ]);
                if (count($exist_field) == 1) {
                    $updated_obj = new ValuesPerfAttr($exist_field[0]['Id']);
                    $updated_obj->setElementsFromArray($perf_obj);
                    $updated_obj->setWritable();
                    $updated_obj->updateDb();
                } else {
                    $new_obj = ValuesPerfAttr::getInstance($perf_obj);
                    $new_obj->addToDb();
                }
            }
        }
        return json_encode($res);
    }
    public function beforeDelete()
    {
        parent::beforeDelete();
        $characteristicCount = ValuesPerfAttr::getRowsCount([['param' => 'ModelId','staticNumber' => $this->id]]);
        if ($characteristicCount > 0) {
            $query = 'DELETE FROM "TBLVALUESPERFATTR" "v" WHERE "v"."ModelId" = '.$this->id;
            getDb()->dbQuery($query);
        }
    }
    public function afterInsert()
    {
        parent::afterInsert();
        $modelService = new ModelService();
        $modelService->sendEmailToEditor($this);
    }
    

}
