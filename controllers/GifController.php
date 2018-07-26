<?php

namespace quoma\media\controllers;

use quoma\media\components\view\Preview;
use quoma\media\models\types\Gif;
use quoma\media\models\types\Image;
use Yii;
use yii\web\Response;

/**
 * ImageController implements the CRUD actions for Image model.
 */
class GifController extends DefaultController
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
        
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = new Gif();
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
