<?php

?>

<div class="media-search">
    <?php $form = yii\bootstrap\ActiveForm::begin(['id' => 'search-form', 'method' => 'GET'])?>
    
    <?php echo $form->field($search, '_search', 
            ['inputTemplate' => '<div class="input-group">{input}<a id="btn-search" class="btn btn-primary input-group-addon"><span class="glyphicon glyphicon-search"></span></a></div>'])->textInput(['id'=>'search_media_input']);?>
    
    <?php yii\bootstrap\ActiveForm::end()?>
</div>

<script>

    var SearchForm = new function(){
        
        this.init = function(){
            <?php if(!$embedded):?>
                $(document).on('click', '#btn-search', function(e){
                   e.preventDefault();
                   $('#search-form').submit();
                });
           <?php endif;?>
            $('#search-form').off('submit');   
        }
        
        
        
    }


</script>

<?php $this->registerJs('SearchForm.init()')?>