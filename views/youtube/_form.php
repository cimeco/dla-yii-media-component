<?php
use yii\bootstrap\ActiveForm;
?>

<div class="youtube-form">
    <?php $form = ActiveForm::begin(['id'=> 'youtube-form', 'enableAjaxValidation' => true])?>
    
        <?= $form->field($model, 'title')->textInput() ?>

        <?= $form->field($model, 'extUrl')->textInput(['placeholder'=> "https://www.youtube.com/watch?v=IrAhGWwmKnY"]) ?>

        <?= yii\bootstrap\Html::submitButton(Yii::t('app', 'Add Youtube Video'), ['class' => 'btn btn-primary', 'id' => 'submit-modal-form'])?>
    
    <?php ActiveForm::end()?>
</div>

