<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $debtClients array */
/* @var $selectedDebtPeriod string|null */

$allSumm = 0;
$sumOrder = 0;

$titleText = 'Qarzdor mijozlar hisobi';

if ($selectedDebtPeriod == '10') {
    $titleText .= ' - 10 kundan oshganlar';
} elseif ($selectedDebtPeriod == '30') {
    $titleText .= ' - 1 oydan oshganlar';
} else {
    $titleText .= ' - Barchasi';
}
?>

<style>
    body {
        font-family: dejavusans;
        font-size: 10px;
        color: #333333;
    }

    h3 {
        text-align: center;
        font-size: 16px;
        margin-bottom: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background-color: #2d353c;
        color: #ffffff;
        border: 1px solid #555555;
        padding: 6px;
        font-size: 10px;
        text-align: left;
    }

    td {
        border: 1px solid #999999;
        padding: 5px;
        font-size: 9px;
        vertical-align: middle;
    }

    .blue-text {
        color: #337ab7;
        font-weight: bold;
    }

    .green-text {
        color: green;
        font-weight: bold;
    }

    .red-text {
        color: #d9534f;
        font-weight: bold;
    }

    .orange-text {
        color: #f0ad4e;
        font-weight: bold;
    }

    .total-row td {
        background-color: #2d353c;
        color: #ffffff;
        font-weight: bold;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }
</style>

<h3><?= Html::encode($titleText) ?></h3>

<table>
    <thead>
        <tr>
            <th style="width:35px;">#</th>
            <th>FIO</th>
            <th>Telefon raqami</th>
            <th>Umumiy qarzi ($)</th>
            <th>Oxirgi buyurtma summasi ($)</th>
            <th>Oxirgi buyurtma sanasi</th>
            <th>Savdo qilmagan kuni</th>
        </tr>
    </thead>

    <tbody>
        <?php $i = 1; ?>

        <?php if (!empty($debtClients)) { ?>

            <?php foreach ($debtClients as $item) { ?>

                <?php
                    $client = $item['client'];
                    $orderAccount = $item['orderAccount'];
                    $lastOrder = $item['lastOrder'];
                    $lastOrderSum = $item['lastOrderSum'];
                    $daysAfterLastOrder = $item['daysAfterLastOrder'];

                    $totalDebt = $orderAccount->total_debt;

                    $textClass = 'blue-text';
                    $moneyClass = 'green-text';
                    $dayText = '—';

                    if ($lastOrder == null) {
                        $textClass = 'red-text';
                        $moneyClass = 'red-text';
                        $dayText = 'Savdo yo‘q';
                    } elseif ($daysAfterLastOrder > 30) {
                        $textClass = 'red-text';
                        $moneyClass = 'red-text';
                        $dayText = $daysAfterLastOrder . ' kun';
                    } elseif ($daysAfterLastOrder > 10) {
                        $textClass = 'orange-text';
                        $moneyClass = 'orange-text';
                        $dayText = $daysAfterLastOrder . ' kun';
                    } else {
                        $textClass = 'blue-text';
                        $moneyClass = 'green-text';
                        $dayText = $daysAfterLastOrder . ' kun';
                    }

                    $allSumm = $allSumm + $totalDebt;
                    $sumOrder = $sumOrder + $lastOrderSum;
                ?>

                <tr>
                    <td class="<?= $textClass ?> text-center">
                        <?= $i ?>
                    </td>

                    <td class="<?= $textClass ?>">
                        <?= $client ? Html::encode($client->fio) : '—' ?>
                    </td>

                    <td class="<?= $textClass ?>">
                        <?= $client && $client->phone ? Html::encode($client->phone) : '—' ?>
                    </td>

                    <td class="<?= $moneyClass ?> text-right">
                        <?= Yii::$app->formatter->asDecimal($totalDebt, 2) ?>
                    </td>

                    <td class="<?= $textClass ?> text-right">
                        <?= $lastOrder ? Yii::$app->formatter->asDecimal($lastOrderSum, 2) : '—' ?>
                    </td>

                    <td class="<?= $textClass ?> text-center">
                        <?= $lastOrder ? date('d.m.Y', strtotime($lastOrder->date)) : '—' ?>
                    </td>

                    <td class="<?= $textClass ?> text-center">
                        <?= $dayText ?>
                    </td>
                </tr>

                <?php $i = $i + 1; ?>

            <?php } ?>

            <tr class="total-row">
                <td colspan="3">
                    Jami:
                </td>

                <td class="text-right">
                    <?= Yii::$app->formatter->asDecimal($allSumm, 2) ?>
                </td>

                <td class="text-right">
                    <?= Yii::$app->formatter->asDecimal($sumOrder, 2) ?>
                </td>

                <td class="text-center">
                    —
                </td>

                <td class="text-center">
                    —
                </td>
            </tr>

        <?php } else { ?>

            <tr>
                <td colspan="7" class="text-center">
                    Ma'lumot topilmadi
                </td>
            </tr>

        <?php } ?>
    </tbody>
</table>