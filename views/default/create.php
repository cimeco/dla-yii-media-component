<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\media\models\types\search\MediaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'New Media');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="image-index">

    <div class="title">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>
    
    <?php $form = ActiveForm::begin(['id' => 'update-all', 'action' => ['update-all']]); ?>
    
        <?php 
        $types = quoma\media\components\helpers\MediaFactory::getTypes();
        sort($types);
        echo \quoma\media\components\widgets\Buttons::widget([
            'media' => [],
            'types' => $types,
            'searchButton' => false,
            'previewOptions' => [
                'buttonsTemplate' => '{insert} {custom}'
            ]
        ]);
        ?>
    
        <div data-save-all class="btn btn-primary"><?= Yii::t('app', 'Save') ?></div>
    
    <?php ActiveForm::end(); ?>
    
    <hr/>
    
</div>

<script>
    var Update = new function(){
        this.init = function(){
            $('[data-save-all]').on('click', function(){
                save();
            });
        }
        
        function save(){
            $.ajax({
                url: $('#update-all').attr('action'),
                method: 'post',
                data: $('#update-all').serialize(),
                beforeSend: function(){
                    $('#update-all').css('opacity', 0.5);
                }
            })
            .done(function(response){
                if(response.status == 'success'){
                    $('#update-all').prepend('<div class="alert alert-success">'+response.message+'</div>');
                }else{
                    $('#update-all').prepend('<div class="alert alert-danger">'+response.message+'</div>');
                }
                
                setTimeout(function(){$('.alert').hide(500, function(){ $(this).remove(); })}, 4000);
            })
            .always(function(){
                $('#update-all').css('opacity', 1);
            });
        }
    }
</script>
<?php $this->registerJs('Update.init();') ?>