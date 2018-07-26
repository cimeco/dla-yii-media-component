<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace quoma\media\components\upload;

use yii\web\AssetBundle;

/**
 * 
 */
class UploadAsset extends AssetBundle
{
    public $sourcePath = __DIR__.'/assets';
    public $css = [
        'jquery.fileupload.css'
    ];
    public $js = [
        'UploadWidget.js',
        //Bower??
        'jquery.fileupload.js',
        'jquery.iframe-transport.js'
    ];
    public $depends = [
        'yii\jui\JuiAsset',
        'quoma\media\components\widgets\ButtonsAssets'
    ];
    public $publishOptions = [
    ];
}
