<?php

namespace app\stat;

/**
 * Description of Application
 *
 * @author kotov
 */
class Application {
    
    public static $css_files = array();
    public static $js_files = array();
    /**
     *
     * @var array переменные для шаблона
     */
    public static $vars = array();

    public static function addCSS($css_uri)
    {
        if (is_array($css_uri)) {
            foreach ($css_uri as $file)
            {
                self::addCSS($file);
            }
            
            return true;
        }
        $path = _ROOT_DIR_. $css_uri;
        if (file_exists($path)) {
            $element = [
                'uri' => $css_uri,
                'no_cache' => date("U",filemtime($path))
            ];
            array_push(self::$css_files, $element);

        }
        return true;
    }
    public static function addJS($js_uri)
    {
        if (is_array($js_uri)) {
            foreach ($js_uri as $file)
            {
                self::addJS($file);
            }
            
            return true;
        }
        $path = __BASE_URI__. $js_uri;
        if (file_exists($path)) {
            $element = [
                'uri' => $js_uri,
                'no_cache' => date("U",filemtime($path))
            ];            
            array_push(self::$js_files, $element);
        }
        return true;
    }
}
