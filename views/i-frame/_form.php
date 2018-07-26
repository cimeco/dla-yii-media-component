<?php
use yii\bootstrap\ActiveForm;
?>

<div class="iframe-form">
    
    <?php $form = ActiveForm::begin(['id'=>'iframe-form' ,'enableAjaxValidation' => true])?>

        <?= $form->field($model, 'title')->textInput()->label(Yii::t('app', 'Title'))?>

        <?= $form->field($model, 'relative_url')->textInput(['placeholder' => 'http://www.example.com'])->label(Yii::t('app', 'Url'))?>

        <?= yii\bootstrap\Html::submitButton(Yii::t('app', 'Add IFrame'), ['class' => 'btn btn-primary', 'id' => 'submit-modal-form'])?>
    
    <?php ActiveForm::end()?>
</div>

