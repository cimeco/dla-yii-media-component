<?php

use yii\db\Migration;

class m170720_190458_image_epigraph_conf extends Migration
{
    public function init(){
        $this->db= 'db_config';
        parent::init();
    }
    public function safeUp()
    {
        $category= quoma\modules\config\models\Category::findOne(['name' => 'Media']);

        $this->insert('item', [
            'attr' => 'insert_image_epigraph',
            'type' => 'checkbox',
            'label' => 'Insertar Imagen con Epígrafe',
            'description' => 'Indica si al insertar una imagen en el contenido, se debe insertarla con epígrafe',
            'multiple' => 0,
            'category_id' => $category->category_id,
            'superadmin' => 1,
            'default' => 0
        ]);

        $itemId = $this->db->getLastInsertID();

        $this->insert('rule', [
            'message' => '',
            'max' => null,
            'min' => null,
            'pattern' => null,
            'format' => null,
            'targetAttribute' => null,
            'targetClass' => null,
            'item_id' => $itemId,
            'validator' => 'boolean'
        ]);
    }

    public function safeDown()
    {
        echo "m170720_190458_image_epigraph_conf cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170720_190458_image_epigraph_conf cannot be reverted.\n";

        return false;
    }
    */
}
