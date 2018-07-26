<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\modules\media\models\types\Image */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Images'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('.img-container{ 
        max-width: 100%; 
        max-height: 100%;
        overflow: hidden;
    } 
    .img-container img{
        margin: 10px;
        max-width: 100%;
        max-height: 100%;
    }
    .cut-preview{
        height: 400px;
    }
    .img-container.center{
        text-align: center;
    }
    @-moz-document url-prefix() {
        img { width: 100%; max-width: -moz-max-content; }
    }
    @media (min-width: 1024px){
        .main-container{
            height: 600px;
            margin-bottom: 20px;
        }
    }
'
)
?>
<div class="image-view">

    <div class="title">
        <h1><?= Html::encode($this->title) ?></h1>

        <p>
            <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->media_id], ['class' => 'btn btn-primary']) ?>
            <?php if($model->deletable) echo Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->media_id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]) ?>
        </p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="img-container center main-container">
            <?php echo $model->render()?>
            </div>
        </div>
        <div class="col-sm-6">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'media_id',
                    'title',
                    'description',
                    'name',
                    'url:url',
                    'relative_url:url',
                    'type',
                    'mime',
                    'size:shortSize',
                    'width',
                    'height',
                    'extension',
                    'create_date',
                    'create_time',
                    'create_timestamp:datetime',
                    'status',
                ],
            ]) ?>
        </div>
    </div>
    
</div>
