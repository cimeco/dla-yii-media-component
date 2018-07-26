<?php

use yii\db\Migration;

class m160630_135311_media_create_sized_table extends Migration {

    public function init() {
        $this->db = 'db_media';
        parent::init();
    }

    public function up() {

        
        $this->createTable('sized', [
            'sized_id' => $this->primaryKey(),
            'base_url' => $this->string(255),
            'relative_url' => $this->string(255),
            'width' => $this->integer(),
            'height' => $this->integer(),
            'media_id' => $this->integer()->notNull(),
        ]);
        
        $this->addForeignKey('fk_table1_media1', 'sized', 'media_id', 'media', 'media_id');
        
    }

    public function down() {

        $this->dropTable('sized');

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
