<?php

use yii\db\Migration;

class m170907_155552_cdn extends Migration
{
    public function init(){
        $this->db= 'db_config';
        parent::init();
    }
    
    public function safeUp()
    {
        $category= quoma\modules\config\models\Category::findOne(['name' => 'Media']);

        $this->insert('item', [
            'attr' => 'cdn',
            'type' => 'checkbox',
            'label' => 'Use content delivery network?',
            'description' => '',
            'multiple' => 0,
            'category_id' => $category->category_id,
            'superadmin' => 1,
            'default' => 0
        ]);
        
        $this->insert('item', [
            'attr' => 'cdn_base_url',
            'type' => 'string',
            'label' => 'Content delivery network base url',
            'description' => '',
            'multiple' => 0,
            'category_id' => $category->category_id,
            'superadmin' => 1,
            'default' => ''
        ]);

        $itemId = $this->db->getLastInsertID();

        $this->insert('rule', [
            'message' => '',
            'max' => null,
            'min' => null,
            'pattern' => null,
            'format' => null,
            'targetAttribute' => null,
            'targetClass' => null,
            'item_id' => $itemId,
            'validator' => 'url'
        ]);
    }

    public function safeDown()
    {
        $cdn_base_url = \quoma\modules\config\models\Item::find()->where(['attr' => 'cdn_base_url'])->one();
        
        $this->delete('rule', ['item_id' => $cdn_base_url->item_id]);
        
        $this->delete('item', ['attr' => ['cdn_base_url', 'cdn']]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170907_155552_cdn cannot be reverted.\n";

        return false;
    }
    */
}
