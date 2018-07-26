<?php

$this->title = $model->type . ': ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Media'), 'url' => ['default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="media-view">
    
    <h1><?php echo $this->title ?></h1>
    
    <?php echo \yii\widgets\DetailView::widget([
        'model' => $model,
        'attributes' => [
            'title',
            'description',
            'relative_url',
            [
                'attribute' => 'create_timestamp',
                'value' => Yii::$app->formatter->asDatetime($model->create_timestamp, 'dd-MM-yyyy hh:mm:ss')
            ]
        ]
    ])?>
    
    <?= $model->render() ?>
    
</div>

