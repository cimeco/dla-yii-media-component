<?php

use yii\db\Migration;

class m170911_181331_force_aspect extends Migration
{
    public function init(){
        $this->db= 'db_config';
        parent::init();
    }
    
    public function safeUp()
    {
        $category= quoma\modules\config\models\Category::findOne(['name' => 'Media']);

        $this->insert('item', [
            'attr' => 'force_aspect',
            'type' => 'checkbox',
            'label' => 'Force aspect ratio on small images?',
            'description' => '',
            'multiple' => 0,
            'category_id' => $category->category_id,
            'superadmin' => 1,
            'default' => 1
        ]);
        
    }

    public function safeDown()
    {
        $this->delete('item', ['attr' => ['force_aspect']]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170911_181331_force_aspect cannot be reverted.\n";

        return false;
    }
    */
}
