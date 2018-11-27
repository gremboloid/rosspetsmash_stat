<?php

namespace app\stat\providers;
use app\stat\services\InputFormService;
use app\stat\exceptions\DefaultException;

/**
 * Description of InputFormTableProvider
 *
 * @author kotov
 */
class InputFormTableProvider implements TableDataProvideInterface
{
    /**
     * @var InputFormService;
     */
    protected $formService;


    public function __construct(InputFormService $service) {
        $this->formService = $service;
    }

    public function getCount() {        
        return $this->formService->getFormsListCount();
    }

    public function getTableData(int $pageNumber,int $rowCount) {
        return $this->formService->getFormsList($pageNumber, $rowCount);
    }

}
