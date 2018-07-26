<?php

namespace quoma\media\components\colorsuggestion;

use Yii;

/**
 * Description of ColorHelper
 *
 * @author martin
 */
class ColorSuggest extends \yii\base\Component
{
    
    private static $_instance;
    
    public static function getInstance()
    {
        if(!self::$_instance){
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    
    public function getSuggestions($qty = 3, array $base, $bg)
    {
        
        $suggesters = $this->getSuggesters();
        
        $suggestions = [];
        foreach($suggesters as $suggester){
            $sugg = new $suggester;
            if($sugg->applies($qty, $base, true, $bg)){
                $suggestions[$sugg->getName()] = $sugg->getColors($qty, $base, true, $bg);
            }
        }
        
        return $suggestions;
        
    }
    
    public function getSuggesters($keys = false)
    {
        $files = \yii\helpers\FileHelper::findFiles(__DIR__.'/suggesters/');
        
        $classes = [];
        
        foreach($files as $element){
            $class = '\\common\\modules\\media\\components\\colorsuggestion\\suggesters\\'.str_replace('.php','',substr($element, strrpos($element, DIRECTORY_SEPARATOR)+1));
            if(class_exists($class)){
                $classes[] = $class;
            }
        }
            
        if($keys){
            $classes = array_combine($classes, $classes);
        }
        
        return $classes;
    }
    
}
