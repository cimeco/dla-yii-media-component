<div class="image-optimize">
    <div class="row">
        <div class="col-sm-12" style="text-align: center;">
            
            <div class="col-sm-12">
                <?php 
                
                $colMdWidth = count($steps) < 3 ? 6 : 4;
                
                foreach($steps as $stepNumber => $step):
                    $width = $step['minWidth'];
                    $height = $width / $step['aspect'];
                    ?>
                
                    <div class="col-xs-6 col-md-<?= $colMdWidth ?>">
                        <a href="#" data-media-action data-media-url="<?= yii\helpers\Url::to(['/media/image/optimize', 'id' => $model->media_id, 'step' => $stepNumber]) ?>" class="thumbnail">
                        <?= yii\helpers\Html::img($model->getSizedUrl((int)$width, (int)$height));?>
                            <div class="caption">
                                <h3><?= $step['name'] ?></h3>
                                <p><?= Yii::t('app', 'Click to edit') ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <hr/>
        </div>
        <div class="col-sm-9">
            <a data-popover data-trigger="focus" data-placement="top" tabindex="0" class="btn" role="button" title="<?= Yii::t('app', 'Help') ?>" 
               data-content="En esta pantalla usted puede visualizar los recortes previamente generados. Puede optar
               por actualizar un recorte o todos."><span class="glyphicon glyphicon-question-sign"></span> <?= Yii::t('app', 'Need help?') ?></a>
        </div>
        <div class="col-sm-3">
            <a data-media-action href="#Cut" class="btn btn-primary pull-right" data-media-url="<?= yii\helpers\Url::to(['/media/image/optimize', 'id' => $model->media_id, 'step' => 0]) ?>">
                <?= Yii::t('app', 'Optimize again').' <span class="glyphicon glyphicon-arrow-right"></span>' ?>
            </a>
        </div>
    </div>
</div>
