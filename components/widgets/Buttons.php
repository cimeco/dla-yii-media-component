<?php
namespace quoma\media\components\widgets;

use quoma\media\components\helpers\MediaFactory;
use yii\helpers\Html;
use quoma\media\components\view\Preview;

/**
 * Description of Buttons
 *
 * @author martin
 */
class Buttons extends \yii\base\Widget {
    
    public $types = [];
    
    public $media = [];
    
    public $buttonTemplate = '{button} ';
    
    public $buttonOptions = ['class' => 'btn btn-default'];
    
    public $searchButtonOptions = ['class' => 'btn btn-primary pull-right'];

    public $previewOptions = [];
    
    public $update = true;
    
    //Mostramos el boton de busqueda de media?
    public $searchButton = true;
    
    public $editorSelector = '.ckeditable';

    public $extraButtons= [];
    
    //I18n
    public $language = null;

    //Multisite
    public $website_id = false;
    
    public function run()
    {
        
        $widgetId = 'media_'.static::$counter;
        $this->buttonOptions['data-widget'] = $widgetId;
        
        //Parametros adicionales para renderizar los botones por tipo de media
        $extraParams = [];
        
        //I18n
        $extraParams['lang'] = $this->language;
        $this->buttonOptions['data-language'] = $this->language;
        
        //Multisite
        if($this->website_id){
            $extraParams['website_id'] = $this->website_id;
        }
        
        //En caso de no especificar tipos, mostramos todos los disponibles
        if(empty($this->types)){
            $this->types = MediaFactory::getNames();
        }
        
        //Barra de mensajes y progreso: utilizamos una de cada una para todos los tipos
        $messageBar = $this->renderMessageBar();
        $progressBar = $this->renderProgressBar();
        
        $buttons = "";
        
        //Botones para upload
        foreach ($this->types as $type){
            $obj = MediaFactory::getObject($type);
            if ($obj) {
                $buttons .= str_replace($this->buttonTemplate, '{button}', $obj->renderButton($this->buttonOptions, $extraParams));
            }
        }
        
        if($this->searchButton){
            $buttons .= str_replace($this->buttonTemplate, '{button}', $this->searchButton($extraParams));
        }

        foreach ($this->extraButtons as $button){
            $buttons .= str_replace($this->buttonTemplate, '{button}', $button).' ';
        }

        //Preview de media actual
        $preview = $this->renderPreview();
        
        ButtonsAssets::register($this->view);
        $this->view->registerJs('Media.init('.\yii\helpers\Json::encode([
            'editorSelector' => $this->editorSelector, 
            'previewOptions' => $this->previewOptions
        ]).')', \yii\web\View::POS_READY, '-media-init-');
        
        return Html::tag('div', $messageBar.$buttons.$progressBar.$preview, ['class' => 'media-box', 'id' => $widgetId]);
        
    }
    
    public function renderMessageBar(){
        return Html::tag('div', Html::tag('div', null, ['class' => 'col-lg-12', 'data-messages' => '']), ['class' => 'row']);
    }
    
    public function renderProgressBar()
    {
        return '<br/><br/><div class="progress">
            <div class="progress-bar progress-bar-success"></div>
        </div>';
    }
    
    public function renderPreview()
    {
        $preview = '';
        if(!empty($this->media)){
            foreach($this->media as $media){
                
                $previewOptions = array_merge([
                    'media' => $media,
                    'update' => true,
                    'width' => 300,
                    'height' => 300,
                ],$this->previewOptions);
                
                $preview .= Preview::widget($previewOptions);
            }
        }
        return Html::tag('div', $preview, ['class' => 'row preview-list', 'data-preview-list' => true]);
        
    }
    
    /**
     * Renderiza un botón para buscar en la biblioteca
     * @return type
     */
    public function searchButton($params = [])
    {
        $extraParams = [];
        if($params){
            $extraParams = [
                'MediaSearch' => $params
            ];
        }

        $options = $this->searchButtonOptions;
        $options['data-search-btn'] = true;
        $options['data-media-url'] = \yii\helpers\Url::to(array_merge(['/media/default/search', 'type' => 'Image', 'types' => implode(',', $this->types)],$extraParams));
        return \yii\helpers\Html::a('<span class="glyphicon glyphicon-search"></span> '. \Yii::t('app', 'Search Media'), '#Search', $options);
    }
    
}
