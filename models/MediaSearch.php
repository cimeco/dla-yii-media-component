<?php

namespace quoma\media\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use quoma\media\models\Media;
use quoma\media\MediaModule;

/**
 * ImageSearch represents the model behind the search form about `common\modules\media\models\types\Image`.
 */
class MediaSearch extends Media
{
    
    public $_search;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['media_id', 'width', 'height'], 'integer'],
            [['title', 'description', 'name', 'base_url', 'relative_url', 'class', 'mime', 'extension', 'create_date', 'create_time', 'status', 'type', '_search', 'lang'], 'safe'],
            [['size'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Media::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'create_timestamp' => SORT_DESC
                ]
            ],
            'pagination' => [
                'pageSize' => 9
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }
        
        //Multisite
        if(MediaModule::getInstance() && MediaModule::getInstance()->website_id){
            $website_id = MediaModule::getInstance()->website_id;
            $query->andWhere(['website_id' => $website_id]);
        }

        $query->andFilterWhere([
            'media_id' => $this->media_id,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'create_date' => $this->create_date,
            'type' => $this->type,
            'language' => $this->language
        ]);

        $query->andFilterWhere(['status' => $this->status]);
        
        if(isset($this->_search)){
            $query->andFilterWhere(['like', 'title', $this->_search])
                ->orFilterWhere(['like', 'description', $this->_search]);
        }else{
            $query->andFilterWhere(['like', 'title', $this->title])
                ->andFilterWhere(['like', 'description', $this->description])
                ->andFilterWhere(['like', 'extension', $this->extension]);
        }
        
        $query->orderBy(['media_id' => SORT_DESC]);
            
        return $dataProvider;
    }
    
    public function setLang($lang)
    {
        $this->language = $lang;
    }
}
