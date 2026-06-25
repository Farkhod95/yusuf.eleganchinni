<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

$this->title = 'Buyurtmalar va qarzlar';

$rows = $provider->getModels();

$userPermission = Yii::$app->user->identity->permission ?? null;
$isAdminPermission = ((int)$userPermission === 1);

$selectedUserType = $selectedUserType ?? Yii::$app->request->get('user_type');
$userTypes = $userTypes ?? (new \app\models\Users())->getType();

$activeTab = Yii::$app->request->get('active_tab', $isAdminPermission ? 'main' : 'worker');

if (!$isAdminPermission) {
    $activeTab = 'worker';
}

if (!in_array($activeTab, ['main', 'worker'])) {
    $activeTab = $isAdminPermission ? 'main' : 'worker';
}

$isDateSelected = !empty($startDate) && !empty($endDate);

$dateDiffDays = 0;
$isDateRangeTooLong = false;

if ($isDateSelected) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);

    $dateDiffDays = $start->diff($end)->days + 1;

    if ($dateDiffDays > 3) {
        $isDateRangeTooLong = true;
    }
}
?>

<style>
.table {
    background-color: #d0e7ff !important;
    color: #000 !important;
    border-color: #a7c6e6 !important;
    margin-bottom: 0 !important;
}

.table thead {
    background-color: rgb(167, 201, 238) !important;
    color: #000 !important;
}

.table thead th {
    background-color: rgb(169, 198, 228) !important;
    color: #000 !important;
    font-weight: bold !important;
}

.table tbody tr {
    background-color: #d0e7ff !important;
}

.table tbody tr:nth-child(even) {
    background-color: #c0dcff !important;
}

.table td,
.table th {
    font-size: 14px !important;
    vertical-align: middle !important;
    padding: 8px !important;
    border-color: rgb(165, 179, 179) !important;
}

.table tbody tr.order-row td {
    color: #007000 !important;
    font-weight: bold !important;
}

.table tbody tr.debt-row td {
    color: #b00000 !important;
    font-weight: bold !important;
}

.table tbody tr.date-row td {
    color: rgb(187, 25, 25) !important;
    font-weight: bold !important;
    background-color: rgb(185, 217, 231) !important;
}

.table tbody tr.total-row td {
    color: #348fe2 !important;
    font-weight: bold !important;
    background-color: #f7fbff !important;
}

.status-plus {
    color: #007000 !important;
    font-size: 22px !important;
    font-weight: bold !important;
    line-height: 1 !important;
}

.status-minus {
    color: #b00000 !important;
    font-size: 22px !important;
    font-weight: bold !important;
    line-height: 1 !important;
}

.order-debt-panel {
    position: relative;
    z-index: 1;
    isolation: isolate;
}

.order-debt-panel .panel-heading {
    position: sticky;
    top: 50px;
    z-index: 800;
    background: #398787;
}

.order-debt-sticky-filters {
    position: sticky;
    top: 91px;
    z-index: 790;
    background: rgb(210, 226, 245);
    padding: 14px 15px 10px 15px;
    margin-left: -15px;
    margin-right: -15px;
    border-bottom: 1px solid #b7cbe0;
}

.order-debt-table-wrap {
    max-height: calc(100vh - 250px);
    overflow: auto;
    position: relative;
    z-index: 1;
    background: rgb(210, 226, 245);
}

.order-debt-table-wrap .table thead th {
    position: sticky;
    top: 0;
    z-index: 20;
}

.order-debt-table-wrap .table thead tr {
    position: relative;
    z-index: 20;
}

.order-debt-pagination {
    padding-top: 15px;
}

.order-debt-sticky-filters .form-control,
.order-debt-sticky-filters .select2-container--krajee .select2-selection {
    min-height: 34px;
}

.order-debt-sticky-filters .btn {
    vertical-align: top;
}

.order-debt-tabs {
    margin-top: 10px;
    margin-bottom: 10px;
    background: #d2e2f5;
    position: relative;
    z-index: 2;
}

.order-debt-tabs > li > a {
    font-weight: bold;
    color: #000;
    background: #c0dcff;
    border: 1px solid #a7c6e6;
}

