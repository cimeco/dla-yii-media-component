<?php

namespace quoma\media\controllers;

use quoma\core\web\Controller;
use quoma\media\models\Media;
use quoma\media\models\MediaSearch;
use quoma\modules\config\models\Config;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use quoma\media\components\helpers\MediaFactory;

class DefaultController extends Controller
{
    
    public $embedLayout = 'embed';
 
    public function behaviors()
    {
        if (count(\quoma\media\MediaModule::getInstance()->defaultControllerBehaviors) === 0) {
            return [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['post'],
                    ],
                ],
            ];
        }else{
            return \quoma\media\MediaModule::getInstance()->defaultControllerBehaviors;
        }
        
    }
    
    /**
     * Lists all Image models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MediaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * Finds the Image model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Image the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Media::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    
    public function actionGetMedia($id)
    {
        \Yii::$app->response->format= \yii\web\Response::FORMAT_JSON;
        
        $media = \quoma\media\models\Media::findOne($id);
        $media->scenario= 'insert-cont';
        
        return [
            'status' => 'success',
            'media' => $media->render(850),
            'epigraph_param' => Config::getValue('insert_image_epigraph') && $media->type == 'Image',
            'include_epigraph' => $media->hasCaption()
        ];
    }
    
    /**
     * Permite buscar en la biblioteca multimedia
     * @return json
     */
    public function actionSearch()
    { 
        
        Yii::$app->response->format= \yii\web\Response::FORMAT_JSON;
        
        if(Yii::$app->request->get('types', false)){
            $showTypes = strip_tags(Yii::$app->request->get('types'));
            $showTypes = explode(',',$showTypes);
        }else{
            $showTypes = MediaFactory::getNames();
        }
        
        $searchModel = new MediaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return [
            'status' => 'success',
            'form' => $this->renderAjax('search', ['showTypes' => $showTypes, 'searchModel' => $searchModel, 'dataProvider' => $dataProvider]),
            'title' => Yii::t('app', 'Search'),
            'pages' => [
                'pageCount' => $dataProvider->pagination->pageCount,
                'totalCount' => $dataProvider->pagination->totalCount,
                'currentPage' => $dataProvider->pagination->page + 1
            ]
       ];
    }
    
    /**
     * Permite renderizar una preview de un recurso multimedia
     * @param int $media_id
     * @return json
     */
    public function actionPreview($media_id)
    {
        
        $media = Media::findOne($media_id);
        
        Yii::$app->response->format= \yii\web\Response::FORMAT_JSON;
        
        return [
            'status' => 'success',
            'media' => $media,
            'preview' => $this->preview($media)
        ];
    }
    
    /**
     * Permite renderizar una preview de un recurso multimedia con las caracteristicas
     * por defecto definidas en el modulo
     * @param type $model
     * @return type
     */
    protected function preview($model)
    {
        $defaultPreviewOptions = \quoma\media\MediaModule::getInstance()->defaultPreviewOptions;
        
        //Si el widget especifica opciones de previsualización
        $previewOptions = \yii\helpers\Json::decode(Yii::$app->request->post('previewOptions', '{}'));
        
        return \quoma\media\components\view\Preview::widget(array_merge([
            'update' => true,
            'media' => $model,
        ], array_merge($defaultPreviewOptions, $previewOptions)));
    }
    
    /**
     * Renderiza la grilla de resultados de búsqueda multimedia
     * @return json
     */
    public function actionMediaGrid()
    {
        
        $searchModel = new MediaSearch();
        $mediaQuery = $searchModel->search(Yii::$app->request->queryParams);
        $count= clone $mediaQuery;
        $pages= new \yii\data\Pagination(['totalCount' => $count->count(), 'pageSize' => 16]);
        
        $dataMedia= $mediaQuery->offset($pages->offset)
                ->limit($pages->limit)
                ->all();
        $data=[];
        
        foreach ($dataMedia as $media) {
            $data[] = \quoma\media\components\helpers\MediaFactory::getMediaObject($media);
        }
                
        Yii::$app->response->format= \yii\web\Response::FORMAT_JSON;
        
        return [
            'status' => 'success',
            'form' => $this->renderAjax('media_grid', ['pages' => $pages, 'media' => $data, 'searchModel' => $searchModel]),
            'totalPages' => $pages->pageCount,
        ];
    }
    
    /**
     * Renderiza la vista de un media especifico
     * @param type $id
     * @return type
     */
    public function actionView($id){
        $model = Media::findOne($id);
        
        $viewFile = \yii\helpers\Inflector::camel2id($model->type).'/view.php';
        if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$viewFile)){
            return $this->redirect([\yii\helpers\Inflector::camel2id($model->type).'/view', 'id' => $id]);
        }
        
        return $this->render('view', ['model' => $model]);
    }
    
    public function actionCreate()
    {
        return $this->render('create');
    }
    
    
    public function actionDelete($id){
        $model= Media::findOne($id);        
        
        if($model->delete()){
            Yii::$app->session->addFlash('success', Yii::t('app', 'Media deleted successfull'));
        }else{
            Yii::$app->session->addFlash('error', Yii::t('app', 'Can`t be delete the media element'));
            
        }
        
        return $this->redirect(['index']);
    }
    
    /**
     * Permite actualizar los datos de los modelos desde la pantalla de alta de media.
     */
    public function actionUpdateAll()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $success = true;
        $errors = [];
        
        $mediaData = Yii::$app->request->post('MediaData');
        if(is_array($mediaData)){
            
            foreach($mediaData as $key => $data){
                $model = Media::findOne($key);
                if($model){
                    $success = $this->saveModelData($model, $data) && $success;
                    
                    if($model->hasErrors()){
                        $errors[] = $model->getErrorsAsString();
                    }
                }
            }
            
        }
        
        if($success === true){
            return [
                'status' => 'success',
                'message' => 'Data saved.'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => Yii::t('app', 'Error.').' '.implode(' ', $errors)
            ];
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

        return $model->save(true);
        
    }
}
