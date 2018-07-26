<?php

use yii\db\Migration;

class m160630_135328_media_create_model_has_media_table extends Migration {

    public function init() {
        $this->db = 'db_media';
        parent::init();
    }

    public function up() {

        $this->createTable('model_has_media', [
            'model_has_media_id' => $this->primaryKey()->notNull(),
            'media_id' => $this->integer()->notNull(),
            'model_id' => $this->integer()->notNull(),
            'model' => $this->string(255),
            'order' => $this->integer(),
        ]);

        $this->addForeignKey('fk_model_has_media_media', 'model_has_media', 'media_id', 'media', 'media_id');

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
