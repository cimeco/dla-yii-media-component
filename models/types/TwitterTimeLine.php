<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace quoma\media\models\types;

/**
 * Description of TwitterTimeLine
 *
 * @author juan
 */
class TwitterTimeLine extends \quoma\media\models\Media {
    
    public function init()
    {
        parent::init();
        $this->type= 'TwitterTimeLine';
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
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        
        if($insert){
            $this->timeLineInfo();
        }
        
        return true;
    }
    
    public function timeLineInfo(){
        
        $ch= curl_init('https://publish.twitter.com/oembed?url='.$this->relative_url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $response = curl_exec($ch);
        
        $response = json_decode($response);
        
        $timeline_attr = new \quoma\media\models\Data();
        $timeline_attr->media_id = $this->media_id;
        $timeline_attr->attribute = 'timeLine';
        $timeline_attr->value= $response->html;
        
        $timeline_attr->save();
        
    }
    
    public function getTimeLine(){
        $tweet= \quoma\media\models\Data::findOne(['media_id' => $this->media_id, 'attribute' => 'timeLine']);
        
        return $tweet->value;
    }

    public function renderButton($options = [], $params = []){
        $options['data-media-action'] = $this->type;
        $options['data-media-url'] = \yii\helpers\Url::to(array_merge(['/media/twitter-time-line/create'], $params));
        return \yii\helpers\Html::a('<span class="glyphicon glyphicon-plus"></span> '.$this->getName(), "#$this->type", $options);
    }
    
    public function render($width = null, $height = null, $options = [])
    {
        return $this->getTimeLine();
    }
    
    public function renderPreview()
    {
        return '<div class="col-lg-12" style="text-align: center; border-bottom: #c9c9c9 solid 1px; height: 75%">
                     <h3> Twitter Timeline </h3>            
                 </div>
                 <div class="col-lg-12" style="background-color: #fefefe; height: 25%">
                         <h4>'.$this->relative_url.'</h4>
                 </div>'; 
    }
    
    
}
