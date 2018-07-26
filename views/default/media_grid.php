<?php

use quoma\media\components\view\Preview;
use yii\widgets\LinkPager;
?>

<div class="media-grid">
    
    <?php echo $this->render('_search_form', ['search' => $searchModel, 'embedded' => true])?>
    <?php if(count($media)> 0):?>    
    
    <div class="row">
    <?php foreach ($media as $key => $m):?>
        <?=Preview::widget([
            'update' => true,
            'media' => $m,
            'containerOptions' => ['class' => 'col-lg-4'],
            'buttonsTemplate' => '{insert}',
            'showControls' => false
        ]);?>        
    <?php endforeach;?>   
    </div>
        
    <?php else:?>
            <div class="row">
                <div class="col-lg-12">
                    <h3>No se encuentran datos para mostrar</h3>
                </div>
            </div>
    <?php endif;?>
</div>

       
            