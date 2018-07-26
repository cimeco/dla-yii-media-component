<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace quoma\media\components\map;


use Yii;
use yii\web\View;

/**
 * Description of Map
 *
 * @author juan
 */
class Map extends \dosamigos\google\maps\Map {
    
    
    public function registerClientScript($position = View::POS_END) {
       $view = Yii::$app->getView();
       MapAsset::register($view);

       $view->registerJs($this->getJs(), $position);
    }
       
    public function display()
    {
        $this->registerClientScript();

        return $this->renderContainer();
    }
    
}
