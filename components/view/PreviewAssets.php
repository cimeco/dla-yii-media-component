<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace quoma\media\components\view;

use yii\web\AssetBundle;

/**
 * 
 */
class PreviewAssets extends AssetBundle
{
    public $sourcePath = __DIR__.'/assets';
    public $css = [
        'css/preview.less'
    ];
    public $js = [
    ];
    public $depends = [
        'yii\jui\JuiAsset'
    ];
}
