<?php

namespace quoma\media\components\upload; 

use yii\helpers\Url;
use yii\helpers\Html;
use Yii;
use quoma\media\components\view\Preview;

/**
 * Description of UploadWidgetr
 *
 * @author martin
 */
class UploadWidget extends \yii\jui\Widget{
    
    public $inputId = 'fileupload';
    
    public $buttonOptions = ['class' => 'btn btn-success'];
    
    public $label;
    
    public $attribute = 'Media[file]';
    
    public $type = 'image';
    
    public $previewContainerOptions;
    
    public $moduleId = 'media';
    
    //Not used
    public $template;
    
    public $language;
    
    public $extraParams = [];
    
    public function init()
    {
        UploadAsset::register($this->view);
        
        $this->inputId = 'uploadinput'.$this->moduleId.$this->type.static::$counter++;
        $this->view->registerJs('Media.registerUploader({url: "'.$this->getUrl().'", inputId: "'.$this->inputId.'"});');
        
        if(empty($this->label)){
            $this->label = '<i class="glyphicon glyphicon-plus"></i> '.Yii::t('app', 'Select...');
        }
        
        Html::addCssClass($this->buttonOptions, 'fileinput-button');
    }
    
    private function getUrl()
    {
        $url = ["/$this->moduleId/$this->type/create", 'lang' => $this->language];
        $url = array_merge($url, $this->extraParams);
        
        return Url::to($url);
    }
    
    public function run()
    {
        return $this->renderInput();
    }
    
    public function renderInput()
    {
        $input = Html::fileInput($this->attribute, null, ['multiple' => '', 'id' => $this->inputId ]);
        return Html::tag('span', $this->label . $input, $this->buttonOptions);
    }
    
}
