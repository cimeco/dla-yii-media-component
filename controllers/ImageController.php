<?php

namespace quoma\media\controllers;

use Yii;
use quoma\media\models\types\Image;
use quoma\media\models\types\search\ImageSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\base\DynamicModel;

/**
 * ImageController implements the CRUD actions for Image model.
 */
class ImageController extends DefaultController
{
    
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {            
        if ($action->id == 'ckupload') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Displays a single Image model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Image model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $model = new Image();
        $model->load(Yii::$app->request->post('Media'));
        
        if (Yii::$app->request->isPost && $model->upload()) {
            
            if($model->save()){
                
                return [
                    'status' => 'success',
                    'model' => $model,
                    'preview' => $this->preview($model)
                ];
            }
            
        } 
            
        return [
            'status' => 'error',
            'errors' => $model->getErrors(),
        ];
            
    }
    
       
    /**
     * For CKeditor
     */
    public function actionCkupload(){

        $model = new Image();
        $model->load(Yii::$app->request->post());
        
        if (Yii::$app->request->isPost && $model->upload('upload')) {
            
            if($model->save()){
                return $this->render('_ck-upload', ['model' => $model]);
            }
        }

    }

    /**
     * Updates an existing Image model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->media_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * Inica el proceso de optimización. Si la imagen ya se encuentra optimizada,
     * muestra una preview.
     * @param int $id
     */
    public function actionStartOptimization($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = $this->findModel($id);
        
        if($model->isOptimized()){
            
            $steps = \quoma\media\models\types\Image::getAspects();
        
            return [
                'status' => 'success',
                'form' => $this->renderPartial('optimization-preview', [
                    'model' => $model,
                    'steps' => $steps
                ]),
                'errors' => $model->getErrors(),
                'title' => Yii::t('app', 'Image aspect optimization preview')
            ];
        }
        
        return $this->actionOptimize($id);
    }
    
    /**
     * Permite optimizar una imagen para distintas relaciones de aspecto.
     * El step 0 se utiliza para mostrar una preview cdo la imagen ya está
     * optimizada. A partir del step 1, se accede a los steps
     * @param integer $id
     * @return mixed
     */
    public function actionOptimize($id, $step = 0)
    {
        
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $model = $this->findModel($id);
        
        $steps = \quoma\media\models\types\Image::getAspects();
        
        if (isset($_POST['x1']) && $step >=0) {

            $x1 = Yii::$app->request->post('x1');
            $x2 = Yii::$app->request->post('x2');
            $y1 = Yii::$app->request->post('y1');
            $y2 = Yii::$app->request->post('y2');
            
            $validationModel = DynamicModel::validateData(compact('x1', 'x2', 'y1', 'y2'), [
                [['x1', 'x2', 'y1', 'y2'], 'double', 'max' => 100, 'min' => 0],
                [['x1'], 'compare', 'compareValue' => $x2, 'operator' => '<', 'type' => 'number'],
                [['y1'], 'compare', 'compareValue' => $y2, 'operator' => '<', 'type' => 'number'],
            ]);

            if ($validationModel->hasErrors()) {
                throw new \yii\web\HttpException(500, 'Bad data.');
            } 
            
            //Ancho y alto de la nueva imagen
            $newWidth = (int)((($x2 - $x1)/100) * $model->sourceWidth );
            $newHeight = (int)((($y2 - $y1)/100) * $model->sourceHeight );
            
            $start = [ (int)(($x1/100)*$model->sourceWidth), (int)(($y1/100)*$model->sourceHeight) ];
            
            //marcar las imagenes previas como inválidas borrando el tamaño (no borrar porque puede estar en cache la url)
            $model->invalidate($newWidth, $newHeight);
            
            $cropped = $model->crop($newWidth, $newHeight, $start);

            if($cropped !== false){
                if(isset($steps[$step+1])){
                    ++$step;
                }else{
                    return [
                        'status' => 'success',
                        'step' => -1,
                        'media_id' => $model->media_id,
                        'label' => '<span class="glyphicon glyphicon-phone"></span> ' . Yii::t('app','Optimized')
                    ];
                }
            }else{
                return [
                    'status' => 'error',
                    'errors' => Yii::t('app', 'Image could not be cropped.')
                ];
            }
            
        } 
        
        return [
            'status' => 'success',
            'step' => $step,
            'form' => $this->renderPartial('optimize', [
                'model' => $model,
                'step' => $step,
                'steps' => $steps
            ]),
            'errors' => $model->getErrors(),
            'title' => Yii::t('app', 'Image aspect optimization')
        ];
    }

    /**
     * Deletes an existing Image model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

}
