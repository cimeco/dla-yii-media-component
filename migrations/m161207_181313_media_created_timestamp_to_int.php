<?php

use yii\db\Migration;

class m161207_181313_media_created_timestamp_to_int extends Migration
{
    public function init() {
        $this->db = 'db_media';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute( "ALTER TABLE media CHANGE COLUMN create_timestamp create_timestamp INT(11) NULL DEFAULT NULL ");
    }

    public function down()
    {
        echo "m161207_181313_media_created_timestamp_to_int cannot be reverted.\n";

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
