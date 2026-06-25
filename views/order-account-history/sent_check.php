<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $model app\models\OrderAccountHistory */
?>

<?php $form = ActiveForm::begin([
    'id' => 'sent-status-form',
]); ?>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-warning" style="font-size:16px; line-height:1.7;">
            <b>
                Shu klientni buyurtmasi dastavka qilinganligini tasdiqlaysizmi?
            </b>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-bordered">
            <tr>
                <th style="width:220px;">Mijoz</th>
                <td><?= Html::encode($model->client ? $model->client->fio : '-') ?></td>
            </tr>
            <tr>
                <th>Buyurtma sana</th>
                <td><?= Html::encode($model->date) ?></td>
            </tr>
        </table>
    </div>
</div>

<?php ActiveForm::end(); ?>