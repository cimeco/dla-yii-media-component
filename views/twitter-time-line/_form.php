<?php

?>

<div class="timeline-form">
    
    <?php $form = \yii\bootstrap\ActiveForm::begin(['id'=>'timeline-form' ,'enableAjaxValidation' => true])?>
    
    <?= $form->field($model, 'relative_url')->textInput(['placeholder' => 'https://twitter.com/xxxx'])->label(Yii::t('app', 'Tweet Url'))?>
       
    <?= yii\bootstrap\Html::submitButton(Yii::t('app', 'Add Timeline'), ['class' => 'btn btn-primary', 'id' => 'submit-modal-form'])?>
    <?php \yii\bootstrap\ActiveForm::end()?>
</div>



