<?php

namespace quoma\media\components\image;

use yii\web\AssetBundle;

/**
 * 
 */
class OptimizerAssets extends AssetBundle
{
    public $sourcePath = __DIR__.'/optimizer-assets';
    public $css = [
        'css/styles.css'
    ];
    public $js = [
        'js/ImageOptimizer.js',
    ];
    public $depends = [
        'yii\jui\JuiAsset',
        'quoma\core\assets\bootstrap\BootstrapComponetsAssets'
    ];
}
