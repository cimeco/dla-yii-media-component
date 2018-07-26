<?php

namespace quoma\media\models\types;

use quoma\core\helpers\StringHelper;
use quoma\media\components\icons\IconAsset;
use quoma\media\components\upload\UploadWidget;
use quoma\media\models\Media;
use Yii;

/**
 * Description of Document
 *
 * @author juan
 */
class Document extends Media {
    
    public function init()
    {
        parent::init();
        $this->type = 'Document';
    }
    
    public function rules() {
        $rules = parent::rules();
        $rules[] = ['file', 'file',
            //TODO: PARAM:
            'extensions' => "pdf,doc,docx,xls,xlsx,csv",
            'checkExtensionByMimeType'=>false,
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
            'type' => 'document',
            'label' => '<span class="glyphicon glyphicon-plus"></span> Document',
            'buttonOptions' => $options,
            'template' => '{input}',
            'extraParams' => $params
        ]);       
        
    }
    
    public function render($width = null, $height = null, $options = [])
    {
        return '<div class="download-box-news">
                    <a href="'. Yii::$app->frontendUrlManager->getBaseUrl(). '/'.$this->relative_url .'">
                            <h4>
                                    <span class="icon icon-download3"></span> 
                                    DESCARGAR: '. $this->title.'.'. $this->extension .
                            '</h4>					
                    </a>
		</div>';
    }
    
    public function renderPreview()
    {
        $icons_asset = Yii::$app->assetManager->getBundle(IconAsset::className());
        return '<div class="col-lg-12" style="text-align: center; border-bottom: #c9c9c9 solid 1px; height: 75%">
                    <img src="'.$icons_asset->baseUrl.'/'.$this->extension.'-icon.jpg" width="50%" height="75%"> 
                </div>
                <div class="col-lg-12" style="background-color: #fefefe; height: 25%">
                        <h4>'.$this->title.'</h4>
            </div>';
    }

    //Deprecated - NO UTILIZAR - JP, I will kill you
    public function renderForBack(){
        $icons_asset = Yii::$app->assetManager->getBundle(IconAsset::className());
        return '<div style="height: 200px"> <div class="col-lg-12" style="text-align: center; border-bottom: #c9c9c9 solid 1px; height: 75%">
                    <img src="'.$icons_asset->baseUrl.'/'.$this->extension.'-icon.jpg" width="50%" height="75%"> 
                </div>
                <div class="col-lg-12" style="background-color: #fefefe; height: 25%">
                        <h4>'.$this->title.'</h4>
            </div></div>';
    }
}
