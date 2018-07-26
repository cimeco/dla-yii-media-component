<div class="col-md-6 col-xs-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo $model->getName() ?></h3>
        </div>
        <div class="panel-body">
            <div class="preview-img-container">
                <div class="col-lg-5 col-md-12 col-sm-6 col-xs-12">
                    <div class="thumbnail" style="overflow:hidden;">
                        <?php echo $model->renderPreview(400) ?>     
                    </div>
                </div>
                <div class="col-lg-7 col-md-12 col-sm-6 col-xs-12">
                    <div class="col-lg-12">
                        <h4><?php echo $model->title ?></h4>
                        <p><?php echo $model->description ?></p>
                    </div>
                   
                    <div class="col-lg-12">
                        <p><?php
                            echo \yii\bootstrap\Html::a('<span class="glyphicon glyphicon-eye-open"></span> '. Yii::t('app','View'), yii\helpers\Url::to(['/media/default/view', 'id'=> $model->media_id]), ['class' => 'btn btn-primary']). ' ';
                            echo \yii\bootstrap\Html::a('<span class="glyphicon glyphicon-trash"></span> '. Yii::t('app','Delete'), yii\helpers\Url::to(['/media/default/delete', 'id'=> $model->media_id]), ['class' => 'btn btn-danger',
                                'data' => [
                                    'confirm' => Yii::t('app','Are you sure you want to delete this item?'),
                                    'method' => 'post',
                                ],]);
                            
                            if($model->type == 'Image'){
                                echo ' ' . $model->customButtons();
                            }
                        ?>
                        </p>
                    </div>
                </div><br>
                
                
            </div>
        </div>
    </div>
</div>