<?php
use yii\helpers\Html;
?>

<style>
    body {
        font-family: dejavusans;
        font-size: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    th {
        background-color: #cccccc;
        color: #000;
        font-weight: bold;
        text-align: center;
        font-size: 9px;
        padding: 5px;
        border: 1px solid #555;
        vertical-align: middle;
    }

    td {
        border: 1px solid #555;
        padding: 5px;
        font-size: 9px;
        vertical-align: middle;
        word-wrap: break-word;
    }

    .order-row td {
        color: #007000;
        font-weight: bold;
    }

    .debt-row td {
        color: #b00000;
        font-weight: bold;
    }

    .date-row td {
        background-color: #e9f7fc;
        color: #000;
        font-weight: bold;
        font-size: 11px;
    }

    .total-row td {
        background-color: rgb(214, 183, 183);
        color: #000;
        font-weight: bold;
        font-size: 9px;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .payment-cell {
        font-weight: bold;
        font-size: 9px;
    }

    .total-small {
        font-size: 8.5px;
        line-height: 1.3;
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
</style>

<table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:17%;">Mijoz</th>
            <th style="width:13%;">Hodim</th>
            <th style="width:10%;">To‘lanadigan summa ($)</th>
            <th style="width:11%;">To‘langan summa ($)</th>
            <th style="width:11%;">To‘langan qarz ($)</th>
            <th style="width:8%;">Foyda ($)</th>
            <th style="width:10%;">Umumiy qarz ($)</th>
            <th style="width:11%;">Sana</th>
            <th style="width:5%;">Holat</th>
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
            $sum_profit = 0;

            $sum_paid = 0;
            $sum_paid_debt = 0;
            $sum_total_debt = 0;

            $sum_paid_som = 0;
            $sum_paid_cart = 0;
            $sum_paid_transfers = 0;

            $sum_paid_zdacha_sum = 0;
            $sum_paid_zdacha_dollar = 0;

            $sum_paid_debt_som = 0;
            $sum_paid_debt_cart = 0;
            $sum_paid_debt_transfers = 0;

            $sum_paid_debt_zdacha_sum = 0;
            $sum_paid_debt_zdacha_dollar = 0;
        ?>

            <tr class="date-row">
                <td colspan="10">
                    <?= date('d.m.Y', strtotime($date)) ?>
                </td>
            </tr>

            <?php foreach ($groupRows as $row): ?>
                <?php
                    $isDebt = (($row['action'] ?? '') === 'Qarz to‘lagan');
                    $isOrder = (($row['action'] ?? '') === 'Buyurtma qilgan');
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
                        <?= number_format(($row['all_product_sum'] ?? 0), 2) ?>
                    </td>

                    <td class="text-right payment-cell">
                        <?php if ($isOrder): ?>
                            <?= number_format($row['all_summ_dollar'] ?? 0, 2) ?>
                        <?php endif; ?>
                    </td>

                    <td class="text-right payment-cell">
                        <?php if ($isDebt): ?>
                            <?= number_format($row['paid_debt'] ?? 0, 2) ?>
                        <?php endif; ?>
                    </td>

                    <td class="text-right">
                        <?= number_format(($row['all_profit_dollar'] ?? 0), 2) ?>
                    </td>

                    <td class="text-right">
                        <?= number_format(($row['total_debt'] ?? 0), 2) ?>
                    </td>

                    <td class="text-center">
                        <?= date('Y-m-d H:i', strtotime($row['datetime'])) ?>
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
                $sum_all_product += ($row['all_product_sum'] ?? 0);
                $sum_profit      += ($row['all_profit_dollar'] ?? 0);

                $sum_paid += ($row['all_summ_dollar'] ?? 0);
                $sum_paid_som += ($row['sum_som'] ?? 0);
                $sum_paid_cart += ($row['sum_cart'] ?? 0);
                $sum_paid_transfers += ($row['sum_transfers'] ?? 0);

                $sum_paid_zdacha_sum += ($row['zdacha_sum'] ?? 0);
                $sum_paid_zdacha_dollar += ($row['zdacha_dollar'] ?? 0);

                $sum_paid_debt += ($row['paid_debt'] ?? 0);
                $sum_paid_debt_som += ($row['debt_sum_som'] ?? 0);
                $sum_paid_debt_cart += ($row['debt_summ_cart'] ?? 0);
                $sum_paid_debt_transfers += ($row['debt_sum_transfers'] ?? 0);

                $sum_paid_debt_zdacha_sum += ($row['debt_zdacha_sum'] ?? 0);
                $sum_paid_debt_zdacha_dollar += ($row['debt_zdacha_dollar'] ?? 0);

                $sum_total_debt += ($row['total_debt'] ?? 0);
                ?>
            <?php endforeach; ?>

            <tr class="total-row">
                <td colspan="3" class="text-right">
                    Umumiy summa:
                </td>

                <td class="text-right">
                    <?= number_format($sum_all_product, 2) ?>
                </td>

                <td class="payment-cell">
                    <div>
                        <span style="color: green;" class="total-small">
                            <b>Jami($):</b> <?= number_format($sum_paid ?? 0, 2) ?>
                        </span>
                    </div>

                    <div>
                        <span style="color:rgb(184, 27, 22);" class="total-small">
                            <b>$:</b> <?= number_format($sum_paid_transfers ?? 0, 2) ?>
                        </span>
                    </div>

                    <div>
                        <span style="color:#333;" class="total-small">
                            <b>N:</b> <?= number_format($sum_paid_som ?? 0, 2) ?>
                        </span>
                    </div>

                    <div>
                        <span style="color:#007bff;" class="total-small">
                            <b>K:</b> <?= number_format($sum_paid_cart ?? 0, 2) ?>
                        </span>
                    </div>

                    <div>
                        <span style="color:#333;" class="total-small">
                            <b>Qaytim:</b> <?= number_format($sum_paid_zdacha_sum ?? 0, 2) ?>
                            (<?= number_format($sum_paid_zdacha_dollar ?? 0, 2) ?> $)
                        </span>
                    </div>
                </td>

                <td class="payment-cell">
                    <div>
                        <span style="color: green;" class="total-small">
                            <b>Jami($):</b> <?= number_format($sum_paid_debt ?? 0, 2) ?>
                        </span>
                    </div>

                    <div>
                        <span style="color:rgb(184, 27, 22);" class="total-small">
                            <b>$:</b> <?= number_format($sum_paid_debt_transfers ?? 0, 2) ?>
                        </span>
                    </div>

                    <div>
                        <span style="color:#333;" class="total-small">
                            <b>N:</b> <?= number_format($sum_paid_debt_som ?? 0, 2) ?>
                        </span>
                    </div>

                    <div>
                        <span style="color:#007bff;" class="total-small">
                            <b>K:</b> <?= number_format($sum_paid_debt_cart ?? 0, 2) ?>
                        </span>
                    </div>

                    <div>
                        <span style="color:#333;" class="total-small">
                            <b>Qaytim:</b> <?= number_format($sum_paid_debt_zdacha_sum ?? 0, 2) ?>
                            (<?= number_format($sum_paid_debt_zdacha_dollar ?? 0, 2) ?> $)
                        </span>
                    </div>
                </td>

                <td class="text-right">
                    <?= number_format($sum_profit, 2) ?>
                </td>

                <td class="text-right">
                    <?= number_format($sum_total_debt, 2) ?>
                </td>

                <td colspan="2"></td>
            </tr>

        <?php endforeach; ?>
    </tbody>
</table>