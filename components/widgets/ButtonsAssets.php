<?php

namespace quoma\media\components\widgets;

use yii\web\AssetBundle;

/**
 * 
 */
class ButtonsAssets extends AssetBundle
{
    public $sourcePath = __DIR__.'/assets';
    public $css = [
        'css/media.css'
    ];
    public $js = [
        'js/Media.js',
        'js/bootbox.min.js'
    ];
    public $depends = [
        'yii\jui\JuiAsset'
    ];
}
