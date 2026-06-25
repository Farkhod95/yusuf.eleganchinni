<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%sklad}}`.
 */
class m210108_093457_add_car_number_column_to_sklad_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%sklad}}', 'car_number', $this->string(255)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%sklad}}', 'car_number');
    }
}
