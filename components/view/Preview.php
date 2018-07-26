<?php

namespace quoma\media\components\view; 

use yii\helpers\Url;
use yii\helpers\Html;
use Yii;

/**
 * Description of UploadWidgetr
 *
 * @author martin
 */
class Preview extends \yii\jui\Widget{
    
    const MODE_DEFAULT = 1;
    const MODE_SEARCH = 2;
    
    public $enableTitle;
    public $enableDescription;
    
    public $media;
    
    public $update = false;
    
    public $containerOptions = [];
    
    public $width = 300;
    public $height = 300;
    
    public $actionGetMediaUrl;
    
    public $buttonsTemplate = '{delete} {insert} {custom}';
    
    public $showControls = true;
    
    public $mediaOptions= [];
    
    public $mode = self::MODE_DEFAULT;
    
    public function init()
    {
        
        PreviewAssets::register($this->view);
        
        if(!isset($this->containerOptions['class'])){
            $this->containerOptions['class'] = 'col-md-6 col-xs-12';
        }
        
        if (!isset($this->actionGetMediaUrl)) {
            $this->actionGetMediaUrl= Url::to(['/media/default/get-media']);
        }
        
        if($this->update && $this->view){
            \quoma\media\components\upload\UploadAsset::register($this->view);
        }
        
    }
    
    public function run()
    {
        if($this->mode === self::MODE_DEFAULT){
            return $this->renderPreview();
        }else{
            return $this->renderSearch();
        }
    }
    
    public function renderPreview()
    {
        
        $media = $this->media;
        
        //Media
        $content = $media->renderPreview();
        
        $content = Html::tag('div', $content, array_merge(['class' => 'thumbnail'], $this->mediaOptions));
        
        //Contenedor de media
        $buttons = '';
        if($this->update == true){
            
            $deleteButton= Html::a('<span class="glyphicon glyphicon-remove"></span>'.
                    Yii::t('yii', 'Delete'), null, ['class' => 'btn btn-danger', 'data-media-delete' => '']);
            
            $insertButton= Html::a('<span class="glyphicon glyphicon-copy"></span> '.
                    Yii::t('app', 'Insert'), null, ['class' => 'btn btn-primary', 'data-media-insert' => '', 'data-language' => $media->language, 'data-media-id' => $media->media_id, 'data-media-url' => $this->actionGetMediaUrl]);
            
            $buttons = str_replace(['{delete}', '{insert}', '{custom}'], [$deleteButton, $insertButton, $media->customButtons()], $this->buttonsTemplate)
                    .Html::hiddenInput('Media[]', $media->media_id);
            
            $form = '';
            if($this->showControls){
                
                //Con columna de editor del lado derecho: (o abajo en responsive)
                $content = Html::tag('div', $content, ['class' => 'col-lg-5 col-md-12 col-sm-6 col-xs-12']); 
                
                $form = Html::tag('div', Html::activeTextInput($media, 'title', [
                    'class' => 'form-control', 
                    'placeholder' => Yii::t('app', 'Title'),
                    'name' => "MediaData[$media->media_id][title]"
                ])
                //Error: 
                .Html::error($media, 'title', ['class' => 'help-block text-left']), 
                //Que se vea bien:
                ['class' => $media->hasErrors('title') ? 'form-group has-error' : 'form-group']);
                
                $form .= Html::tag('div', Html::activeTextarea($media, 'description', [
                    'class' => 'form-control', 
                    'placeholder' => Yii::t('app', 'Description'),
                    'name' => "MediaData[$media->media_id][description]"
                //Error: 
                ]).Html::error($media, 'description', ['class' => 'help-block text-left']), 
                //Que se vea bien:
                ['class' => $media->hasErrors('description') ? 'form-group has-error' : 'form-group']);
                
                $form .= '<hr/>';
                
            }

            $editor = Html::tag('div', $form, ['class'=>'col-lg-12']);
            $editor .= Html::tag('div', Html::tag('p', $buttons), ['class' => 'col-lg-12 text-right']);
            
            $content .= Html::tag('div', $editor, ['class' => $this->showControls ? 'col-lg-7 col-md-12 col-sm-6 col-xs-12' : 'col-lg-12']);
            
            $content = Html::tag('div', $content, ['class' => 'img-container']);
        }


        $panel = Html::tag('div', '<h3 class="panel-title">'.Yii::t('app',$media->type).'</h3>', ['class' => 'panel-heading']).Html::tag('div', $content, ['class' => 'panel-body']);
        $panel = Html::tag('div', $panel, ['class' => 'panel panel-default']);

        $this->containerOptions['data-media'] = '';
        return Html::tag('div', $panel, $this->containerOptions);
        
    }
    
    public function renderSearch()
    {
        $media = $this->media;
        
        $preview = Html::tag('div', '<span class="helper"></span>'.$media->renderPreview(), ['class' => 'preview-container', 'data-language' => $media->language]);
        
        $title = $media->title ? $media->title : Yii::t('app', 'No title');
        $caption = Html::tag('div', "<h4>$title</h4>", ['class' => 'caption']);
        
        $content = Html::tag('div', $preview.'<hr/>'.$caption, ['class' => 'thumbnail']);

        $addButton = Html::a('<span class="glyphicon glyphicon-plus"></span> '.
                Yii::t('app', 'Insert'), null, ['class' => 'btn btn-primary btn-add', 'data-media-add' => '', 'data-language' => $media->language, 'data-media-id' => $media->media_id, 'data-media-url' => Url::to(['/media/default/preview'])]);

        $content = Html::tag('div', $content.$addButton, ['class' => 'col-lg-4 col-md-4 col-sm-6 col-xs-12', 'data-media' => '']); 

        return $content;
    }

}