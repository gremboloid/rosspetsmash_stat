<?php

namespace app\stat\services;

use app\stat\model\ModelRequest;
use app\stat\Mailer;
use app\stat\model\OperatorsConfig;
use app\stat\model\Contractor;
use app\stat\model\Brand;
use app\stat\ViewHelper;
use yii\helpers\Url;
/**
 * Description of ModelREquestService
 *
 * @author kotov
 */
class ModelRequestService
{        

    public function sendEmailAfterRequest(ModelRequest $modelRequest) 
    {
        $header = l('NEW_MODEL_REQUEST_HEADER','messages') .' '.$modelRequest->getId().'.';
        $messageTemplate = $this->getMessageForAdministrator($modelRequest);
        $sendTo = OperatorsConfig::getConfigForClassifier($modelRequest->classifierId);
        if (!$sendTo) {
            return ;
        }
        $mailer = new Mailer();
        $mailer->sendMessage($sendTo, $header, $messageTemplate);
        return ;                
    }
    protected function getMessageForAdministrator(ModelRequest $modelRequest)
    {
        $brand = new Brand($modelRequest->brandId);
        $tplVars['request_id'] = $modelRequest->getId();
        $tplVars['link'] = Url::base(true).'/admin/requests/info/';
        $tplVars['contractor_name'] = (new Contractor($brand->contractorId))->name;                
        $viewHelper = new ViewHelper(_MAIL_TEMPLATES_DIR_,'model_request_admin',$tplVars);
        return $viewHelper->getRenderedTemplate();
    }
    /**
     * 
     * @return int
     */
    public function getUnprcessedRequestsCount() 
    {
        return ModelRequest::getRowsCount([['param' => 'Publicated', 'pureValue' => 0]]);    
    }
}
