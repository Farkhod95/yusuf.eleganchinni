<?php

use yii\db\Migration;

class m260629_000001_create_order_account_cart_draft_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%order_account_cart_draft}}', [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'product_details' => $this->text(),
            'total_count' => $this->integer()->notNull()->defaultValue(0),
            'total_sum' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);

        $this->createIndex(
            'idx-order_account_cart_draft-client_user',
            '{{%order_account_cart_draft}}',
            ['client_id', 'user_id'],
            true
        );
        $this->createIndex('idx-order_account_cart_draft-client_id', '{{%order_account_cart_draft}}', 'client_id');
        $this->createIndex('idx-order_account_cart_draft-user_id', '{{%order_account_cart_draft}}', 'user_id');

        $this->addForeignKey(
            'fk-order_account_cart_draft-client_id',
            '{{%order_account_cart_draft}}',
            'client_id',
            '{{%client}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-order_account_cart_draft-user_id',
            '{{%order_account_cart_draft}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-order_account_cart_draft-user_id', '{{%order_account_cart_draft}}');
        $this->dropForeignKey('fk-order_account_cart_draft-client_id', '{{%order_account_cart_draft}}');
        $this->dropIndex('idx-order_account_cart_draft-user_id', '{{%order_account_cart_draft}}');
        $this->dropIndex('idx-order_account_cart_draft-client_id', '{{%order_account_cart_draft}}');
        $this->dropIndex('idx-order_account_cart_draft-client_user', '{{%order_account_cart_draft}}');
        $this->dropTable('{{%order_account_cart_draft}}');
    }
}
