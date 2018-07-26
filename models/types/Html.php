<?php

namespace quoma\media\models\types;

/**
 * Description of Html
 *
 * @author juan
 */
class Html extends \quoma\media\models\Media{
    
    public $code;
    
    public function init()
    {
        parent::init();
        $this->type = 'Html';
    }
    
    public function rules() {
        return [
            [['title', 'code'], 'required','on' => ['default']],
            [['title'], 'string', 'max' => 140,'on' => ['default','update']],
            [['description'], 'string', 'max' => 255,'on' => ['default','update']],
        ];
    }
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        
        $code= new \quoma\media\models\Data();
        $code->media_id= $this->media_id;
        $code->attribute = "code";
        $code->value= $this->code;
        
        $code->save();
        
        return true;
    }
    
    public function getCode(){
        $code = \quoma\media\models\Data::findOne(['attribute' => 'code', 'media_id' => $this->media_id]);
        
        return $code->value;
    }
    
    public function renderButton($options = [], $params = []){
        $options['data-media-action'] = $this->type;
        $options['data-media-url'] = \yii\helpers\Url::to(array_merge(['/media/html/create'], $params));
        return \yii\helpers\Html::a('<span class="glyphicon glyphicon-plus"></span> '.$this->getName(), "#$this->type", $options);
    }
    
    public function render($width = null, $height = null, $options = [])
    {
        return $this->getCode();
    }
    
    public function renderPreview(){
        return '<div class="col-lg-12" style="text-align: center; border-bottom: #c9c9c9 solid 1px; height: 75%">
                     <h3> Codigo HTML </h3>            
                 </div>
                 <div class="col-lg-12" style="background-color: #fefefe; height: 25%">
                         <h4>'.$this->title.'</h4>
                 </div>'; 
    }
}
