<?php

use yii\db\Migration;

class m170911_185546_model_id_index extends Migration
{
    public function init(){
        $this->db= 'db_media';
        parent::init();
    }
    
    public function safeUp()
    {
        $this->createIndex('indx_model_id', 'model_has_media', 'model_id');
    }

    public function safeDown()
    {
        $this->dropIndex('indx_model_id', 'model_has_media');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170911_185546_model_id_index cannot be reverted.\n";

        return false;
    }
    */
}
