<?php

namespace quoma\media\models\types;

use Yii;
use quoma\media\models\Type;
use quoma\media\models\Media;
use quoma\media\models\Sized;
use yii\helpers\FileHelper;
use quoma\modules\config\models\Config;
use yii\helpers\Html;
use quoma\media\components\view\PreviewAssets;

/**
 * Description of Image
 *
 * @author mmoyano
 */
class Youtube extends Media{
    
    public $extUrl;
    
    public function init()
    {
        parent::init();
        $this->type = 'Youtube';
    }
    
    public function rules()
    {
        return [
            [['title', 'extUrl'], 'required', 'on' => ['default']],
            [['status'], 'in', 'range'=>['enabled','disabled']],
            [['title'], 'string', 'max' => 140, 'on' => ['default','update']],
            [['description'], 'string', 'max' => 255, 'on' => ['default','update']],
            [['extUrl'], 'url', 'on' => ['default']],
        ];
    }

    public static function find()
    {
        return new Type(get_called_class(), ['type' => self::$type]);
    }
    
    /**
    * Devuelve el id del video de youtube (tinchosrok)
    * Dos alternativas reconocidas:
    *  1: http://youtu.be/[[ID]]
    *    devuelve [[ID]]
    *  2: http://www.youtube.com/watch?v=[[ID]]&feature=autoplay&list=PL921217C9C403AFA5&playnext=1
    *    devuelve [[ID]]
    *  3: http://www.youtube.com/embed/[[ID]]&feature=autoplay&list=PL921217C9C403AFA5&playnext=1
    *    devuelve [[ID]]
    */
    public function getYoutubeId(){
                
        //1
        if($pos = stripos($this->extUrl,'youtu.be/'))
                return trim(substr($this->extUrl,$pos+9));

        //2
        elseif(($pos = stripos($this->extUrl,'&v=')) || ($pos = stripos($this->extUrl,'?v='))){
                $amp_pos = stripos($this->extUrl,'&',$pos+1);
                if(empty($amp_pos))
                    $amp_pos = strlen ($this->extUrl);
                $lenght = $amp_pos - $pos - 3;
                return trim(substr($this->extUrl,$pos+3,$lenght));
        }
        
        //3
        elseif($pos = stripos($this->extUrl,'/embed/')){
                $amp_pos = stripos($this->extUrl,'&',$pos);
                if(empty($amp_pos))
                    $amp_pos = strlen ($this->extUrl);
                $lenght = $amp_pos - $pos - 7;
                return trim(substr($this->extUrl,$pos+7,$lenght));
        }

        //Si no es ninguna...
        return false;

    }
    
    /**
     * Devuelve una url con un thumbnail del tamanio especificado para el video actual.
     * @param string $size
     * @return string
     */
    public function getThumbnail($size){
        
        if(!in_array($size, array('xl','l','m','s')))
            throw new HttpException(500,'Bad size.');
        
        $map = array('xl'=>'maxres','l'=>'hq','m'=>'mq','s'=>'');
        $quality = $map[$size];
        
        return "http://i4.ytimg.com/vi/".(!empty($this->extUrl)? $this->getYoutubeId(): $this->relative_url)."/{$quality}default.jpg";
        
    }

    public function render($width = null, $height = null, $options = [])
    {
        
        if(isset($options['api']) && $options['api'] == true){
            unset($options['api']);
            return $this->renderJs($width, $height, $options);
        }
        
        return '<iframe width="'.$width.'" height="'.$height.'" src="https://www.youtube.com/embed/'.$this->relative_url.'?modestbranding=1&playlist='.$this->relative_url.'&showinfo=0&rel=0&color=white" frameborder="0" allowfullscreen></iframe>';
    
    }
    
    /**
     * JS Api
     * Para agregar llamada a eventos, incluir 'events' => [...] en $options:
     *  
     * events => [
     *  'onReady' => '...',
     *  'onStateChange' => '...',
     *  'onPlaybackQualityChange' => '...',
     *  'onPlaybackRateChange' => '...',
     *  'onError' => '...',
     *  'onApiChange' => '...',
     * ]
     * 
     * Referencia eventos:
     * https://developers.google.com/youtube/iframe_api_reference?hl=es-419#Events
     * 
     * Opciones parámetros:
     * playerVars => [
     *  'autoplay' 0|1
     *  'color' (color de barra de estado)
     *  'controls' 0|1|2
     *  'disablekb' (desactivar controles de teclado) 0|1
     *  'end' int
     *  'fs' (pantalla completa) 0|1
     *  'loop' 0|1
     *  'modestbranding' (ocultar logo)
     *  'playlist' (lista de videos adicionales; agregar el mismo id del video para loop)
     *  'start' int
     *  'showinfo' 0|1
     * ]
     * 
     * Referencia parámetros:
     * https://developers.google.com/youtube/player_parameters?hl=es-419#Parameters
     * 
     * 
     * ID de video: 
     * El id del video se forma con la palabra "player" seguida del id de media (media_id).
     * Ejemplo: player1988; en código podría ser "player$media->media_id"
     * 
     * @param type $width
     * @param type $height
     * @param type $options
     * @return type
     */
    public function renderJs($width = '100%', $height = '100%', $options = [])
    {
        
        $api_js = <<<JS
var tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
JS;
        Yii::$app->view->registerJs($api_js, \yii\web\View::POS_BEGIN, 'youtube_api_js');
        
        $options = json_encode(array_merge($options, [
            'width' => $width,
            'height' => $height,
            'videoId' => $this->relative_url,
            'enablejsapi' => true
        ]));
        
        $player_js = <<<JS
var player$this->media_id;
function onYouTubeIframeAPIReady() {
  player$this->media_id = new YT.Player('player$this->media_id', $options);
}
JS;
        
        Yii::$app->view->registerJs($player_js, \yii\web\View::POS_END);
        
        return "<div id='player$this->media_id'></div>";
        
    }
    
    public function renderPreview()
    {
        $assets = PreviewAssets::register(Yii::$app->view);
        $logo = Html::img($assets->baseUrl.'/img/youtube-full.png', ['class' => 'youtube-logo']);
        
        return Html::img($this->getThumbnail('m'), ['style' => 'margin: 30px 0;']).'<p>'.$logo.'</p>';
    }
    
    public function fields() {
        $fields = parent::fields();
        
        return array_merge($fields, [
            'thumbnail' => function($model){
                return $model->getThumbnail('s');
            }
        ]);
    }
    
    public function renderButton($options = ['class' => 'btn btn-success'], $params = [])
    {
        
        $options['data-media-action'] = $this->type;
        $options['data-media-url'] = \yii\helpers\Url::to(array_merge(['/media/youtube/create'], $params));
        return \yii\helpers\Html::a('<span class="glyphicon glyphicon-plus"></span> '.$this->getName(), "#$this->type", $options);
        
    }
    
    public function beforeSave($insert) {
        parent::beforeSave($insert);
        
        if($insert){
            $this->relative_url= $this->getYoutubeId();
        }
        
        return true;
    }
    
    public function getUrl()
    {
        return 'http://youtube.com/watch?v='.$this->relative_url;
    }

}
