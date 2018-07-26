<?php

use yii\db\Migration;

class m160630_135242_media_create_media_table extends Migration
{
    public function init() {
        $this->db ='db_media';
        parent::init();
    }
    
    public function up()
    {
        
        $this->createTable('media', [
            'media_id' => $this->primaryKey(),
            'title' => $this->string(140),
            'description' => $this->string(255),
            'name' => $this->string(45),
            'base_url' => $this->string(255),
            'relative_url' => $this->string(255),
            'type' => $this->string(255),
            'mime' => $this->string(45),
            'size' => $this->float(),
            'width' => $this->integer(),
            'height' => $this->integer(),
            'extension' => $this->string(10),
            'create_date' => $this->date(),
            'create_time' => $this->time(),
            'create_timestamp' => $this->timestamp(),
            'status' => 'enum("enabled","disabled")',
        ]);
        
    }

    public function down()
    {
        
        $this->dropTable('media');
        
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
