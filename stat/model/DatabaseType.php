<?php

namespace app\stat\model;

/**
 * Description of DatabaseType
 *
 * @author kotov
 */
class DatabaseType extends ObjectModel
{
        protected $name;
        
        public static $availableTypes = array (1,2,4,5,12);
        protected static $table = 'TBLDATABASETYPE';
}
