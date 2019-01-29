<?php

namespace app\stat\helpers;

/**
 * Description of ModelHelper
 *
 * @author kotov
 */
class ModelHelper
{
    const SERIAL_PRODUCTION_MIN_YEAR = 1950;
    /**
     * 
     * @param type $selectedAlready
     * @return type
     */
    public function getYearsListForSerialProductionSelector($selectedAlready = false)
    {
        $currentYear = date('Y');
        $yearList = $selectedAlready ? [['texr' => '','value' => '']] : [];
        for ($year = $currentYear ; $year >= self::SERIAL_PRODUCTION_MIN_YEAR ; $year--) {
            $yearList[] = [
                'text' => $year,
                'value' => $year
            ];
        }
        return $yearList;
    }
}
