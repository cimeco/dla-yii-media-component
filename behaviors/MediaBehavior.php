<?php

namespace quoma\media\behaviors;

use quoma\media\models\Media;
use quoma\media\models\ModelHasMedia;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\HttpException;

/**
 * Description of MediaBehavior
 * MediaBehaviour debe ser incorporado en una clase que requiera Multimedia.
 * Agrega un setter y un getter para media (getMedia y setMedia).
 * Escucha el evento onAfterSave para guardar los recursos multimedia vinculados
 * al objeto.
 *
 * @author martin
 */
class MediaBehavior extends Behavior{
    
    private $_media = [];
    
    private $_types = [];
    
    public $mediaPreviewOptions = [];
    
    public $modelClass = null;
    
    /**
     * Si se deben capturar los eventos EVENT_AFTER_UPDATE y EVENT_AFTER_INSERT
     * para intentar obtener automaticamente los datos a asociar al modelo
     * desde POST('Media')
     */
    public $captureEvents = true;
    
    public function events()
    {
        if($this->captureEvents == false){
            return [];
        }
        
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'afterValidate',
        ];
    }
    
    /**
     * Establece una relación entre el modelo y media
     * @return Query
     * @throws \yii\web\HttpException
     */
    public function getMedia($type = null){

        /**
         * Si los medias ya estan cargados, los devolvemos aplicando el filtro de tipo, para evitar
         * ejecutar siempre la consulta a base de datos
        **/
        if(!empty($this->_media)){

            return array_values(array_filter($this->_media, function($media) use ($type){
                if($type === null){
                    return true;
                }
                if (!is_array($type)){
                    $type = [$type];
                }

                if(in_array($media->type,$type)){
                    return true;
                }

                return false;

            }));
        }
        
        $media= [];
        $key = $this->owner->primaryKey()[0];
        
        if(is_array($key)){
            throw new \yii\web\HttpException(500, 'Array keys not supported.');
        }
        
        if($this->modelClass){
            $class = $this->modelClass;
        }else{
            $class = $this->owner->className();
        }
        
        //Realizamos un join, dado que necesitamos ordenar por un campo de la tabla usada para junction
        $query = Media::find();
        $query->where(['status' => 'enabled']);
        
        //ALL
        if(!is_array($type) && strtolower($type) != 'all' && !empty($type)){
            $query->andWhere(['type' => $type]);
        }elseif (is_array($type)){
            $query->andFilterWhere(['type' => $type]);
        }

        $query->multiple = true;
        $query->innerJoin('model_has_media', 'model_has_media.media_id = media.media_id AND model_has_media.model = :model', ['model' => $class]);
        $query->andWhere(['model_has_media.model_id' => $this->owner->primaryKey]);
        $query->orderBy(['model_has_media.order' => SORT_ASC]);
        
        foreach ($query->all() as $m){
            if($m['type'] !== null){
                $media[]= \quoma\media\components\helpers\MediaFactory::getMediaObject($m);
            }
        }
        
        
        return $media;
        
//      No ordena por junction table: (conservar este comentario para referencia futura)
//        return $this->owner->hasMany(Media::className(), ['media.media_id' => 'media_id'])
//                ->viaTable('media.model_has_media', ['model_id' => $key], function($query){ 
//                    $query->where(['model' => $this->owner->className()]); 
//                });
        
    }
    
    /**
     * I18n media
     * @param string $lang
     * @param string $type
     * @return array
     */
    public function getLangMedia($type = null, $lang = null)
    {
        $lang = $lang ? $lang : Yii::$app->language;
        
        $items = [];
        
        foreach($this->getMedia($type) as $media){
            if(strtolower($media->language) == strtolower($lang)){
                $items[] = $media;
            }
        }
        
        return $items;
    }


    /**
     * Setea los recursos multimedia al modelo
     * @param array $media
     * @throws \yii\web\HttpException
     */
    public function setMedia($media, $save = true){

        $key = $this->owner->primaryKey;
        if($this->modelClass){
            $class = $this->modelClass;
        }else{
            $class = $this->owner->className();
        }

        if(is_array($key)){
            throw new \yii\web\HttpException(500, 'Array keys not supported.');
        }

        if(is_array($media)){
            
            foreach ($media as $m){
                $this->_media[] = $m;
            }
            
        } else {
            //setMedia es una asignador masivo
            throw new \yii\web\HttpException(500, 'First param should be an array.');
            
        }      

        if($save === true){
            //Quitamos las relaciones actuales
            ModelHasMedia::deleteAll(['model_id'=>$key, 'model'=>$class]);

            //Guardamos las nuevas relaciones
            foreach ($this->_media as $order=>$m){
                $mhm = new ModelHasMedia();

                $mhm->media_id = $m->media_id;
                $mhm->model_id = $key;
                $mhm->model = $class;
                $mhm->order = $order;

                $mhm->save();
            }
        }
    }
    
    /**
     * Esta funcion es llamada en los eventos ActiveRecord::EVENT_AFTER_UPDATE y
     * ActiveRecord::EVENT_AFTER_INSERT. Busca Media en post y en caso de encontrar
     * datos, intenta setear los recursos multimedia al modelo que implementa
     * el behavior.
     * @param type $event
     */
    public function afterSave($event){
        
        $mediaIds = Yii::$app->request->post('Media');
        if(!empty($mediaIds)) {
            unset($mediaIds['file']);
            $mediaIds = array_values($mediaIds);
            if(is_array($mediaIds)){
                $media = array_map(function($id){
                    $item = Media::findOne($id);
                    if($item === null){
                        throw new \yii\web\HttpException(500, 'Media not found.');
                    }
                    return $item;
                }, $mediaIds);
            }else{
                $media = [];
            }
            
            $this->setMedia($media);
        }else{
            $key = $this->owner->primaryKey;
                if($this->modelClass){
                $class = $this->modelClass;
            }else{
                $class = $this->owner->className();
            }
            ModelHasMedia::deleteAll(['model_id'=>$key, 'model'=>$class]);
        }
        
        $this->saveMediaData();

    }
    
    /**
     * Guarda los datos de todos los recursos media al guardar el model owner.
     */
    private function saveMediaData()
    {
        $mediaData = Yii::$app->request->post('MediaData');
        if(is_array($mediaData)){
            
            foreach($mediaData as $key => $data){
                $model = Media::findOne($key);
                if($model){
                    $this->saveModelData($model, $data);
                }
            }
            
        }
    }
    
    /**
     * Guarda los datos de cada recurso media
     * @param Media $model
     * @param array $data
     */
    private function saveModelData($model, $data){
        
        foreach($data as $attr => $val){
            $model->$attr = $val;
        }

        $model->save(false);
        
    }
    
    /**
     * Setea y valida los datos de los recursos multimedia. Si la validación falla, 
     * se deben mostrar los media aún no guardados, por lo que buscamos en el post 
     * si hay algun recurso media y lo agregamos
     * al modelo actual.
     */
    private function setMediaData()
    {
        $mediaData = Yii::$app->request->post('MediaData');
        $mediaIds = Yii::$app->request->post('Media');
        
        if(is_array($mediaData) && is_array($mediaIds)){
            
            $medias = Media::find()->where(['media_id' => $mediaIds])->all();

            foreach($medias as $media){
                if(isset($mediaData[$media->media_id])){
                    $this->setModelData($media, $mediaData[$media->media_id]);
                }
            }

            return $medias;
        }
        
        return [];
        
    }
    
    /**
     * Setea y valida los datos de cada recurso multimedia. 
     * @param type $model
     * @param type $data
     * @return boolean
     */
    private function setModelData($model, $data){
        
        foreach($data as $attr => $val){
            $model->$attr = $val;
        }

        if(!$model->validate()){
            $this->owner->addError('media', Yii::t('app', 'There are some errors in media.'));
            return false;
        }
        
    }
    
    public function afterFind($event)
    {
    }
    
    /**
     * Si la validación falla, se deben mostrar los errores de media. Para poder
     * utilizar ActiveField, asignamos los objetos media validados al owner. 
     * 
     * @param Event $event
     */
    public function afterValidate($event){
        
        $media = $this->setMediaData();

        if($this->owner->hasErrors()){

            $this->owner->setMedia($media,false);
        }
        
    }
    
    public function getMediaTypes()
    {
        return $this->_types;
    }
    
    public function setMediaTypes($types)
    {
        $this->_types = $types;
    }

    /**
     * Devuelve los tipos de media asociado a un objeto en particular
     * @return array|ActiveRecord[]
     */
    public function getObjMediaTypes(){
        $model_id= $this->owner->primaryKey;
        $model= $this->modelClass;

        $query_type= Media::find()
            ->select(['type'])
            ->innerJoin('model_has_media mhm', 'mhm.media_id= media.media_id')
            ->andFilterWhere(['mhm.model_id' => $model_id, 'mhm.model' => $model])
            ->distinct()
            ->asArray();

        return $query_type->all();

    }
}
