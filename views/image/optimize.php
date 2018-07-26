<?php 
$lastStep = ($step == (count($steps)-1)) ? true : false;
?>

<div class="image-optimize">
    <div class="row">
        <div class="col-sm-12" style="text-align: center;">
            
            <div class="row bs-wizard" style="border-bottom:0;">
                
                <?php foreach($steps as $stepNumber => $stepData): 
                    
                    $stepClass = 'disabled';
                    if($stepNumber == $step){
                        $stepClass = 'active';
                    }elseif($stepNumber < $step){
                        $stepClass = 'complete';
                    }
                    ?>
                <div class="col-xs-<?= (int)(12/count($steps)) ?> bs-wizard-step <?= $stepClass ?>">
                    <div class="text-center bs-wizard-stepnum"><?= $stepData['name'] ?></div>
                    <div class="progress"><div class="progress-bar"></div></div>
                    <a href="#" class="bs-wizard-dot"></a>
                    <div class="bs-wizard-info text-center"><?= Yii::t('app', 'Step {number}', ['number'=>$stepNumber+1]) ?></div>
                </div>
                <?php endforeach; ?>
                 
            </div>
            
            <?php 
            /**
            <ol class="carousel-indicators optimize-indicators pull-right">
                <?php foreach($steps as $stepNumber => $stepData): ?>
                <li data-step="<?= $stepNumber ?>" class="<?= ($stepNumber == $step) ? 'active' : '' ?>"><?= $stepData['name'] ?></li>
                <?php endforeach; ?>
            </ol>
            */
            ?>
            
            <div class="col-sm-12">
                <div class="optimizer-container" style="position:relative; overflow: hidden; display: inline-block;" 
                     data-min-width="<?= $steps[$step]['minWidth'] ?>" 
                     data-min-height="<?= $steps[$step]['minHeight'] ?>"
                     data-aspect="<?= $steps[$step]['aspect'] ?>">
                    <div data-cutter style="position:absolute; z-index: 100000; cursor: move;"></div>
                    <img id="img2optimize" src="<?= $model->url ?>" style="max-width: 100%;" />
                </div>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <hr/>
        </div>
        <div class="col-sm-9">
            <a data-popover data-trigger="focus" data-placement="top" tabindex="0" class="btn" role="button" title="<?= Yii::t('app', 'Help') ?>" 
               data-content="Con esta herramienta usted podrá optimizar el tamaño y las proporciones de las imagenes, para que se vean bien en todo el sitio y 
               desde cualquier dispositivo. Para esto, ubique el costado o la esquina del rectángulo de corte (si no lo visualiza, 
               es posible que coincida con el borde de la imagen). Podrá mover, ampliar y reducir el rectángulo de corte hasta ver 
               la porción de imagen adecuada. Luego presione 'Siguiente' o 'Finalizar'. Repita estos pasos para todos los tamaños
               de imagen solicitados."><span class="glyphicon glyphicon-question-sign"></span> <?= Yii::t('app', 'Need help?') ?></a>
        </div>
        <div class="col-sm-3">
            <div class="pull-right">
                <?php if($model->isOptimized()): ?>
                    <?php if($lastStep): ?>
                    <a href="#Skip" class="btn btn-default" onclick="Media.close()">
                        <?= '<span class="glyphicon glyphicon-remove-sign"></span> '.Yii::t('app', 'Skip') ?>
                    </a>
                    <?php else: ?>
                    <a href="#Skip" class="btn btn-default" data-media-action data-media-url="<?= yii\helpers\Url::to(['/media/image/optimize','id'=>$model->media_id,'step' => $lastStep ? -1 : ($step+1)]) ?>">
                        <?= '<span class="glyphicon glyphicon-remove-sign"></span> '.Yii::t('app', 'Skip') ?>
                    </a>
                    <?php endif; ?>

                <?php endif; ?>
                <a data-optimize-send href="#Cut" class="btn btn-primary" data-url="<?= yii\helpers\Url::to(['/media/image/optimize', 'id' => $model->media_id, 'step' => $step]) ?>">
                    <?= $lastStep ? '<span class="glyphicon glyphicon-check"></span> '.Yii::t('app', 'Finish') : Yii::t('app', 'Next').' <span class="glyphicon glyphicon-arrow-right"></span>' ?>
                </a>
            </div>
        </div>
    </div>
</div>
<script>
ImageOptimizer.init();
</script>