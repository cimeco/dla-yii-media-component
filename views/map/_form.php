<?php

?>

<style>
    .pac-container {
        z-index: 10000;
    }
</style>

<div class="map-form">
    
    <?php $form= yii\bootstrap\ActiveForm::begin(['id' => 'form-map'])?>
    
    <?php echo $form->field($model, 'title')->textInput()?>
    
    <hr>
        <h5 style="font-style: italic"><?php echo Yii::t('app', 'Click on the map or enter a location to add a marker to the map')?></h5>
    <div class="row">
        <div class="marker-opt">
            <div class="col-lg-5">
                <div class="input-group">
                    <input type="text" name="mark_description" id="map_search" class="form-control" style="width: 450px; z-index:1000">
                    <a href="#" class="input-group-addon"><span class="glyphicon glyphicon-search"></span></a>
                </div>
            </div>

            <div id="listing">
                <table id="resultsTable">
                  <tbody id="results"></tbody>
                </table>
            </div>
        </div>
    </div>
    <br><br>
    <div class="row">
        <div class="col-lg-7">
            <div id="map-canvas"  style="width: 500px; height: 300px; padding-bottom: 3px; color: "></div>
        </div>
        <div class="col-lg-4" id="scroll-div" style="overflow: scroll; height: 300px;">
            <h4> <?php echo Yii::t('app', 'Markers')?></h4>
            <hr>
            <ul id="marker-list">

            </ul>
        </div>
    </div>
    <div class="row">
        <br>
        <input type="hidden" name="Map[_center_lat]" value="-32.8912173">
        <input type="hidden" name="Map[_center_lng]" value="-68.8394868">
        <input type="hidden" name="Map[_zoom]" value="18">
        <div class="col-lg-2">
            <?php echo \yii\bootstrap\Html::submitButton(Yii::t('app', 'Add Map'), ['class' => 'btn btn-primary', 'id' => 'submit-modal-form'])?>
        </div>
    </div>
    
    <?php yii\bootstrap\ActiveForm::end()?>
    
    
</div>

<!--<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php //echo quoma\modules\config\models\Config::getValue('google_maps_api_key')?>&libraries=places&callback=initMap"></script>-->
<?php $this->registerJs('MapForm.init()', \yii\web\View::POS_LOAD)?>