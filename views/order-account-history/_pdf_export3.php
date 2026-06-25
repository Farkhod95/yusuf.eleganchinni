<?php

use yii\helpers\Html;

?>

<style>
    body {
        font-family: dejavusans;
        font-size: 8.5px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 7.5px;
        margin-bottom: 9px;
    }

    th {
        background-color: #d9edf7;
        color: #000;
        font-weight: bold;
        text-align: center;
        font-size: 7px;
        padding: 3px;
        border: 1px solid #777;
    }

    td {
        border: 1px solid #777;
        padding: 3px;
        font-size: 7px;
        vertical-align: middle;
    }

    .date-title {
        background-color: #348fe2;
        color: #fff;
        font-weight: bold;
        font-size: 13px;
        text-align: center;
        padding: 7px;
        margin-top: 10px;
        margin-bottom: 5px;
        border: 1px solid #1f5f99;
    }

    .type-title {
        background-color: #b7d7f0;
        color: #000;
        font-weight: bold;
        font-size: 11px;
        text-align: center;
        padding: 5px;
        margin-top: 5px;
        margin-bottom: 3px;
        border: 1px solid #777;
    }

    .total-row td {
        background-color: #fff3cd;
        font-weight: bold;
        color: #000;
        font-size: 7px;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .status-plus {
        color: #007000;
        font-size: 15px;
        font-weight: bold;
    }

    .status-minus {
        color: #b00000;
        font-size: 15px;
        font-weight: bold;
    }

    .order-row td {
        color: #007000;
        font-weight: bold;
    }

    .debt-row td {
        color: #b00000;
        font-weight: bold;
    }

    .date-total-box {
        border: 1px solid #8cc152;
        background-color: #f2fbec;
        margin-top: 8px;
        margin-bottom: 12px;
    }

    .date-total-title {
        background-color: #8cc152;
        color: #fff;
        font-weight: bold;
        font-size: 10px;
        padding: 7px;
        border-bottom: 1px solid #8cc152;
    }

    .date-total-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 4px;
        margin: 0;
        background-color: #f2fbec;
    }

    .date-total-table td {
        background-color: #fff;
        border: 1px solid #d6e9c6;
        border-left: 4px solid #8cc152;
        padding: 6px 5px;
        vertical-align: top;
        width: 9.09%;
    }

    .date-total-table .blue {
        border-left-color: #348fe2;
    }

    .date-total-table .green {
        border-left-color: #5cb85c;
    }

    .date-total-table .red {
        border-left-color: #d9534f;
    }

    .date-total-table .orange {
        border-left-color: #f0ad4e;
    }

    .date-total-table .purple {
        border-left-color: #7e57c2;
    }

    .date-total-table .dark {
        border-left-color: #555;
    }

    .date-total-label {
        display: block;
        font-size: 6.5px;
        color: #555;
        font-weight: bold;
        margin-bottom: 3px;
    }

    .date-total-value {
        display: block;
        font-size: 8px;
        color: #000;
        font-weight: bold;
        white-space: nowrap;
    }

    .filter-box {
        font-size: 10px;
        margin-bottom: 8px;
        padding: 5px;
        border: 1px solid #b7cbe0;
        background-color: #eef7ff;
    }
</style>

<h3 style="text-align:center; margin-bottom:5px;">
    Buyurtmalar va qarzlar PDF3
</h3>

<h4 style="text-align:center; margin-top:0;">
    Hodim turi bo‘yicha hisobot
</h4>

<?php if (!empty($customer_name) || !empty($start_date) || !empty($end_date) || !empty($selectedUserTypeName)): ?>
    <div class="filter-box">
        <?php if (!empty($customer_name)): ?>
            <b>Mijoz:</b> <?= Html::encode($customer_name) ?>
            &nbsp;&nbsp;
        <?php endif; ?>

        <?php if (!empty($selectedUserTypeName)): ?>
            <b>Hodim turi:</b> <?= Html::encode($selectedUserTypeName) ?>
            &nbsp;&nbsp;
        <?php endif; ?>

        <?php if (!empty($start_date) && !empty($end_date)): ?>
            <b>Sana:</b> <?= Html::encode($start_date) ?> - <?= Html::encode($end_date) ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
$typeOrder = [
    1 => 'Do‘kon sotuvchisi',
    2 => 'Sklad sotuvchisi',
    3 => 'Dastavka sotuvchisi',
    0 => 'Turi belgilanmagan',
];

