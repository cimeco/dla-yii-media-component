<?php

namespace quoma\media\models\types;

/**
 * Description of IFrame
 *
 * TODO: agregar alto
 * 
 * @author juan
 */
class IFrame extends \quoma\media\models\Media {
    
    public function init()
    {
        parent::init();
        $this->type= 'IFrame';
    }
    
    public function rules() {
        return  [
            [['title'], 'string','on' => ['default','update']],
            [['relative_url', 'title'], 'required','on' => ['default']],
            [['relative_url'], 'url'],
            [['title'], 'string', 'max' => 140],
            [['description'], 'string', 'max' => 255],
        ];
        
    }

    public function renderButton($options = [], $params = []) {
        $options['data-media-action'] = $this->type;
        $options['data-media-url'] = \yii\helpers\Url::to(array_merge(['/media/i-frame/create'], $params));
        return \yii\helpers\Html::a('<span class="glyphicon glyphicon-plus"></span> '.$this->getName(), "#$this->type", $options);
    }
    
    public function render($width = null, $height = null, $options = [])
    {
        return '<iframe src="'.$this->relative_url.'" style="width: 100%; border:0;">Su navegador no soporta iframes. Actualicelo para poder visualizar este contenido</iframe>';
        
    }    
    
    public function renderPreview()
    {
        return '<div class="update-preview-img-container" style="background-color: #dcdcdc; width: 100%; max-width:300px; height: auto; border: #c9c9c9 solid 3px;">
                <div class="col-lg-12" style="text-align: center; border-bottom: #c9c9c9 solid 1px; height: 120px;">
                    <h3> IFrame </h3>            
                </div>
                <div class="col-lg-12" style="background-color: #fefefe; height: 80px;">
                    <h4>'.$this->title.'</h4>
                    <h5>'.$this->relative_url.'</h5>
                </div>
            </div>';
        
    }    
    
}
