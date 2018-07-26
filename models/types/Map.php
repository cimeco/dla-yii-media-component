<?php

namespace quoma\media\models\types;

use quoma\media\components\icons\IconAsset;
use quoma\media\components\map\MapAsset;
use quoma\media\models\Data;
use quoma\media\models\Media;
use Yii;
use yii\helpers\Url;

/**
 * Description of Map
 *
 * @author juan
 */
class Map extends Media{
    
    public $_markers;
    public $_center_lat;
    public $_center_lng;
    public $_zoom;
    
    public function rules() {
        return [
            [['_markers', '_center_lat', '_center_lng', '_zoom'], 'safe' ,'on' => ['default','update']],
        ];
    }
    public function init() {
        parent::init();
        $this->type= 'Map';
    }
   
    public function render($width = 300, $height = 300, $options = Array()){
        
        $this->getDataMap();
        
        $coord= new \dosamigos\google\maps\LatLng(['lat' => $this->_center_lat, 'lng' => $this->_center_lng]);
        $map = new \quoma\media\components\map\Map(['center' => $coord, 'zoom' => (int)$this->_zoom !== 0 ? (int)$this->_zoom : 17, 'width' => $width, 'height' => $height , 'containerOptions' => $options]);
        
        if(!empty($this->_markers)){// Verifico que el mapa tenga marcadores configurados
            foreach ($this->_markers as $m){
                $marker= new \dosamigos\google\maps\overlays\Marker([
                    'position' => new \dosamigos\google\maps\LatLng(['lat' => $m['lat'], 'lng' => $m['lng']]),
                    'title' => $m['description'],
                ]);

                $map->addOverlay($marker);
            }
        }
        
        return  $map->display();
        
    }
    
    public function renderPreview(){
        $icons_asset = Yii::$app->assetManager->getBundle(IconAsset::className());
        return '<div class="col-lg-12" style="text-align: center; border-bottom: #c9c9c9 solid 1px; height: 75%">
                    <img src="'.$icons_asset->baseUrl.'/'.'google_maps_icon.jpg" width="50%" height="75%"> 
                </div>
                <div class="col-lg-12" style="background-color: #fefefe; height: 25%">
                        <h4>'.$this->title.'</h4>
            </div>';
    }
    
    
    public function renderButton($options = [], $params = []) {
        $options['data-media-action'] = $this->type;
        $options['data-media-url'] = Url::to(array_merge(['/media/map/create'], $params));
        MapAsset::register(Yii::$app->view);
        return \yii\helpers\Html::a('<span class="glyphicon glyphicon-plus"></span> '.$this->getName(), "#$this->type", $options);
    }
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        if($insert){
            $center_lat= new Data(['media_id' => $this->media_id, 'attribute' =>'center_lat', 'value' => $this->_center_lat]);
            $center_lat->save();
            $center_lng= new Data(['media_id' => $this->media_id, 'attribute' =>'center_lng', 'value' => $this->_center_lng]);
            $center_lng->save();
            $zoom= new Data(['media_id' => $this->media_id, 'attribute' =>'zoom', 'value' => $this->_zoom]);
            $zoom->save();

            if ($this->_markers) {
                foreach ($this->_markers as $key => $mark) {
                    $m_lat= new Data(['media_id' => $this->media_id, 'attribute' =>"mark-lat-$key", 'value' => $mark['lat']]);
                    $m_lat->save();
                    $m_lng= new Data(['media_id' => $this->media_id, 'attribute' =>"mark-lng-$key", 'value' => $mark['lng']]);
                    $m_lng->save();
                    $m_description= new Data(['media_id' => $this->media_id, 'attribute' =>"mark-description-$key", 'value' => $mark['description']]);
                    $m_description->save();
                }
            }
        }
    }
    
    
    
    /**
     * Recupera los datos del mapa guardados en BD 
     */
    public function getDataMap(){
        $data= $this->data;
        
        foreach ($data as $d){
            if ($d->attribute === 'center_lat') {
                $this->_center_lat = $d->value;
            }elseif ($d->attribute === 'center_lng'){
                $this->_center_lng = $d->value;
            }elseif ($d->attribute === 'zoom'){
                $this->_zoom = $d->value;
            }else{
                $attr= explode('-', $d->attribute);
                if ($attr[0] === 'mark') {
                    $this->_markers[$attr[2]][$attr[1]] = $d->value;
                }
            }
        }
    }
}
