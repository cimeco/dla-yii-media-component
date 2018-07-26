<?php

namespace quoma\media\models\types;

use quoma\core\helpers\StringHelper;
use quoma\media\components\icons\IconAsset;
use quoma\media\components\upload\UploadWidget;
use quoma\media\models\Media;
use Yii;

/**
 * Description of Pdf
 *
 * @author juan
 */
class Gif extends Media {
    
    public function init(){
        parent::init();
        $this->type= 'Gif';
    }
    
    public function rules() {
        $rules = parent::rules();
        $rules[] = ['file', 'image',
            'extensions' => 'gif',
        ];
        
        return $rules;
    }
    
    public function beforeSave($insert) {
        parent::beforeSave($insert);
        
        if($insert){
            $this->title = StringHelper::systemName($this->file->baseName);
        }
        
        return true;
    }
    
    public function renderButton($options = [], $params = []) {
        
        return UploadWidget::widget([
            'type' => 'gif',
            'label' => '<span class="glyphicon glyphicon-plus"></span> Gif',
            'buttonOptions' => $options,
            'template' => '{input}',
            'extraParams' => $params
        ]);       
        
    }
    
    public function render($width = null, $height = null, $options = [])
    {
        return '<img src="'.$this->url.'">';
    }
    
    public function renderPreview(){
        return '<img src="'.$this->url.'">';
    }
}
