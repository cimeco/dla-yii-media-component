<?php
use yii\helpers\Html;
use quoma\media\components\view\Preview;
use quoma\media\components\helpers\MediaFactory;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\media\models\types\search\MediaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Media');
$this->params['breadcrumbs'][] = $this->title;

\quoma\media\components\paginator\PaginatorAsset::register($this);
?>
<div class="image-index" data-search-box>

    <?php
    $tabs= [];
    foreach($showTypes as $typeName){
        $type = MediaFactory::getObject($typeName);
        $tabs[]= [
            'label' => $type->getName(),
            'url' => '#',
            'linkOptions' => [
                'data-type' => $type->type
            ],
            'active' => $searchModel->type == $type->type ? true : false
        ];
    }
    ?>
        
    <?= yii\bootstrap\Tabs::widget(['items' => $tabs, 'linkOptions' => ['class' => 'tab']])?>

    <div class="media-grid">

        <?php 
        echo $this->render('_search_form', ['search' => $searchModel, 'embedded' => true]);
        if(count($dataProvider->getModels()) > 0):
        ?>    

        <div class="row">
        <?php foreach ($dataProvider->getModels() as $key => $media){
            echo Preview::widget([
                'mode' => Preview::MODE_SEARCH,
                'media' => $media
            ]);
        }
        ?>   
        </div>

        <?php else:?>
            <div class="row">
                <div class="col-lg-12">
                    <h3>No se encuentran datos para mostrar</h3>
                </div>
            </div>
        <?php endif;?>
    </div>

    <div id="paginator">
        <ul id="m-pagination" class="pagination"></ul>
    </div>
    
</div>