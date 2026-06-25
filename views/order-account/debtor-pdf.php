<?php
use yii\helpers\Html;
use app\models\OrderAccountHistory;

/* @var $orderAccount app\models\OrderAccount[] */
/* @var $selectedClientType string|null */
/* @var $selectedClientId string|null */

$allSumm = 0;
$allCount = 0;

function clientTypeNamePdf($type)
{
    if ($type == 1) return 'Oddiy mijoz';
    if ($type == 2) return 'Bozordagi mijoz';
    if ($type == 3) return 'Filial';
    return '';
}

$filterText = [];
if (!empty($selectedClientType)) {
    $filterText[] = 'Mijoz turi: ' . clientTypeNamePdf($selectedClientType);
}
if (!empty($selectedClientId)) {
    foreach ($orderAccount as $item) {
        if ((string)$item->client_id === (string)$selectedClientId && $item->client) {
            $filterText[] = 'Mijoz: ' . $item->client->fio;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Qarzdorlar ro'yxati</title>
    <style>
        body {
            font-family: dejavusans;
            font-size: 11px;
            color: #000;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .meta {
            margin-bottom: 10px;
            font-size: 10px;
        }
        .meta-line {
            margin-bottom: 3px;
        }
        .summary {
            margin-bottom: 10px;
            font-size: 11px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 18px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #cfd6dc;
            padding: 7px 8px;
            vertical-align: middle;
        }
        thead th {
            background: #f3f6f9;
            color: #2b7a78;
            font-weight: bold;
            text-align: left;
        }
        .fio {
            color: #2f7ed8;
            font-weight: bold;
        }
        .type {
            color: #348fe2;
            font-weight: bold;
        }
        .debt {
            color: #ff0000;
            font-weight: bold;
        }
        .date {
            color: #f59c1a;
            font-weight: bold;
        }
        .total-label,
        .total-value,
        .total-empty {
            background: #2d353c;
            color: #fff;
            font-weight: bold;
        }
        .center {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="title">Qarzdorlar ro'yxati</div>

<div class="meta">
    <div class="meta-line">Sana: <?= date('d.m.Y H:i') ?></div>
    <?php if (!empty($filterText)): ?>
        <div class="meta-line"><?= implode(' | ', array_map(function ($item) {
            return Html::encode($item);
        }, $filterText)) ?></div>
    <?php endif; ?>
</div>

<table>
    <thead>
        <tr>
            <th style="width: 6%;">#</th>
            <th style="width: 40%;">FIO</th>
            <th style="width: 22%;">Mijoz turi</th>
            <th style="width: 15%;">Qarzi ($)</th>
            <th style="width: 17%;">Oxirgi buyurtma sanasi</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; ?>
        <?php foreach ($orderAccount as $model): ?>
            <?php
            $debtValue = (float)$model->total_debt;
            if ($debtValue < 1) {
                continue;
            }

            $orderAccountHistory = OrderAccountHistory::find()
                ->where(['client_id' => $model->client_id])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            $allSumm += $debtValue;
            $allCount++;
            ?>
            <tr>
                <td class="center"><?= $i ?></td>
                <td class="fio"><?= Html::encode($model->client ? $model->client->fio : '') ?></td>
                <td class="type"><?= Html::encode($model->client ? $model->client->getTypeView($model->client->type) : '') ?></td>
                <td class="debt"><?= Yii::$app->formatter->asDecimal($debtValue, 2) ?></td>
                <td class="date"><?= $orderAccountHistory ? Yii::$app->formatter->asDate($orderAccountHistory->date, 'php:d.m.Y') : '' ?></td>
            </tr>
            <?php $i++; ?>
        <?php endforeach; ?>

        <tr>
            <td colspan="3" class="total-label">Jami:</td>
            <td class="total-value"><?= Yii::$app->formatter->asDecimal($allSumm, 2) ?></td>
            <td class="total-empty"></td>
        </tr>
    </tbody>
</table>

</body>
</html>