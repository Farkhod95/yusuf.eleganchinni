<?php

use yii\db\Migration;

class m260630_000003_add_vozvrat_summa_to_product_account_history extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%product_account_history}}', 'vozvrat_summa', $this->decimal(15, 2)->null());
        $this->createIndex('idx-product_account_history-vozvrat_summa', '{{%product_account_history}}', 'vozvrat_summa');

        $this->execute("
            UPDATE product_account_history
            SET vozvrat_summa = ROUND(count * price, 2)
            WHERE vozvrat_order_id IS NOT NULL
              AND vozvrat_summa IS NULL
        ");
    }

    public function safeDown()
    {
        $this->dropIndex('idx-product_account_history-vozvrat_summa', '{{%product_account_history}}');
        $this->dropColumn('{{%product_account_history}}', 'vozvrat_summa');
    }
}
