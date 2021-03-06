<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace quoma\media\controllers;

use quoma\media\components\view\Preview;
use quoma\media\models\types\Html;
use Yii;
use yii\web\Response;

/**
 * Description of IFrameController
 *
 * @author juan
 */
class HtmlController extends DefaultController{
    
    
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
        
        $model = new Html();
                
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
            'title' => 'Html'
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
