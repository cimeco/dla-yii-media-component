<?php

use yii\db\Migration;
use quoma\core\db\MigrationHelper;

class m160630_134919_media_create_database extends Migration {

    public function up() {
        $db = $this->getDbName();
        $this->execute("create database if not exists `$db`");
    }

    public function down() {
        $db = $this->getDbName();
        $this->execute("drop database `$db`");

        return true;
    }

    private function getDbName() {
        return \quoma\core\helpers\DbHelper::getDbName('db_media');
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
