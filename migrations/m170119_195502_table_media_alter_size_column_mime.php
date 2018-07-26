<?php

use yii\db\Migration;

class m170119_195502_table_media_alter_size_column_mime extends Migration
{
    
    public function init(){
        
        $this->db= 'db_media';
        
        parent::init();
    }
    
    public function safeUp()
    {
        $this->execute(
                "ALTER TABLE `media` 
                    CHANGE COLUMN `mime` `mime` VARCHAR(90) NULL DEFAULT NULL 
                ");
    }

    public function safeDown()
    {
       $this->execute(
                "ALTER TABLE `media` 
                    CHANGE COLUMN `mime` `mime` VARCHAR(45) NULL DEFAULT NULL 
                ");

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
