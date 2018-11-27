<?php

namespace app\stat\model;

use app\stat\db\QuerySelectBuilder;
use app\stat\Sessions;
use app\stat\model\ModelContractorForm;
/**
 * Description of InputForm
 *
 * @author kotov
 */
class InputForm extends ObjectModel
{
    
    public $contractorId;
    public $date;
    public $userId;
    public $actuality;
    public $comment;
    public $dataBaseTypeId;
    public $month;
    public $year;
    public $day;
    public $fileId;    
    
    protected static $table = "TBLINPUTFORM";
    
    public function getFormHtml($period)
    {
       // сброс сессионной переменной
        Sessions::unsetVarSession('TMP_COUNTRIES_LIST');        
        switch ($this->dataBaseTypeId) {
            case 1:
            case 2:
                $formData = new Production();
                break;
            case 4:
            case 5:
                $formData = new Export();
                break;
            case 12:
                $formData = new Economic();
                break;
            default:                   
        }
        if ($formData) {
            $formData->setPeriod($period);
            $formData->setContractorId($this->contractorId);
            $formData->setTypeData($this->dataBaseTypeId);
            return json_encode([
                    'message' => $formData->getHtmlForm($this)
                ]);            
        }                
    }
    public function getModelsCount() 
    {
        if (!$this->id) {
            return 0;
        }
        $queryBuilder = new QuerySelectBuilder();
        $queryBuilder->from = 'TBLPRODUCTION';
        $queryBuilder->where = [['param' => 'InputFormId', 'staticNumber' => $this->id ]];
        return (int) getDb()->getRowsCount($queryBuilder);
    }
    public function deleteForm()
    {
        $id = (int) $this->id;
        if (empty($id)) {
            return false;
        }
        switch ($this->dataBaseTypeId) {
            case 1:
            case 2:
            case 4:
            case 5:                

                $elements_list = Production::getRowsArray(["Id"],[['param' => 'InputFormId' , 'staticNumber' => $this->getId()]]);
                $class_name = 'Production';
                break;
            case 12:
                $elements_list = Economic::getRowsArray(["Id"],[['param' => 'InputFormId' , 'staticNumber' => $this->getId()]]);
                $class_name = 'Economic';
                break;
            default:                   
        }
        $class_name = '\\app\\stat\\model\\'.$class_name;
        foreach ($elements_list as $element) {
            $row = new $class_name($element['Id']);
            $row_id = (int) $row->id;
            if (empty($row_id)) {
                continue;
            }
            $row->deletable = true;
            $row->delete();
            unset ($row);
        }
        $this->deletable = true;
        $this->delete();
        return true;
    }
   
    public function deleteModelFromForm($assocArray) 
    {                
        $formId = $this->getId();
        if ($this->getId()) {
            $prodProps = [  'inputFormId' => $formId,
                            'modelId' => $assocArray['modelId']];
            Production::deleteByProps($prodProps);
        }
        ModelContractorForm::deleteByProps($assocArray);
    }         
    
    
    
    
    
}
