<?php

namespace quoma\media;

Use Yii;

class MediaModule extends \yii\base\Module
{
    public $controllerNamespace = 'quoma\media\controllers';
    
    public $defaultControllerBehaviors= [];
    
    public $defaultPreviewOptions = [];
    
    public $website_id = null;
    
    //Directorio donde se encuentra el directorio uploads con los archivos media
    public $webFolder= '@frontend/web';
    
    //Necesario para que webvimark/user-management tome las rutas
    public $controllerMap = [
        'audio' => '\quoma\media\controllers\AudioController',
        'default' => '\quoma\media\controllers\DefaultController',
        'document' => '\quoma\media\controllers\DocumentController',
        'gif' => '\quoma\media\controllers\GifController',
        'html' => '\quoma\media\controllers\HtmlController',
        'i-frame' => '\quoma\media\controllers\IFrameController',
        'image' => '\quoma\media\controllers\ImageController',
        'map' => '\quoma\media\controllers\MapController',
        'twitter' => '\quoma\media\controllers\TwitterController',
        'twitter-time-line' => '\quoma\media\controllers\TwitterTimeLineController',
        'youtube' => '\quoma\media\controllers\YoutubeController',
    ];

    public function init()
    {
        parent::init();
        Yii::setAlias('@quoma', dirname(dirname(__DIR__)) . '/vendor/quoma');
        Yii::setAlias('@media', __DIR__);
            
    }
}