$groupedByDate = [];

foreach ($rows as $row) {
    $dateKey = date('Y-m-d', strtotime($row['datetime']));
    $typeKey = (int)($row['created_by_type'] ?? 0);

    $groupedByDate[$dateKey][$typeKey][] = $row;
}

krsort($groupedByDate);
?>

<?php if (empty($groupedByDate)): ?>
    <div style="text-align:center; font-weight:bold; padding:15px;">
        Ma’lumot topilmadi
    </div>
<?php endif; ?>

<?php foreach ($groupedByDate as $date => $dateTypes): ?>

    <div class="date-title">
        <?= date('d.m.Y', strtotime($date)) ?> sana
    </div>

    <?php
    $date_sum_all_product = 0;
    $date_sum_all_paid_dollar = 0;
    $date_sum_paid_dollar = 0;
    $date_sum_paid_cart = 0;
    $date_sum_paid_som = 0;
    $date_sum_paid_zdacha_sum = 0;
    $date_sum_paid_zdacha_dollar = 0;

    $date_sum_paid_debt = 0;
    $date_sum_debt_dollar = 0;
    $date_sum_debt_som = 0;
    $date_sum_debt_cart = 0;
    $date_sum_debt_zdacha_sum = 0;
    $date_sum_debt_zdacha_dollar = 0;
    ?>

    <?php foreach ($typeOrder as $type => $typeName): ?>

        <?php if (empty($dateTypes[$type])): ?>
            <?php continue; ?>
        <?php endif; ?>

        <?php
        $typeRows = $dateTypes[$type];

        $type_sum_all_product = 0;
        $type_sum_all_paid_dollar = 0;
        $type_sum_paid_dollar = 0;
        $type_sum_paid_cart = 0;
        $type_sum_paid_som = 0;
        $type_sum_paid_zdacha_sum = 0;
        $type_sum_paid_zdacha_dollar = 0;

        $type_sum_paid_debt = 0;
        $type_sum_debt_dollar = 0;
        $type_sum_debt_som = 0;
        $type_sum_debt_cart = 0;
        $type_sum_debt_zdacha_sum = 0;
        $type_sum_debt_zdacha_dollar = 0;
        ?>

        <div class="type-title">
            <?= Html::encode($typeName) ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Mijoz</th>
                    <th>Hodim</th>
                    <th>To'lanadigan summa ($)</th>
                    <th>To‘l jami summa ($)</th>
                    <th>To‘l summa ($)</th>
                    <th>To‘l summa karta</th>
                    <th>To‘l summa naqt</th>
                    <th>To‘l summa qaytim</th>
                    <th>Umumiy to‘l qarz ($)</th>
                    <th>To‘l qarz ($)</th>
                    <th>To‘l qarz naqt</th>
                    <th>To‘l qarz karta</th>
                    <th>To‘l qarz qaytim</th>
                    <th>Holat</th>
                </tr>
            </thead>

            <tbody>
                <?php $counter = 1; ?>

                <?php foreach ($typeRows as $row): ?>

                    <?php
                    $isOrder = (($row['action'] ?? '') === 'Buyurtma qilgan');
                    $isDebt = (($row['action'] ?? '') === 'Qarz to‘lagan');

                    $allProductSum = $isOrder ? (float)($row['all_product_sum'] ?? 0) : 0;

                    $allPaidDollar = $isOrder ? (float)($row['all_summ_dollar'] ?? 0) : 0;
                    $paidDollar = $isOrder ? (float)($row['sum_transfers'] ?? 0) : 0;
                    $paidCart = $isOrder ? (float)($row['sum_cart'] ?? 0) : 0;
                    $paidSom = $isOrder ? (float)($row['sum_som'] ?? 0) : 0;

                    $paidZdachaSum = $isOrder ? (float)($row['zdacha_sum'] ?? 0) : 0;
                    $paidZdachaDollar = $isOrder ? (float)($row['zdacha_dollar'] ?? 0) : 0;

                    $paidDebt = $isDebt ? (float)($row['paid_debt'] ?? 0) : 0;

                    $debtDollar = $isDebt ? (float)($row['debt_sum_transfers'] ?? 0) : 0;
                    $debtSom = $isDebt ? (float)($row['debt_sum_som'] ?? 0) : 0;
                    $debtCart = $isDebt ? (float)($row['debt_summ_cart'] ?? 0) : 0;

                    $debtZdachaSum = $isDebt ? (float)($row['debt_zdacha_sum'] ?? 0) : 0;
                    $debtZdachaDollar = $isDebt ? (float)($row['debt_zdacha_dollar'] ?? 0) : 0;

                    $rowClass = $isDebt ? 'debt-row' : 'order-row';
                    ?>

                    <tr class="<?= $rowClass ?>">
                        <td class="text-center">
                            <?= $counter++ ?>
                        </td>

                        <td>
                            <?= Html::encode($row['client_name'] ?? '') ?>
                        </td>

                        <td>
                            <?= Html::encode($row['created_by_name'] ?? '-') ?>
                        </td>

                        <td class="text-right">
                            <?= $allProductSum ? number_format($allProductSum, 2) : '' ?>
                        </td>

                        <td class="text-right">
                            <?= $allPaidDollar ? number_format($allPaidDollar, 2) : '' ?>
                        </td>

                        <td class="text-right">
                            <?= $paidDollar ? number_format($paidDollar, 2) : '' ?>
                        </td>

                        <td class="text-right">
                            <?= $paidCart ? number_format($paidCart, 2) : '' ?>
                        </td>

                        <td class="text-right">
                            <?= $paidSom ? number_format($paidSom, 2) : '' ?>
                        </td>

                        <td class="text-right">
                            <?php if ($isOrder && ($paidZdachaSum || $paidZdachaDollar)): ?>
                                <?= number_format($paidZdachaSum, 2) ?> (<?= number_format($paidZdachaDollar, 2) ?> $)
                            <?php endif; ?>
                        </td>

                        <td class="text-right">
                            <?= $paidDebt ? number_format($paidDebt, 2) : '' ?>
                        </td>

                        <td class="text-right">
                            <?= $debtDollar ? number_format($debtDollar, 2) : '' ?>
                        </td>

                        <td class="text-right">
                            <?= $debtSom ? number_format($debtSom, 2) : '' ?>
                        </td>

                        <td class="text-right">
                            <?= $debtCart ? number_format($debtCart, 2) : '' ?>
                        </td>

                        <td class="text-right">
                            <?php if ($isDebt && ($debtZdachaSum || $debtZdachaDollar)): ?>
                                <?= number_format($debtZdachaSum, 2) ?> (<?= number_format($debtZdachaDollar, 2) ?> $)
                            <?php endif; ?>
                        </td>

                        <td class="text-center">
                            <?php if ($isDebt): ?>
                                <span class="status-minus">−</span>
                            <?php else: ?>
                                <span class="status-plus">+</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php
                    $type_sum_all_product += $allProductSum;

                    $type_sum_all_paid_dollar += $allPaidDollar;
                    $type_sum_paid_dollar += $paidDollar;
                    $type_sum_paid_cart += $paidCart;
                    $type_sum_paid_som += $paidSom;
                    $type_sum_paid_zdacha_sum += $paidZdachaSum;
                    $type_sum_paid_zdacha_dollar += $paidZdachaDollar;

                    $type_sum_paid_debt += $paidDebt;
                    $type_sum_debt_dollar += $debtDollar;
                    $type_sum_debt_som += $debtSom;
                    $type_sum_debt_cart += $debtCart;
                    $type_sum_debt_zdacha_sum += $debtZdachaSum;
                    $type_sum_debt_zdacha_dollar += $debtZdachaDollar;
                    ?>

                <?php endforeach; ?>

                <tr class="total-row">
                    <td colspan="3" class="text-right">
                        <?= Html::encode($typeName) ?> jami:
                    </td>

                    <td class="text-right">
                        <?= number_format($type_sum_all_product, 2) ?>
                    </td>

                    <td class="text-right">
                        <?= number_format($type_sum_all_paid_dollar, 2) ?>
                    </td>

                    <td class="text-right">
                        <?= number_format($type_sum_paid_dollar, 2) ?>
                    </td>

                    <td class="text-right">
                        <?= number_format($type_sum_paid_cart, 2) ?>
                    </td>

                    <td class="text-right">
                        <?= number_format($type_sum_paid_som, 2) ?>
                    </td>

                    <td class="text-right">
                        <?= number_format($type_sum_paid_zdacha_sum, 2) ?> (<?= number_format($type_sum_paid_zdacha_dollar, 2) ?> $)
                    </td>

                    <td class="text-right">
                        <?= number_format($type_sum_paid_debt, 2) ?>
                    </td>

                    <td class="text-right">
                        <?= number_format($type_sum_debt_dollar, 2) ?>
                    </td>

                    <td class="text-right">
                        <?= number_format($type_sum_debt_som, 2) ?>
                    </td>

                    <td class="text-right">
                        <?= number_format($type_sum_debt_cart, 2) ?>
                    </td>

                    <td class="text-right">
                        <?= number_format($type_sum_debt_zdacha_sum, 2) ?> (<?= number_format($type_sum_debt_zdacha_dollar, 2) ?> $)
                    </td>

                    <td></td>
                </tr>

                <?php
                $date_sum_all_product += $type_sum_all_product;

                $date_sum_all_paid_dollar += $type_sum_all_paid_dollar;
                $date_sum_paid_dollar += $type_sum_paid_dollar;
                $date_sum_paid_cart += $type_sum_paid_cart;
                $date_sum_paid_som += $type_sum_paid_som;
                $date_sum_paid_zdacha_sum += $type_sum_paid_zdacha_sum;
                $date_sum_paid_zdacha_dollar += $type_sum_paid_zdacha_dollar;

                $date_sum_paid_debt += $type_sum_paid_debt;
                $date_sum_debt_dollar += $type_sum_debt_dollar;
                $date_sum_debt_som += $type_sum_debt_som;
                $date_sum_debt_cart += $type_sum_debt_cart;
                $date_sum_debt_zdacha_sum += $type_sum_debt_zdacha_sum;
                $date_sum_debt_zdacha_dollar += $type_sum_debt_zdacha_dollar;
                ?>
            </tbody>
        </table>

    <?php endforeach; ?>

    <div class="date-total-box">
        <div class="date-total-title">
            <?= date('d.m.Y', strtotime($date)) ?> sana bo‘yicha jami summa va to‘lovlar
        </div>

        <table class="date-total-table">
            <tr>
                <td class="blue">
                    <span class="date-total-label">To‘lanadigan</span>
                    <span class="date-total-value">
                        <?= number_format($date_sum_all_product, 2) ?> $
                    </span>
                </td>

                <td class="green">
                    <span class="date-total-label">To‘l jami</span>
                    <span class="date-total-value">
                        <?= number_format($date_sum_all_paid_dollar, 2) ?> $
                    </span>
                </td>

                <td class="red">
                    <span class="date-total-label">Dollar</span>
                    <span class="date-total-value">
                        <?= number_format($date_sum_paid_dollar, 2) ?> $
                    </span>
                </td>

                <td class="orange">
                    <span class="date-total-label">Karta</span>
                    <span class="date-total-value">
                        <?= number_format($date_sum_paid_cart, 2) ?>
                    </span>
                </td>

                <td class="dark">
                    <span class="date-total-label">Naqt</span>
                    <span class="date-total-value">
                        <?= number_format($date_sum_paid_som, 2) ?>
                    </span>
                </td>

                <td class="purple">
                    <span class="date-total-label">Qaytim</span>
                    <span class="date-total-value">
                        <?= number_format($date_sum_paid_zdacha_sum, 2) ?>
                        (<?= number_format($date_sum_paid_zdacha_dollar, 2) ?> $)
                    </span>
                </td>

                <td class="green">
                    <span class="date-total-label">Qarz jami</span>
                    <span class="date-total-value">
                        <?= number_format($date_sum_paid_debt, 2) ?> $
                    </span>
                </td>

                <td class="red">
                    <span class="date-total-label">Qarz dollar</span>
                    <span class="date-total-value">
                        <?= number_format($date_sum_debt_dollar, 2) ?> $
                    </span>
                </td>

                <td class="dark">
                    <span class="date-total-label">Qarz naqt</span>
                    <span class="date-total-value">
                        <?= number_format($date_sum_debt_som, 2) ?>
                    </span>
                </td>

                <td class="orange">
                    <span class="date-total-label">Qarz karta</span>
                    <span class="date-total-value">
                        <?= number_format($date_sum_debt_cart, 2) ?>
                    </span>
                </td>

                <td class="purple">
                    <span class="date-total-label">Qarz qaytim</span>
                    <span class="date-total-value">
                        <?= number_format($date_sum_debt_zdacha_sum, 2) ?>
                        (<?= number_format($date_sum_debt_zdacha_dollar, 2) ?> $)
                    </span>
                </td>
            </tr>
        </table>
    </div>

<?php endforeach; ?>