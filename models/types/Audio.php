<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace quoma\media\models\types;

use hosanna\audiojs\AudioJs;
use quoma\media\components\upload\UploadWidget;
use quoma\media\models\Media;
use Yii;

/**
 * Description of Audio
 *
 * @author juan
 */
class Audio extends Media{
    
    public function init()
    {
        parent::init();
        $this->type= 'Audio';
    }
    
    public function rules()
    {
        
        $rules = parent::rules();
        $rules[] = ['file', 'file',
            //TODO: PARAM:
            'extensions' => 'mp3,wav,wma',
            
        ];
        
        return $rules;
    }
    
    public function render($width = null, $height = null, $options = [])
    {
        return AudioJs::widget(['files' => $this->relative_url, 'uploads' => '../../frontend/web']);
    }
    
    public function renderPreview()
    {
        return '<div class="col-lg-12" style="text-align: center; border-bottom: #c9c9c9 solid 1px; height: 75%">
                    <span class="glyphicon glyphicon-music" style="font-size: 150px"></span>
                </div>
                <div class="col-lg-12" style="background-color: #fefefe; height: 25%">
                        <h4>'.$this->description.'</h4>
            </div>';
         
    }
    
    public function renderButton($options = [], $params = []) {
        return UploadWidget::widget([
            'type' => 'audio', 
            'label' => '<span class="glyphicon glyphicon-plus"></span> '.Yii::t('app', 'Audio'),
            'buttonOptions' => $options,
            'template' => '{input}',
            'extraParams' => $params
        ]);
    }
    
}
