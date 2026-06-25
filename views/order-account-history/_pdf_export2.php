<?php

use yii\helpers\Html;

?>

<style>
    body {
        font-family: dejavusans;
        font-size: 9.5px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 9.5px;
    }

    th {
        background-color: #d9edf7;
        color: #000;
        font-weight: bold;
        text-align: center;
        font-size: 8px;
        padding: 4px;
        border: 1px solid #777;
    }

    td {
        border: 1px solid #777;
        padding: 4px;
        font-size: 8px;
        vertical-align: middle;
    }

    .date-row td {
        background-color: #e9f7fc;
        font-weight: bold;
        font-size: 11px;
        color: #000;
    }

    .total-row td {
        background-color: #f2dede;
        font-weight: bold;
        color: #000;
        font-size: 8px;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .status-plus {
        color: #007000;
        font-size: 17px;
        font-weight: bold;
    }

    .status-minus {
        color: #b00000;
        font-size: 17px;
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
</style>

<h3 style="text-align:center; margin-bottom:5px;">
    Buyurtmalar va qarzlar PDF2
</h3>

<?php if (!empty($customer_name) || !empty($start_date) || !empty($end_date)): ?>
    <div style="font-size:11px; margin-bottom:8px;">
        <?php if (!empty($customer_name)): ?>
            <b>Mijoz:</b> <?= Html::encode($customer_name) ?>
        <?php endif; ?>

        <?php if (!empty($start_date) && !empty($end_date)): ?>
            &nbsp;&nbsp;
            <b>Sana:</b> <?= Html::encode($start_date) ?> - <?= Html::encode($end_date) ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

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
        <?php
        $grouped = [];

        foreach ($rows as $row) {
            $key = date('Y-m-d', strtotime($row['datetime']));
            $grouped[$key][] = $row;
        }

        foreach ($grouped as $date => $groupRows):

            $counter = 1;

            $sum_all_product = 0;
            $sum_all_paid_dollar = 0;
            $sum_paid_dollar = 0;
            $sum_paid_cart = 0;
            $sum_paid_som = 0;

            $sum_paid_zdacha_sum = 0;
            $sum_paid_zdacha_dollar = 0;

            $sum_paid_debt = 0;
            $sum_total_debt = 0;

            $sum_debt_dollar = 0;
            $sum_debt_som = 0;
            $sum_debt_cart = 0;

            $sum_debt_zdacha_sum = 0;
            $sum_debt_zdacha_dollar = 0;
        ?>

            <tr class="date-row">
                <td colspan="15">
                    <?= date('d.m.Y', strtotime($date)) ?>
                </td>
            </tr>

            <?php foreach ($groupRows as $row): ?>

                <?php
                    $isOrder = (($row['action'] ?? '') === 'Buyurtma qilgan');
                    $isDebt = (($row['action'] ?? '') === 'Qarz to‘lagan');

                    $allProductSum = $row['all_product_sum'] ?? 0;

                    // Buyurtma to‘lovlari
                    $allPaidDollar = $isOrder ? ($row['all_summ_dollar'] ?? 0) : 0;
                    $paidDollar = $isOrder ? ($row['sum_transfers'] ?? 0) : 0;
                    $paidCart = $isOrder ? ($row['sum_cart'] ?? 0) : 0;
                    $paidSom = $isOrder ? ($row['sum_som'] ?? 0) : 0;

                    $paidZdachaSum = $isOrder ? ($row['zdacha_sum'] ?? 0) : 0;
                    $paidZdachaDollar = $isOrder ? ($row['zdacha_dollar'] ?? 0) : 0;

                    // Qarz to‘lovlari
                    $paidDebt = $isDebt ? ($row['paid_debt'] ?? 0) : 0;

                    $debtDollar = $isDebt ? ($row['debt_sum_transfers'] ?? 0) : 0;
                    $debtSom = $isDebt ? ($row['debt_sum_som'] ?? 0) : 0;
                    $debtCart = $isDebt ? ($row['debt_summ_cart'] ?? 0) : 0;

                    $debtZdachaSum = $isDebt ? ($row['debt_zdacha_sum'] ?? 0) : 0;
                    $debtZdachaDollar = $isDebt ? ($row['debt_zdacha_dollar'] ?? 0) : 0;

                    $totalDebt = $row['total_debt'] ?? 0;

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
                        <?= number_format($allProductSum, 2) ?>
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
                    $sum_all_product += $allProductSum;

                    $sum_all_paid_dollar += $allPaidDollar;
                    $sum_paid_dollar += $paidDollar;
                    $sum_paid_cart += $paidCart;
                    $sum_paid_som += $paidSom;

                    $sum_paid_zdacha_sum += $paidZdachaSum;
                    $sum_paid_zdacha_dollar += $paidZdachaDollar;

                    $sum_paid_debt += $paidDebt;
                    $sum_total_debt += $totalDebt;

                    $sum_debt_dollar += $debtDollar;
                    $sum_debt_som += $debtSom;
                    $sum_debt_cart += $debtCart;

                    $sum_debt_zdacha_sum += $debtZdachaSum;
                    $sum_debt_zdacha_dollar += $debtZdachaDollar;
                ?>

            <?php endforeach; ?>

            <tr class="total-row">
                <td colspan="3" class="text-right">
                    Umumiy summa:
                </td>

                <td class="text-right">
                    <?= number_format($sum_all_product, 2) ?>
                </td>

                <td class="text-right">
                    <?= number_format($sum_all_paid_dollar, 2) ?>
                </td>

                <td class="text-right">
                    <?= number_format($sum_paid_dollar, 2) ?>
                </td>

                <td class="text-right">
                    <?= number_format($sum_paid_cart, 2) ?>
                </td>

                <td class="text-right">
                    <?= number_format($sum_paid_som, 2) ?>
                </td>

                <td class="text-right">
                    <?= number_format($sum_paid_zdacha_sum, 2) ?> (<?= number_format($sum_paid_zdacha_dollar, 2) ?> $)
                </td>

                <td class="text-right">
                    <?= number_format($sum_paid_debt, 2) ?>
                </td>

                <td class="text-right">
                    <?= number_format($sum_debt_dollar, 2) ?>
                </td>

                <td class="text-right">
                    <?= number_format($sum_debt_som, 2) ?>
                </td>

                <td class="text-right">
                    <?= number_format($sum_debt_cart, 2) ?>
                </td>

                <td class="text-right">
                    <?= number_format($sum_debt_zdacha_sum, 2) ?> (<?= number_format($sum_debt_zdacha_dollar, 2) ?> $)
                </td>

                <td></td>
            </tr>

        <?php endforeach; ?>
    </tbody>
</table>