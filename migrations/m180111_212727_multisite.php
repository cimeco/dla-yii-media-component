<?php

use yii\db\Migration;

class m180111_212727_multisite extends Migration
{
    public function init(){
        $this->db= 'db_media';
        parent::init();
    }
    
    public function up()
    {
        $this->addColumn('media', 'website_id', 'int(11)');
        $this->createIndex('website_id_index', 'media', 'website_id');
    }

    public function down()
    {
        $this->dropColumn('media', 'website_id');
    }
}
