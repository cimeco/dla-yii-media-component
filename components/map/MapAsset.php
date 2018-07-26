<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace quoma\media\components\map;

/**
 * Description of MapAsset
 *
 * @author juan
 */
class MapAsset extends \yii\web\AssetBundle{

    public $sourcePath= __DIR__.'/js';
    public $options= [];
    
    public function init(){
        $this->options= [
            'key' => \quoma\modules\config\models\Config::getValue('google_maps_api_key'),
            'language' => 'ES',
            //'version' => '3.1.18',
            'libraries' => 'places'
        ];
        
        $this->js[] = 'https://maps.googleapis.com/maps/api/js?'. http_build_query($this->options);
        $this->js[]= 'map.js';
        
    }
}

