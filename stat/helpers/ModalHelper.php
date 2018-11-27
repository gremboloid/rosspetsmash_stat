<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\helpers;

use app\stat\ViewHelper;
/**
 * Класс для отображения модальных окон
 *
 * @author kotov
 */
class ModalHelper {
    
    public function showDateChangeModal($settings) {
        $def_obj = [
            'min_year' => 2009,
            'max_year' => date('Y'),
            'selected' => [
                'year' => date('Y'),
                'month' => 1
            ],
            'button_id' => 'change-date-in-form'
        ];
        $tpl_vars = [];
        if (is_array($settings)) {
            if (key_exists('min_year',$settings)) {
                $min_year = $settings['min_year'];
            }
            if (key_exists('selected', $settings)) {
                $selected = $settings['selected'];
                if (is_array($selected)) {
                    if (key_exists('year', $selected)) {
                        $selected_year = $selected['year'];
                    }
                    if (key_exists('month', $selected)) {
                        $selected_month = $selected['month'];
                    }
                }
            }
        }
        $tpl_vars['min_year'] = $min_year ? $min_year : $def_obj['min_year'];
        $tpl_vars['max_year'] = $max_year ? $max_year : $def_obj['max_year'];
        $tpl_vars['selected_month'] = $selected_month ? $selected_month : $def_obj['selected']['month'];
        $tpl_vars['selected_year'] = $selected_year ? $selected_year : $def_obj['selected']['year'];
        $tpl_vars['button_id'] = $button_id ? $button_id : $def_obj['button_id'];
        $tpl_vars['legend'] = l('CHANGE_PERIOD_LEGEND');
        $tpl_vars['months'] = l('MONTHS','words');
        $view_helper = new ViewHelper(_MODAL_TEMPLATES_DIR_,'modal_date_select',$tpl_vars);
        return $view_helper->getRenderedTemplate();
        
    }
}
