<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Karzinka';
$this->params['breadcrumbs'][] = ['label' => 'Mijozlar buyurmalari tarixi', 'url' => ['/order-account-history/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="panel panel-inverse">
    <div class="panel-heading">
        <div class="panel-heading-btn">
            <?= Html::a('<span class="btn btn-default btn-xs m-r-5"><i class="fa fa-arrow-left"></i> Orqaga qaytish</span>', ['/order-account-history/index'], ['data-pjax' => 0]) ?>
        </div>
        <h4 class="panel-title">Karzinkada turgan buyurtmalar</h4>
    </div>

    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Mijoz</th>
                        <th>Foydalanuvchi</th>
                        <th style="width:90px;">Soni</th>
                        <th style="width:120px;">Summa ($)</th>
                        <th>Mahsulotlar</th>
                        <th style="width:150px;">Yangilangan</th>
                        <th style="width:90px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->models as $index => $model): ?>
                        <?php
                        $items = json_decode($model->product_details, true);
                        if (!is_array($items)) {
                            $items = [];
                        }
                        ?>
                        <tr>
                            <td><?= $dataProvider->pagination->offset + $index + 1 ?></td>
                            <td><?= Html::encode($model->client ? $model->client->fio : '') ?></td>
                            <td><?= Html::encode($model->user ? $model->user->username : '') ?></td>
                            <td><?= (int)$model->total_count ?></td>
                            <td><?= number_format((float)$model->total_sum, 2, '.', ' ') ?></td>
                            <td>
                                <?php if (empty($items)): ?>
                                    <span class="text-muted">Bo'sh</span>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                        <div>
                                            <b><?= Html::encode($item['marka'] ?? '') ?></b>
                                            <?= Html::encode($item['name'] ?? '') ?>
                                            <?= Html::encode($item['size'] ?? '') ?>
                                            <?= Html::encode($item['tip'] ?? '') ?>
                                            - <?= Html::encode($item['count'] ?? '') ?> ta,
                                            <?= Html::encode($item['price'] ?? '') ?> $
                                            (<?= Html::encode($item['joy'] ?? '') ?>)
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td><?= Html::encode($model->updated_at) ?></td>
                            <td>
                                <?= Html::a(
                                    '<i class="fa fa-trash"></i> O\'chirish',
                                    ['cartdraft-delete', 'id' => $model->id],
                                    [
                                        'class' => 'btn btn-danger btn-xs',
                                        'data-method' => 'post',
                                        'data-confirm' => 'Karzinkani o\'chirasizmi?',
                                    ]
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if ($dataProvider->count === 0): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Karzinkada buyurtma yo'q.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
    </div>
</div>
