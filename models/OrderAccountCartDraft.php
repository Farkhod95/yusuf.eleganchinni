<?php

namespace app\models;

/**
 * Draft cart for the order-account sale page.
 *
 * @property int $id
 * @property int $client_id
 * @property int $user_id
 * @property string $product_details
 * @property int $total_count
 * @property double $total_sum
 * @property string $created_at
 * @property string $updated_at
 */
class OrderAccountCartDraft extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'order_account_cart_draft';
    }

    public function rules()
    {
        return [
            [['client_id', 'user_id'], 'required'],
            [['client_id', 'user_id', 'total_count'], 'integer'],
            [['product_details'], 'string'],
            [['total_sum'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function beforeSave($insert)
    {
        $now = date('Y-m-d H:i:s');
        if ($insert) {
            $this->created_at = $now;
        }
        $this->updated_at = $now;

        return parent::beforeSave($insert);
    }

    public function getClient()
    {
        return $this->hasOne(Client::className(), ['id' => 'client_id']);
    }

    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }
}
