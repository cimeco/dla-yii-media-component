<?php

?>

<div class="html-form">
    
    <?php $form = \yii\bootstrap\ActiveForm::begin(['id'=>'iframe-form' ,'enableAjaxValidation' => true])?>
    
    <?= $form->field($model, 'title')->textInput()->label(Yii::t('app', 'Title'))?>
    
    <?= $form->field($model, 'code')->textarea(['cols' => '30'])?>
    
    <?= yii\bootstrap\Html::submitButton(Yii::t('app', 'Add HTML'), ['class' => 'btn btn-primary', 'id' => 'submit-modal-form'])?>
    <?php \yii\bootstrap\ActiveForm::end()?>
</div>

