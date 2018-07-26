<?php

namespace quoma\media\models;

use Yii;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use quoma\modules\config\models\Config;
use quoma\media\MediaModule;

/**
 * This is the model class for table "media".
 *
 * @property integer $media_id
 * @property string $title
 * @property string $description
 * @property string $name
 * @property string $base_url
 * @property string $relative_url
 * @property string $mime
 * @property double $size
 * @property integer $width
 * @property integer $height
 * @property string $extension
 * @property string $create_date
 * @property string $create_time
 * @property integer $create_timestamp
 * @property string $status
 *
 * @property Data[] $datas
 * @property ModelHasMedia[] $modelHasMedia
 * @property Sized[] $sized
 */
class Media extends \quoma\core\db\ActiveRecord
{
 
    public $file;
    
    public static function instantiate($row)
    {
        
        $class = 'quoma\media\models\types\\'.$row['type'];
        
        if( class_exists($class) ){
            return new $class;
        }
        
        return new self;
        
    }
    
    public function init()
    {
        $this->status = 'enabled';
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'media';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_media');
    }

    public function behaviors()
    {
        return [
            'datestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_date'],
                ],
                'value' => function(){return date('Y-m-d');},
            ],
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],
                ],
                'value' => function(){return date('H:i');},
            ],
            'unix_timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_timestamp'],
                ],
            ],
        ];
    }
    
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'in', 'range'=>['enabled','disabled']],
            [['title'], 'string', 'max' => 140],
            [['description'], 'string', 'max' => 255],
            [['language'], 'string', 'max' => 10],
            [['file'], 'file', 'skipOnEmpty' => false, 'except' => 'update'],
        ];
    }
    
    /**
     * Devuelve los parametros configurados para este tipo de media
     * @return array
     */
    protected function getParams(){
        
        $params = Yii::$app->getModule('media')->params;
        
        $c = get_called_class();
        $c = new \ReflectionClass($c);
        $c = $c->getShortName();
        
        if($c && isset($params[$c])){
            return $params[$c];
        }
        
        return [];
        
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'media_id' => Yii::t('app', 'Media ID'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'name' => Yii::t('app', 'Name'),
            'base_url' => Yii::t('app', 'Base Url'),
            'relative_url' => Yii::t('app', 'Relative Url'),
            'url' => Yii::t('app', 'Url'),
            'class' => Yii::t('app', 'Class'),
            'mime' => Yii::t('app', 'Mime'),
            'size' => Yii::t('app', 'Size'),
            'width' => Yii::t('app', 'Width'),
            'height' => Yii::t('app', 'Height'),
            'extension' => Yii::t('app', 'Extension'),
            'create_date' => Yii::t('app', 'Create Date'),
            'create_time' => Yii::t('app', 'Create Time'),
            'create_timestamp' => Yii::t('app', 'Create Timestamp'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getData()
    {
        return $this->hasMany(Data::className(), ['media_id' => 'media_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModels()
    {
        return $this->hasMany(ModelHasMedia::className(), ['media_id' => 'media_id']);
    }
    
    public function getUrl(){
        
        if(Config::getValue('cdn') == true){
            $baseUrl = Config::getValue('cdn_base_url');
        }else{
            $baseUrl = $this->base_url;
        }
        
        return $baseUrl .'/'. $this->relative_url;
        
    }
    
    public function getDirectory()
    {
        
        $directory = 'uploads/'.date('Y').'/'.date('m').'/';
        
        return $directory;
    }
    
    public function getBasePath($end = 'frontend')
    {
        $basePath = '';
        try {
            if($end == 'frontend'){
                $basePath = Yii::getAlias('@frontend/web');
            }else{
                $basePath = Yii::getAlias('@backend/web');
            }
        } catch(\Exception $ex) {
            $basePath = Yii::getAlias('@app/web');
        }
        return $basePath;
    }
    
    public function upload($name = 'Media[file]')
    {
        if(!$this->file){
            $this->file = UploadedFile::getInstanceByName($name);
        }

        if ($this->validate()) {
            
            $basePath = $this->getBasePath();
            $directory = $this->getDirectory();
            
            $name = \quoma\core\helpers\StringHelper::systemName($this->file->baseName);

            FileHelper::createDirectory($basePath.DIRECTORY_SEPARATOR.$directory, 0775, true);
            
            $uid = uniqid();
            
            $relativePath = $directory . $name . $uid . '.' . $this->file->extension;
            $fullPath = $basePath .DIRECTORY_SEPARATOR. $directory . $name . $uid . '.' . $this->file->extension;
            
            $this->file->saveAs($fullPath, false);
            $this->relative_url = $relativePath;
            $this->extension = $this->file->extension;
            $this->size = $this->file->size;
            $this->mime = FileHelper::getMimeType($fullPath);
            
            $this->process($fullPath);
            
            return true;
        } else {
            return false;
        }
    }
    
    protected function process($file)
    {
    }
    
    public function beforeSave($insert) 
    {
        
        //I18n sites
        if(Yii::$app->request->get('lang')){
            $this->language = Yii::$app->request->get('lang');
        }
        
        //Multisite
        if(MediaModule::getInstance() && MediaModule::getInstance()->website_id){
            $this->website_id = MediaModule::getInstance()->website_id;
        }
        
        $this->base_url = str_replace(['http:','https:'],'',Yii::$app->frontendUrlManager->getBaseUrl());
        return parent::beforeSave($insert);
    }
    
    /**
     * Ancho y alto definidos en params['sizes']
     * @param string $size
     * @return array
     */
    public function getSize($size)
    {
        if(isset($size['width']) && isset($size['height'])){
            return [$size['width'], $size['height']];
        }
        
        $params = Yii::$app->getModule('media')->params;
        if(isset($params['sizes'][$size])){
            return $params['sizes'][$size];
        }
            
        return $params['sizes']['default'];

    }
    
    public function renderButton($options = [], $params = [])
    {
        throw new \yii\web\HttpException(500, 'Not implemented.');
    }
    
    public function afterFind() {
        parent::afterFind();
        $this->setScenario('update');
    }
    
    public function getName()
    {
        return \yii\helpers\Inflector::camel2words($this->type);
    }
    
    /**
     * Botones definidos por cada tipo de multimedia
     * @return string
     */
    public function customButtons($options = [])
    {
        return '';
    }
    
    public function render($width = null, $height = null, $options = [])
    {
    }
    
    /**
     * Caption para media
     * @param type $titleTag
     * @param type $descriptionTag
     * @return type
     */
    public function getCaption($titleTag = 'h3', $descriptionTag = 'p')
    {
        if(empty($this->title) && empty($this->description)){
            return '';
        }
        
        $title = $this->title;
        
        if($title && $titleTag){
            $title = \yii\helpers\Html::tag($titleTag, $this->title);
        }
        
        $description = $this->description;
        
        if($description && $descriptionTag){
            $description = \yii\helpers\Html::tag($descriptionTag, $this->description);
        }
        
        return "$title $description";
        
    }
    
    /**
     * Eliminamos los datos relacionados y los archivos
     * @return boolean
     */
    public function beforeDelete() {
        if (parent::beforeDelete()) {
            ModelHasMedia::deleteAll(['media_id' => $this->media_id]);
            Data::deleteAll(['media_id' => $this->media_id]);
            
            foreach($this->sized as $sized){
                //No utilizar query. Se debe ejecutar Sized::beforeDelete
                $sized->delete();
            }
            
            //Eliminamos el archivo asociado
            $filename = $this->getBasePath().DIRECTORY_SEPARATOR.$this->relative_url;
            if(file_exists($filename) && is_file($filename)){
                unlink($filename);
            }
            
            return true;
        }else{
            return false;
        }
        
    }

    public function hasCaption(){
        if(empty($this->title) && empty($this->description)){
            return false;
        }

        return true;
    }
}
