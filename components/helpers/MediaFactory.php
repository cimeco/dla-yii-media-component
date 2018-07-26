<?php

namespace quoma\media\components\helpers;

/**
 * Description of MediaFactory
 *
 * @author martin
 */
class MediaFactory {
    
    private static $typesAlias = '@vendor/quoma/media-module/models/types';
    private static $typesNamespace = '\quoma\media\models\types';

    //Devuelve los nombres de clases
    public static function getClasses() 
    {
        
        $classes = \quoma\core\helpers\ClassFinderHelper::findClasses([self::$typesAlias], true, true, [\quoma\media\models\Media::className()]);
        return $classes;
        
    }
    
    //Devuelve los nombres cortos de las clases, es decir sin namespaces
    public static function getNames() 
    {
        
        $classes = \quoma\core\helpers\ClassFinderHelper::findClasses([self::$typesAlias]);
        $names = [];
        foreach($classes as $class){
            $reflex = new \ReflectionClass($class);
            $names = $reflex->getShortName();
        }
        return $names;
        
    }
    
    //Devuelve un objeto de cada clase
    public static function getObjects() 
    {
        
        $classes = self::getClasses();
        $objects = [];
        foreach ($classes as $class){
            $reflex = new \ReflectionClass($class);
            $shortName = $reflex->getShortName();
            
            $objects[$shortName] = new $class;
        }
        
        return $objects;
        
    }
    
    public static function getObject($name)
    {
        $className = self::$typesNamespace."\\".$name;
        if(class_exists($className)){
            return new $className;
        }
        
        return null;
    }
    
    public static function getMediaObject($row){
        $media= self::getObject($row['type']);

        if($media !== null){
            foreach ($row as $attr => $value){

                $media->$attr= $value;
            }
        }
        
        return $media;
    }
    
    public static function getTypes(){
        $types = [];
        $dir= opendir(\yii\helpers\Url::to(MediaFactory::$typesAlias));
        
        while ($file= readdir($dir)){
            if (!is_dir($file) && $file !== '.' && $file !== '..' && $file !== 'search') {
                $types[]= substr($file, 0, strlen($file) - 4);
            }
        }
        
        return $types;
    }
    
}
