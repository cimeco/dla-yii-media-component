<?php

namespace quoma\media\models\types;

use quoma\media\components\icons\IconAsset;

/**
 * Description of Twitter
 *
 * @author juan
 */
class Twitter extends \quoma\media\models\Media{
    
    public $tweet;
    
    public function init()
    {
        parent::init();
        $this->type= 'Twitter';
    }


    public function rules() {
        return [
            [['relative_url'] , 'url'],
            [['relative_url'], 'required','on' => ['default']],
            ['relative_url',  'validateTwitterUrl', 'skipOnEmpty' => false, 'skipOnError' => false,'on' => ['default']],
            [['title'], 'string', 'max' => 140,'on' => ['default','update']],
            [['description'], 'string', 'max' => 255,'on' => ['default','update']],
        ];
    }
    
    public function validateTwitterUrl($attribute, $params){
        if (!strpos($this->relative_url, 'twitter')) {
            $this->addError('relative_url', \Yii::t('app', 'The url is not a valid Twitter Url'));
            return false;
        }
        
        return true;    
    }

    public function renderButton($options = [], $params = []) {
        $options['data-media-action'] = $this->type;
        $options['data-media-url'] = \yii\helpers\Url::to(array_merge(['/media/twitter/create'], $params));
        return \yii\helpers\Html::a('<span class="glyphicon glyphicon-plus"></span> '.$this->getName(), "#$this->type", $options);
    }
    
    public function render($width = null, $height = null, $options = [])
    {
        return $this->getTweet();
    }

    public function renderForBack(){
        $icons_asset = \Yii::$app->assetManager->getBundle(IconAsset::className());
        return '<div class="col-lg-12" style="text-align: center; border-bottom: #c9c9c9 solid 1px; height: 75%">
                    <img src="'.$icons_asset->baseUrl.'/'.'twitter_icon.gif" width="50%" height="100%"> 
                </div>
                <div class="col-lg-12" style="background-color: #fefefe; height: 25%">
                        <h4>'.$this->title.'</h4>
            </div>';
    }
    
    public function renderPreview()
    {
        $icons_asset = \Yii::$app->assetManager->getBundle(IconAsset::className());
        return '<div class="col-lg-12" style="text-align: center; border-bottom: #c9c9c9 solid 1px; height: 75%">
                    <img src="'.$icons_asset->baseUrl.'/'.'twitter_icon.gif" width="50%" height="75%"> 
                </div>
                <div class="col-lg-12" style="background-color: #fefefe; height: 25%">
                        <h4>'.$this->title.'</h4>
            </div>';
    }
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        if($insert){
            $this->tweetInfo();
        }

        return true;
    }

    public function tweetInfo(){
        
        $ch= curl_init('https://publish.twitter.com/oembed?url='.$this->relative_url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $response = curl_exec($ch);
        
        $response = json_decode($response);
        
        return $this->removeEmojis(utf8_encode($response->html));
        
    }
    
    public function getTweet(){


        return $this->tweetInfo();
    }

    /**
     * Devuelvo el codigo del tweet sin emojis, para evitar excepciones en la base de datos
     * @param $string
     * @return string
     */
    private function removeEmojis( $string ) {
        $string = preg_replace('/[^\s.\/A-Za-z0-9<>="-]/','',$string);
        return $string;
    }
}
