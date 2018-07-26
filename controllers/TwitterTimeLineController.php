<?php

namespace quoma\media\controllers;

use quoma\media\components\view\Preview;
use quoma\media\models\types\Twitter;
use Yii;
use yii\web\Response;

/**
 * ImageController implements the CRUD actions for Image model.
 */
class TwitterTimeLineController extends DefaultController
{

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
        
        $model = new \quoma\media\models\types\TwitterTimeLine();
        
        
        if ($model->load(Yii::$app->request->post()) && Yii::$app->request->isPost) {
            
            $validate = $model->validate();
            if(isset(Yii::$app->request->post()['save']) && $validate && $model->save(false)){
                
                    return [
                        'status' => 'success',
                        'model' => $model,
                        'preview' => $this->preview($model)
                    ];
            }else{
                return [
                    'status' => $validate ? 'success' : 'error',
                    'errors' => $model->getErrors(),
                ];
            }
            
        } 
            
        return [
            'status' => 'success',
            'form' => $this->renderAjax('create', ['model' => $model]),
            'errors' => $model->getErrors(),
            'title' => 'Twitter time line'
        ];
            
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
