<?php

use yii\helpers\Html;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\media\models\types\search\MediaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Media');
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('@media (min-width: 991px){ .thumbnail{ height: 260px; overflow: hidden; } }');

quoma\media\components\widgets\ButtonsAssets::register($this);
$this->registerJs('Media.init({editorSelector: "", previewOptions: {}});');

quoma\media\components\image\OptimizerAssets::register($this);
?>
<div class="image-index">

    <div class="title">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>
    
    <p>
        <?= Html::a('<span class="glyphicon glyphicon-plus"></span> '.Yii::t('app', 'New media'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    
    <?php
        $types= opendir(Yii::getAlias('@media/models/types'));
                
        $tabs= [
            [
                'label' => Yii::t('app', 'All'),
                'url' => \yii\helpers\Url::to(['/media/default/index']),
                
            ]
        ];
        
        while ($type= readdir($types)){
            if(!is_dir($type) && $type !== '.' && $type !== '..' && $type !== 'search'){
                $name= substr($type, 0, strlen($type)-4 );
                $tabs[]= [
                    'label' => $name,
                    'url' => \yii\helpers\Url::to(['/media/default/index', 'MediaSearch[type]' => $name]),
                    'active' => ($searchModel->type == $name ? true : false)
                ];
            }
        }
    
    ?>
    
    <p>
    <?= yii\bootstrap\Tabs::widget(['items' => $tabs])?>
    </p>
    
    <?php echo $this->render('_search_form', ['search' => $searchModel, 'embedded' => false])?>
    
    
    <div class="media-grid">

        <div class="row">
            <div class="col-lg-12">
                <?php echo ListView::widget([
                    'dataProvider' => $dataProvider,
                    'itemView' => '_view',
                    'layout' => "{summary}\n <div class='row'>{items}</div> \n{pager}"
                ]); ?>
            </div>
        </div>

    </div>

</div>
