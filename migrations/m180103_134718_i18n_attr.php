<?php

use yii\db\Migration;

class m180103_134718_i18n_attr extends Migration
{
    public function init(){
        $this->db= 'db_media';
        parent::init();
    }
    
    public function up()
    {
        $this->addColumn('media', 'language', 'varchar(10)');
        $this->createIndex('lang_index', 'media', 'language');
    }

    public function down()
    {
        $this->dropColumn('media', 'language');
    }
}
