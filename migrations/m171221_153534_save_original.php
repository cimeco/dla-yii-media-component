<?php

use yii\db\Migration;
use quoma\modules\config\models\Category;

class m171221_153534_save_original extends Migration
{
    
    public function init(){
        $this->db= 'db_config';
        parent::init();
    }
    
    public function up()
    {
        $categoryId = Category::findOne(['name' => 'Media'])->category_id;
        
        /************** 
         ** Imagenes **
         *************/
        
        //Ancho min
        $this->insert('item', [
            'attr' => 'save_original',
            'type' => 'checkbox',
            'label' => 'Guardar original?',
            'description' => '',
            'multiple' => 0,
            'category_id' => $categoryId,
            'superadmin' => 0,
            'default' => 1
        ]);
        
    }

    public function down()
    {
        $items = \quoma\modules\config\models\Item::find()->where(['attr' => 'save_original']);
        
        $this->delete('rule', ['item_id' => $items->select('item_id')]);
        $this->delete('config', ['item_id' => $items->select('item_id')]);
        $this->delete('item', ['attr' => 'save_original']);
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
