<?php

use yii\db\Migration;

class m260630_000002_backfill_product_account_history_old_count extends Migration
{
    public function safeUp()
    {
        $this->execute("
            UPDATE product_account_history returned
            INNER JOIN (
                SELECT
                    order_account_history_id,
                    brand_id,
                    product_category_id,
                    size,
                    type,
                    type_sklad_id,
                    MAX(count) AS sold_count
                FROM product_account_history
                WHERE vozvrat_order_id IS NULL OR vozvrat_order_id = 0
                GROUP BY order_account_history_id, brand_id, product_category_id, size, type, type_sklad_id
            ) sold ON sold.order_account_history_id = returned.order_account_history_id
                AND sold.brand_id = returned.brand_id
                AND sold.product_category_id = returned.product_category_id
                AND sold.size = returned.size
                AND sold.type = returned.type
                AND sold.type_sklad_id = returned.type_sklad_id
            SET returned.old_count = sold.sold_count
            WHERE returned.vozvrat_order_id IS NOT NULL
        ");
    }

    public function safeDown()
    {
        return true;
    }
}
