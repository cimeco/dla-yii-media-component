<?php

use yii\db\Migration;

class m160630_135258_media_create_data_table extends Migration {
    
    public function init() {
        $this->db = 'db_media';
        parent::init();
    }

    public function up() {

          $this->createTable('data', [
            'data_id' => $this->primaryKey(),
            'media_id' => $this->integer()->notNull(),
            'attribute' => $this->string(45),
            'type' => $this->string(45),
            'value' => $this->text(),
        ]);
        
        $this->addForeignKey('fk_data_media1', 'data', 'media_id', 'media', 'media_id');
        
        
    }

    public function down() {

        $this->dropTable('data');

        return true;
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
