<?php

use yii\db\Migration;

class m260630_000001_add_vozvrat_tracking_fields extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_account_history}}', 'is_vozvrat', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('{{%product_account_history}}', 'old_count', $this->integer()->null());

        $this->createIndex('idx-order_account_history-is_vozvrat', '{{%order_account_history}}', 'is_vozvrat');
        $this->createIndex('idx-product_account_history-old_count', '{{%product_account_history}}', 'old_count');

        $this->execute("
            UPDATE order_account_history oah
            SET is_vozvrat = 1
            WHERE EXISTS (
                SELECT 1
                FROM product_account_history pah
                WHERE pah.order_account_history_id = oah.id
                  AND pah.vozvrat_order_id IS NOT NULL
            )
        ");

        $this->execute("
            UPDATE product_account_history
            SET old_count = count
            WHERE vozvrat_order_id IS NOT NULL
              AND old_count IS NULL
        ");
    }

    public function safeDown()
    {
        $this->dropIndex('idx-product_account_history-old_count', '{{%product_account_history}}');
        $this->dropIndex('idx-order_account_history-is_vozvrat', '{{%order_account_history}}');

        $this->dropColumn('{{%product_account_history}}', 'old_count');
        $this->dropColumn('{{%order_account_history}}', 'is_vozvrat');
    }
}