.order-debt-tabs > li.active > a,
.order-debt-tabs > li.active > a:hover,
.order-debt-tabs > li.active > a:focus {
    background: #348fe2;
    color: #fff;
    font-weight: bold;
}

.worker-date-box {
    margin-bottom: 25px;
    border: 2px solid #9fc5e8;
    background: #eef7ff;
    padding: 12px;
    border-radius: 6px;
}

.worker-date-title {
    background: #348fe2;
    color: #fff;
    padding: 10px 14px;
    font-size: 18px;
    font-weight: bold;
    border-radius: 4px;
    margin-bottom: 12px;
}

.worker-type-box {
    margin-bottom: 18px;
    border: 1px solid #b7cbe0;
    background: #ffffff;
    border-radius: 5px;
    overflow: hidden;
}

.worker-type-title {
    background: #d9edf7;
    color: #000;
    padding: 9px 12px;
    font-size: 16px;
    font-weight: bold;
    border-bottom: 1px solid #b7cbe0;
}

.worker-simple-table {
    margin-bottom: 0 !important;
}

.worker-simple-table th {
    text-align: center;
    white-space: nowrap;
}

.worker-simple-table td {
    white-space: nowrap;
}

.worker-total-row td {
    background: #fff3cd !important;
    color: #000 !important;
    font-weight: bold !important;
}

.worker-empty {
    padding: 15px;
    background: #fff;
    border: 1px solid #ddd;
    color: #777;
    font-weight: bold;
}

.worker-date-total-box {
    border: 2px solid #8cc152;
    background: #f2fbec;
    border-radius: 8px;
    margin-top: 16px;
    overflow: hidden;
}

.worker-date-total-title {
    background: #8cc152;
    color: #fff;
    padding: 10px 14px;
    font-size: 17px;
    font-weight: bold;
}

.worker-date-total-items {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 12px;
}

.worker-total-card {
    background: #fff;
    border: 1px solid #cfe8bf;
    border-left: 5px solid #8cc152;
    border-radius: 6px;
    padding: 8px 12px;
    min-width: 170px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.worker-total-card .label-text {
    display: block;
    font-size: 12px;
    color: #555;
    font-weight: bold;
    margin-bottom: 3px;
}

.worker-total-card .value-text {
    display: block;
    font-size: 15px;
    color: #000;
    font-weight: bold;
}

.worker-total-card.blue {
    border-left-color: #348fe2;
}

.worker-total-card.red {
    border-left-color: #d9534f;
}

.worker-total-card.green {
    border-left-color: #5cb85c;
}

.worker-total-card.orange {
    border-left-color: #f0ad4e;
}

.worker-total-card.purple {
    border-left-color: #7e57c2;
}

.worker-total-card.dark {
    border-left-color: #555;
}

/* ==========================================================
   TAB CONTENT ICHIDAGI TEXTLARNI KICHIKROQ QILISH
   ========================================================== */

.tab-content {
    font-size: 12px !important;
}

/* Tab ichidagi umumiy jadval textlari */
.tab-content .table td,
.tab-content .table th {
    font-size: 12px !important;
    padding: 6px !important;
    line-height: 1.25 !important;
    white-space: nowrap;
}

/* Jadval headerlari */
.tab-content .table thead th {
    font-size: 12px !important;
    font-weight: bold !important;
}

/* Inline style bilan berilgan font-size:14px larni bosish */
.tab-content td[style],
.tab-content th[style] {
    font-size: 12px !important;
}

/* Ichki div/span/b lar */
.tab-content td div,
.tab-content td span,
.tab-content td b {
    font-size: 12px !important;
    line-height: 1.25 !important;
}

/* Umumiy summa ichidagi mayda textlar */
.tab-content .total-row div,
.tab-content .total-row span {
    font-size: 11px !important;
    line-height: 1.25 !important;
}

/* Sana qatori */
.tab-content .date-row td {
    font-size: 12px !important;
    padding: 7px !important;
}

/* + va - belgilar */
.tab-content .status-plus,
.tab-content .status-minus {
    font-size: 18px !important;
}

/* Hodim turi bo‘yicha: sana title */
.tab-content .worker-date-title {
    font-size: 15px !important;
    padding: 8px 12px !important;
    margin-bottom: 10px !important;
}

/* Hodim turi title */
.tab-content .worker-type-title {
    font-size: 13px !important;
    padding: 7px 10px !important;
}

/* Hodim turi jadvali yanada ixcham */
.tab-content .worker-simple-table th {
    font-size: 11px !important;
    padding: 5px !important;
}

.tab-content .worker-simple-table td {
    font-size: 11px !important;
    padding: 5px !important;
}

/* Worker jami row */
.tab-content .worker-total-row td {
    font-size: 11px !important;
    padding: 5px !important;
}

/* Sana bo‘yicha jami title */
.tab-content .worker-date-total-title {
    font-size: 14px !important;
    padding: 8px 12px !important;
}

/* Sana bo‘yicha jami kartochkalar */
.tab-content .worker-date-total-items {
    gap: 7px !important;
    padding: 9px !important;
}

.tab-content .worker-total-card {
    min-width: 145px !important;
    padding: 6px 9px !important;
}

.tab-content .worker-total-card .label-text {
    font-size: 10px !important;
    margin-bottom: 2px !important;
}

.tab-content .worker-total-card .value-text {
    font-size: 12px !important;
}

/* Bo‘sh ma’lumot text */
.tab-content .worker-empty {
    font-size: 12px !important;
    padding: 12px !important;
}

/* Print button ixcham */
.tab-content .btn-xs {
    padding: 3px 6px !important;
    font-size: 11px !important;
}

/* Mijoz va Hodim ustunlari o‘qilishi uchun */
.tab-content .table td:nth-child(2),
.tab-content .table td:nth-child(3) {
    white-space: normal !important;
    min-width: 105px;
}

/* Umumiy hisobotdagi keng ustunlarni ixcham qilish */
.tab-content #tab-main-report .table td,
.tab-content #tab-main-report .table th {
    font-size: 12px !important;
}

