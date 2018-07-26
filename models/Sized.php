<?php

namespace quoma\media\models;

use Yii;
use quoma\modules\config\models\Config;

/**
 * This is the model class for table "data".
 *
 * @property integer $sized_id
 * @property integer $media_id
 * @property string $width
 * @property string $height
 * @property string $relative_url
 * @property string $base_url
 *
 * @property Media $media
 */
class Sized extends \quoma\core\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sized';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_media');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['media_id'], 'required'],
            [['media_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'sized_id' => Yii::t('app', 'Sized ID'),
            'media_id' => Yii::t('app', 'Media ID'),
            'width' => Yii::t('app', 'Width'),
            'height' => Yii::t('app', 'Height'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMedia()
    {
        return $this->hasOne(Media::className(), ['media_id' => 'media_id']);
    }
    
    public function getUrl(){
        
        if(Config::getValue('cdn') == true){
            $baseUrl = Config::getValue('cdn_base_url');
        }else{
            $baseUrl = $this->base_url;
        }
        
        return $baseUrl .'/'. $this->relative_url;
        
    }
    
    public function beforeSave($insert) 
    {
        $this->base_url = str_replace(['http:','https:'],'',Yii::$app->frontendUrlManager->getBaseUrl());
        
        return parent::beforeSave($insert);
    }
    
    /**
     * Antes de eliminar el registro de la base de datos, elimino el archivo del disco
     * @return type
     */
    public function beforeDelete() {
        parent::beforeDelete();
        
        $filename = $this->media->getBasePath().DIRECTORY_SEPARATOR.$this->relative_url;
        if(file_exists($filename) && is_file($filename)){
            return unlink($filename);
        }
        
        return false;
    }
}
