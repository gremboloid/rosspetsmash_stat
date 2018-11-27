<?php

namespace app\stat;

class Convert {

    /**
     * Получить числа из строки
     * @param type $string строка
     * @return int
     */
    public static function getNumbers($string=null)
    {
        if (!$string) {
            return 0;
        }
        return (int) preg_replace('/[^\d]/', '', $string);
    }
}