/* Hodim turi bo‘yicha tab yanada kichikroq */
.tab-content #tab-worker-type-report .table td,
.tab-content #tab-worker-type-report .table th {
    font-size: 11px !important;
}

/* Hodim turi bo‘yicha ichki textlar */
.tab-content #tab-worker-type-report td div,
.tab-content #tab-worker-type-report td span,
.tab-content #tab-worker-type-report td b {
    font-size: 11px !important;
}

/* Pagination textlari */
.tab-content .pagination > li > a,
.tab-content .pagination > li > span {
    font-size: 12px !important;
    padding: 5px 9px !important;
}

/* Juda kichik ekranlarda yanada ixcham */
@media (max-width: 1366px) {
    .tab-content .table td,
    .tab-content .table th {
        font-size: 11px !important;
        padding: 5px !important;
    }

    .tab-content td div,
    .tab-content td span,
    .tab-content td b {
        font-size: 11px !important;
    }

    .tab-content .worker-total-card {
        min-width: 130px !important;
        padding: 5px 8px !important;
    }

    .tab-content .worker-total-card .label-text {
        font-size: 9.5px !important;
    }

    .tab-content .worker-total-card .value-text {
        font-size: 11px !important;
    }
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse order-debt-panel">
            <div class="panel-heading">
                <h4 class="panel-title">Buyurtmalar va qarzlar</h4>
            </div>

            <div class="panel-body" style="background-color:rgb(210, 226, 245);">
                <div class="row row-space-12">

                    <div class="order-debt-sticky-filters">
                        <?php $form = ActiveForm::begin([
                            'method' => 'get',
                            'action' => Url::to(['order-account-history/order-and-debt']),
                            'options' => [
                                'class' => 'form-inline',
                                'style' => 'margin-bottom: 0; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 10px;'
                            ]
                        ]); ?>

                        <div class="form-group" style="margin-right:10px;">
                            <?= Select2::widget([
                                'name' => 'customer_name',
                                'id' => 'myselect',
                                'data' => ArrayHelper::map($clients, 'fio', 'fio'),
                                'value' => $selectedFio,
                                'options' => [
                                    'placeholder' => 'Mijoz tanlang'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'width' => '250px',
                                ],
                            ]); ?>
                        </div>

                        <div class="form-group" style="margin-right:10px;">
                            <?= Select2::widget([
                                'name' => 'user_type',
                                'id' => 'user-type-select',
                                'data' => $userTypes,
                                'value' => $selectedUserType,
                                'options' => [
                                    'placeholder' => 'Hodim turi tanlang'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'width' => '220px',
                                ],
                            ]); ?>
                        </div>

                        <div class="form-group" style="margin-right:10px;">
                            <?= Html::input('date', 'start_date', $startDate, [
                                'class' => 'form-control'
                            ]) ?>
                        </div>

                        <div class="form-group" style="margin-right:10px;">
                            <?= Html::input('date', 'end_date', $endDate, [
                                'class' => 'form-control'
                            ]) ?>
                        </div>

                        <?= Html::hiddenInput('active_tab', $activeTab) ?>

                        <?= Html::submitButton('Qidirish', [
                            'class' => 'btn btn-primary'
                        ]) ?>

                        <?= Html::a('Tozalash', ['order-account-history/order-and-debt'], [
                            'class' => 'btn btn-default'
                        ]) ?>

                        <div class="form-group" style="margin-left:auto;">
                            <?php if ($isDateSelected): ?>

                                <?php if ($isAdminPermission): ?>
                                    <?= Html::a('<i class="fa fa-file-pdf-o"></i> PDF1', [
                                        'order-account-history/export-pdf',
                                        'customer_name' => $selectedFio,
                                        'user_type' => $selectedUserType,
                                        'start_date' => $startDate,
                                        'end_date' => $endDate
                                    ], [
                                        'class' => 'btn btn-warning pdf-date-check',
                                        'target' => '_blank',
                                        'data-too-long' => $isDateRangeTooLong ? 1 : 0,
                                        'data-days' => $dateDiffDays,
                                    ]) ?>

                                    <?= Html::a('<i class="fa fa-file-pdf-o"></i> PDF2 Hisob uchun', [
                                        'order-account-history/export-pdf2',
                                        'customer_name' => $selectedFio,
                                        'user_type' => $selectedUserType,
                                        'start_date' => $startDate,
                                        'end_date' => $endDate
                                    ], [
                                        'class' => 'btn btn-danger pdf-date-check',
                                        'target' => '_blank',
                                        'data-too-long' => $isDateRangeTooLong ? 1 : 0,
                                        'data-days' => $dateDiffDays,
                                    ]) ?>
                                <?php endif; ?>

                                <?= Html::a('<i class="fa fa-file-pdf-o"></i> PDF3 Hodim turi', [
                                    'order-account-history/export-pdf3',
                                    'customer_name' => $selectedFio,
                                    'user_type' => $selectedUserType,
                                    'start_date' => $startDate,
                                    'end_date' => $endDate
                                ], [
                                    'class' => 'btn btn-info pdf-date-check',
                                    'target' => '_blank',
                                    'data-too-long' => $isDateRangeTooLong ? 1 : 0,
                                    'data-days' => $dateDiffDays,
                                ]) ?>

                            <?php else: ?>

                                <?php if ($isAdminPermission): ?>
                                    <?= Html::button('<i class="fa fa-file-pdf-o"></i> PDF1', [
                                        'class' => 'btn btn-warning',
                                        'disabled' => true,
                                        'title' => 'Avval sana oralig‘ini tanlang'
                                    ]) ?>

                                    <?= Html::button('<i class="fa fa-file-pdf-o"></i> PDF2 Hisob uchun', [
                                        'class' => 'btn btn-danger',
                                        'disabled' => true,
                                        'title' => 'Avval sana oralig‘ini tanlang'
                                    ]) ?>
                                <?php endif; ?>

                                <?= Html::button('<i class="fa fa-file-pdf-o"></i> PDF3 Hodim turi', [
                                    'class' => 'btn btn-info',
                                    'disabled' => true,
                                    'title' => 'Avval sana oralig‘ini tanlang'
                                ]) ?>

                            <?php endif; ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>

                    <ul class="nav nav-tabs order-debt-tabs">
                        <?php if ($isAdminPermission): ?>
                            <li class="<?= $activeTab === 'main' ? 'active' : '' ?>">
                                <a href="#tab-main-report" data-toggle="tab" data-tab-name="main">
                                    Umumiy hisobot
                                </a>
                            </li>

                            <li class="<?= $activeTab === 'worker' ? 'active' : '' ?>">
                                <a href="#tab-worker-type-report" data-toggle="tab" data-tab-name="worker">
                                    Hodim turi bo‘yicha
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="active">
                                <a href="#tab-worker-type-report" data-toggle="tab" data-tab-name="worker">
                                    Hodim turi bo‘yicha
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div class="tab-content">

                        <?php if ($isAdminPermission): ?>
                            <div class="tab-pane fade <?= $activeTab === 'main' ? 'in active' : '' ?>" id="tab-main-report">
                                <div class="table-responsive order-debt-table-wrap">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th style="color:rgb(187, 25, 25) !important;font-weight: bold;font-size:14px">#</th>
                                                <th style="color:rgb(187, 25, 25) !important;font-weight: bold;font-size:14px">Mijoz</th>
                                                <th style="color:rgb(187, 25, 25) !important;font-weight: bold;font-size:14px">Hodim</th>
                                                <th style="color:rgb(187, 25, 25) !important;font-weight: bold;font-size:14px">To‘lanadigan summa ($)</th>
                                                <th style="color:rgb(187, 25, 25) !important;font-weight: bold;font-size:14px">To‘langan summa ($)</th>
                                                <th style="color:rgb(187, 25, 25) !important;font-weight: bold;font-size:14px">To‘langan qarz ($)</th>
                                                <th style="color:rgb(187, 25, 25) !important;font-weight: bold;font-size:14px">Foyda ($)</th>
                                                <th style="color:rgb(187, 25, 25) !important;font-weight: bold;font-size:14px">Umumiy qolgan qarz ($)</th>
                                                <th style="color:rgb(187, 25, 25) !important;font-weight: bold;font-size:14px">Vaqti</th>
                                                <th style="color:rgb(187, 25, 25) !important;font-weight: bold;font-size:14px">Holati</th>
                                                <th style="color:rgb(187, 25, 25) !important;font-weight: bold;font-size:14px"></th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php
                                            $grouped = [];

                                            foreach ($rows as $row) {
                                                $dateKey = date('Y-m-d', strtotime($row['datetime']));
                                                $grouped[$dateKey][] = $row;
                                            }

                                            foreach ($grouped as $date => $groupRows):
                                                $counter = 1;

                                                $sum_all_product = 0;
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

                                                $sum_profit = 0;
                                            ?>

                                                <tr class="date-row">
                                                    <td colspan="11">
                                                        <?= date('d.m.Y', strtotime($date)) ?>
                                                    </td>
                                                </tr>

                                                <?php foreach ($groupRows as $row): ?>
                                                    <?php
                                                        $action = $row['action'] ?? '';
                                                        $isDebt = ($action === 'Qarz to‘lagan');
                                                        $isOrder = ($action === 'Buyurtma qilgan');
                                                        $rowClass = $isDebt ? 'debt-row' : 'order-row';

                                                        $allProductSum = (float)($row['all_product_sum'] ?? 0);
                                                        $allSummDollar = (float)($row['all_summ_dollar'] ?? 0);
                                                        $paidDebt = (float)($row['paid_debt'] ?? 0);
                                                        $allProfitDollar = (float)($row['all_profit_dollar'] ?? 0);
                                                        $totalDebt = (float)($row['total_debt'] ?? 0);

                                                        $sumSom = (float)($row['sum_som'] ?? 0);
                                                        $sumCart = (float)($row['sum_cart'] ?? 0);
                                                        $sumTransfers = (float)($row['sum_transfers'] ?? 0);

                                                        $debtSumSom = (float)($row['debt_sum_som'] ?? 0);
                                                        $debtSummCart = (float)($row['debt_summ_cart'] ?? 0);
                                                        $debtSumTransfers = (float)($row['debt_sum_transfers'] ?? 0);

                                                        $zdachaSum = (float)($row['zdacha_sum'] ?? 0);
                                                        $zdachaDollar = (float)($row['zdacha_dollar'] ?? 0);
                                                    ?>

                                                    <tr class="<?= $rowClass ?>">
                                                        <td style="font-weight: bold;font-size:14px">
                                                            <?= $counter++ ?>
                                                        </td>

                                                        <td style="font-weight: bold;font-size:14px">
                                                            <b><?= Html::encode($row['client_name'] ?? 'Nomaʼlum mijoz') ?></b>
                                                        </td>

                                                        <td style="font-weight: bold;font-size:14px">
                                                            <?= Html::encode($row['created_by_name'] ?? '-') ?>
                                                        </td>

                                                        <td style="font-weight: bold;font-size:14px">
                                                            <?= number_format($allProductSum, 2) ?>
                                                        </td>

                                                        <td style="font-weight: bold; font-size:14px; min-width:200px;">
                                                            <?php if ($isOrder): ?>
                                                                <?= number_format($allSummDollar, 2) ?>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td style="font-weight: bold; font-size:14px; min-width:200px;">
                                                            <?php if ($isDebt): ?>
                                                                <?= number_format($paidDebt, 2) ?>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td style="font-weight: bold;font-size:14px">
                                                            <?= number_format($allProfitDollar, 2) ?>
                                                        </td>

                                                        <td style="font-weight: bold;font-size:14px">
                                                            <?= number_format($totalDebt, 2) ?>
                                                        </td>

                                                        <td style="font-weight: bold;font-size:14px">
                                                            <?= date('d.m.Y H:i', strtotime($row['datetime'])) ?>
                                                        </td>

                                                        <td class="text-center" style="font-weight:bold;">
                                                            <?php if ($isDebt): ?>
                                                                <span class="status-minus">−</span>
                                                            <?php else: ?>
                                                                <span class="status-plus">+</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td>
                                                            <?= Html::a('<span class="glyphicon glyphicon-print"></span>', [
                                                                $isOrder ? '/order-account-history/print' : '/debt-repayment/print-debt',
                                                                'id' => $row['id']
                                                            ], [
                                                                'class' => 'btn btn-warning btn-xs',
                                                                'role' => 'modal-remote',
                                                                'data-toggle' => 'tooltip',
                                                                'title' => 'Chop qilish',
                                                                'target' => '_blank'
                                                            ]) ?>
                                                        </td>
                                                    </tr>

                                                    <?php
                                                        if ($isOrder) {
                                                            $sum_all_product += $allProductSum;
                                                            $sum_paid += $allSummDollar;
                                                            $sum_paid_som += $sumSom;
                                                            $sum_paid_cart += $sumCart;
                                                            $sum_paid_transfers += $sumTransfers;
                                                            $sum_paid_zdacha_sum += $zdachaSum;
                                                            $sum_paid_zdacha_dollar += $zdachaDollar;
                                                            $sum_profit += $allProfitDollar;
                                                        }

                                                        if ($isDebt) {
                                                            $sum_paid_debt += $paidDebt;
                                                            $sum_paid_debt_som += $debtSumSom;
                                                            $sum_paid_debt_cart += $debtSummCart;
                                                            $sum_paid_debt_transfers += $debtSumTransfers;
                                                            $sum_paid_debt_zdacha_sum += $zdachaSum;
                                                            $sum_paid_debt_zdacha_dollar += $zdachaDollar;
                                                        }

                                                        $sum_total_debt += $totalDebt;
                                                    ?>
                                                <?php endforeach; ?>

                                                <tr class="total-row">
                                                    <td colspan="3" class="text-right">Umumiy summa:</td>

                                                    <td>
                                                        <?= number_format($sum_all_product, 2) ?>
                                                    </td>

                                                    <td style="font-weight: bold; font-size:14px; min-width:200px;">
                                                        <div style="display:flex; flex-direction:column; gap:6px;">
                                                            <span style="color: green; font-weight:600;">
                                                                Jami ($): <?= number_format($sum_paid, 2) ?>
                                                            </span>

                                                            <div style="font-size:12px; color:#666; display:flex; flex-direction:column; gap:4px; padding-left:12px;">
                                                                <span style="color: rgb(184, 27, 22);">
                                                                    Dollar ($): <?= number_format($sum_paid_transfers, 2) ?>
                                                                </span>

                                                                <span style="color: #333;">
                                                                    Naqt: <?= number_format($sum_paid_som, 2) ?>
                                                                </span>

                                                                <span style="color: #007bff;">
                                                                    Karta: <?= number_format($sum_paid_cart, 2) ?>
                                                                </span>

                                                                <span style="color: #333;">
                                                                    Qaytim: <?= number_format($sum_paid_zdacha_sum, 2) ?> (<?= number_format($sum_paid_zdacha_dollar, 2) ?> $)
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td style="font-weight: bold; font-size:15px; min-width:200px;">
                                                        <div style="display:flex; flex-direction:column; gap:6px;">
                                                            <span style="color: green; font-weight:600;">
                                                                Jami ($): <?= number_format($sum_paid_debt, 2) ?>
                                                            </span>

                                                            <div style="font-size:12px; color:#666; display:flex; flex-direction:column; gap:4px; padding-left:12px;">
                                                                <span style="color: rgb(184, 27, 22);">
                                                                    Dollar ($): <?= number_format($sum_paid_debt_transfers, 2) ?>
                                                                </span>

                                                                <span style="color: #333;">
                                                                    Naqt: <?= number_format($sum_paid_debt_som, 2) ?>
                                                                </span>

                                                                <span style="color: #007bff;">
                                                                    Karta: <?= number_format($sum_paid_debt_cart, 2) ?>
                                                                </span>

                                                                <span style="color: #333;">
                                                                    Qaytim: <?= number_format($sum_paid_debt_zdacha_sum, 2) ?> (<?= number_format($sum_paid_debt_zdacha_dollar, 2) ?> $)
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <?= number_format($sum_profit, 2) ?>
                                                    </td>

                                                    <td>
                                                        <?= number_format($sum_total_debt, 2) ?>
                                                    </td>

                                                    <td colspan="4"></td>
                                                </tr>

                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="text-center order-debt-pagination main-pagination">
                                    <?= LinkPager::widget([
                                        'pagination' => $provider->getPagination(),
                                    ]) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="tab-pane fade <?= $activeTab === 'worker' ? 'in active' : '' ?>" id="tab-worker-type-report">

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

                                <div class="worker-empty">
                                    Ma’lumot topilmadi
                                </div>

                            <?php endif; ?>

                            <?php foreach ($groupedByDate as $date => $dateTypes): ?>

                                <div class="worker-date-box">

                                    <div class="worker-date-title">
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

                                        <div class="worker-type-box">

                                            <div class="worker-type-title">
                                                <?= Html::encode($typeName) ?>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped table-hover worker-simple-table">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Mijoz</th>
                                                            <th>Hodim</th>
                                                            <th>To‘lanadigan ($)</th>
                                                            <th>To‘l jami ($)</th>
                                                            <th>Dollar ($)</th>
                                                            <th>Karta</th>
                                                            <th>Naqt</th>
                                                            <th>Qaytim</th>
                                                            <th>Qarz jami ($)</th>
                                                            <th>Qarz dollar ($)</th>
                                                            <th>Qarz naqt</th>
                                                            <th>Qarz karta</th>
                                                            <th>Qarz qaytim</th>
                                                            <th>Holat</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        <?php $counter = 1; ?>

                                                        <?php foreach ($typeRows as $row): ?>

                                                            <?php
                                                            $isOrder = (($row['action'] ?? '') === 'Buyurtma qilgan');
                                                            $isDebt = (($row['action'] ?? '') === 'Qarz to‘lagan');

                                                            $rowClass = $isDebt ? 'debt-row' : 'order-row';

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

                                                            $debtZdachaSum = $isDebt ? (float)($row['zdacha_sum'] ?? 0) : 0;
                                                            $debtZdachaDollar = $isDebt ? (float)($row['zdacha_dollar'] ?? 0) : 0;
                                                            ?>

                                                            <tr class="<?= $rowClass ?>">
                                                                <td class="text-center">
                                                                    <?= $counter++ ?>
                                                                </td>

                                                                <td>
                                                                    <?= Html::encode($row['client_name'] ?? 'Nomaʼlum mijoz') ?>
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

                                                        <tr class="worker-total-row">
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
                                            </div>
                                        </div>

                                    <?php endforeach; ?>

                                    <div class="worker-date-total-box">
                                        <div class="worker-date-total-title">
                                            <?= date('d.m.Y', strtotime($date)) ?> sana bo‘yicha jami summa va to‘lovlar
                                        </div>

                                        <div class="worker-date-total-items">

                                            <div class="worker-total-card blue">
                                                <span class="label-text">To‘lanadigan</span>
                                                <span class="value-text"><?= number_format($date_sum_all_product, 2) ?> $</span>
                                            </div>

                                            <div class="worker-total-card green">
                                                <span class="label-text">To‘l jami</span>
                                                <span class="value-text"><?= number_format($date_sum_all_paid_dollar, 2) ?> $</span>
                                            </div>

                                            <div class="worker-total-card red">
                                                <span class="label-text">Dollar</span>
                                                <span class="value-text"><?= number_format($date_sum_paid_dollar, 2) ?> $</span>
                                            </div>

                                            <div class="worker-total-card orange">
                                                <span class="label-text">Karta</span>
                                                <span class="value-text"><?= number_format($date_sum_paid_cart, 2) ?></span>
                                            </div>

                                            <div class="worker-total-card dark">
                                                <span class="label-text">Naqt</span>
                                                <span class="value-text"><?= number_format($date_sum_paid_som, 2) ?></span>
                                            </div>

                                            <div class="worker-total-card purple">
                                                <span class="label-text">Qaytim</span>
                                                <span class="value-text">
                                                    <?= number_format($date_sum_paid_zdacha_sum, 2) ?>
                                                    (<?= number_format($date_sum_paid_zdacha_dollar, 2) ?> $)
                                                </span>
                                            </div>

                                            <div class="worker-total-card green">
                                                <span class="label-text">Qarz jami</span>
                                                <span class="value-text"><?= number_format($date_sum_paid_debt, 2) ?> $</span>
                                            </div>

                                            <div class="worker-total-card red">
                                                <span class="label-text">Qarz dollar</span>
                                                <span class="value-text"><?= number_format($date_sum_debt_dollar, 2) ?> $</span>
                                            </div>

                                            <div class="worker-total-card dark">
                                                <span class="label-text">Qarz naqt</span>
                                                <span class="value-text"><?= number_format($date_sum_debt_som, 2) ?></span>
                                            </div>

                                            <div class="worker-total-card orange">
                                                <span class="label-text">Qarz karta</span>
                                                <span class="value-text"><?= number_format($date_sum_debt_cart, 2) ?></span>
                                            </div>

                                            <div class="worker-total-card purple">
                                                <span class="label-text">Qarz qaytim</span>
                                                <span class="value-text">
                                                    <?= number_format($date_sum_debt_zdacha_sum, 2) ?>
                                                    (<?= number_format($date_sum_debt_zdacha_dollar, 2) ?> $)
                                                </span>
                                            </div>

                                        </div>
                                    </div>

                                </div>

                            <?php endforeach; ?>

                            <div class="text-center order-debt-pagination worker-pagination">
                                <?= LinkPager::widget([
                                    'pagination' => $provider->getPagination(),
                                ]) ?>
                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs("
    function setUrlParam(url, key, value) {
        var a = document.createElement('a');
        a.href = url;

        var params = new URLSearchParams(a.search);
        params.set(key, value);

        a.search = params.toString();
        return a.href;
    }

    function updatePaginationLinks() {
        $('.worker-pagination a').each(function() {
            var href = $(this).attr('href');

            if (href && href !== '#') {
                $(this).attr('href', setUrlParam(href, 'active_tab', 'worker'));
            }
        });

        $('.main-pagination a').each(function() {
            var href = $(this).attr('href');

            if (href && href !== '#') {
                $(this).attr('href', setUrlParam(href, 'active_tab', 'main'));
            }
        });
    }

    updatePaginationLinks();

    $(document).on('shown.bs.tab', 'a[data-toggle=\"tab\"]', function(e) {
        var tabName = $(e.target).data('tab-name');

        if (tabName) {
            var newUrl = setUrlParam(window.location.href, 'active_tab', tabName);
            window.history.replaceState({}, '', newUrl);
        }

        $('input[name=\"active_tab\"]').val(tabName);

        updatePaginationLinks();
    });

    $(document).on('click', '.pdf-date-check', function(e) {
        var tooLong = $(this).data('too-long');
        var days = $(this).data('days');

        if (tooLong == 1) {
            e.preventDefault();

            alert('PDF chiqarish uchun sana oralig‘i 3 kundan oshmasligi kerak. Siz tanlagan oraliq: ' + days + ' kun.');

            return false;
        }
    });
");
?>