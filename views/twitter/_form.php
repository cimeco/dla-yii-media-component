<?php

?>

<div class="tweet-form">
    
    <?php $form = \yii\bootstrap\ActiveForm::begin(['id'=>'tweet-form' ,'enableAjaxValidation' => true])?>
    
    <?= $form->field($model, 'relative_url')->textInput(['placeholder' => 'https://twitter.com/xxxx/status/816697701593186304'])->label(Yii::t('app', 'Tweet Url'))?>

    <?= $form->field($model, 'title')->textInput()?>

    <?= $form->field($model, 'description')->textInput()?>

    
    <?= yii\bootstrap\Html::submitButton(Yii::t('app', 'Add Tweet'), ['class' => 'btn btn-primary', 'id' => 'submit-modal-form'])?>
    <?php \yii\bootstrap\ActiveForm::end()?>
</div>



