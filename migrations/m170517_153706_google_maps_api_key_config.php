<?php

use yii\db\Migration;

class m170517_153706_google_maps_api_key_config extends Migration
{
    public function init(){
        $this->db= 'db_config';
        parent::init();
    }
    public function safeUp()
    {
         //Google Maps
        $category= quoma\modules\config\models\Category::findOne(['name' => 'Media']);
        
        $this->insert('item', [
            'attr' => 'google_maps_api_key',
            'type' => 'textInput',
            'label' => 'Google Maps Api Key',
            'description' => 'Llave para los servicios de Google Maps',
            'multiple' => 0,
            'category_id' => $category->category_id,
            'superadmin' => 1,
            'default' => 0
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
            'validator' => 'string'
        ]);
    }

    public function down()
    {
        echo "m170517_153706_google_maps_api_key_config cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
