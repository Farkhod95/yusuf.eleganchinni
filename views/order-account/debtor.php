<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use app\models\OrderAccountHistory;
use app\models\Client;

/* @var $this yii\web\View */
/* @var $orderAccount app\models\OrderAccount[] */
/* @var $clients app\models\Client[] */
/* @var $selectedClientType string|null */
/* @var $selectedClientId string|null */

$this->title = 'Qarzdorlar ro\'yxati';

$allSumm = 0;
$allCount = 0;

$clientTypes = [
    '1' => 'Oddiy mijoz',
    '2' => 'Bozordagi mijoz',
    '3' => 'Filial',
];

$clientData = ArrayHelper::map($clients, 'id', 'fio');

$clientMeta = [];
foreach ($clients as $client) {
    $clientMeta[] = [
        'id'   => (string)$client->id,
        'fio'  => $client->fio,
        'type' => (string)$client->type,
    ];
}
?>

<style>
.summary-cards-wrap {
    margin: 0 0 10px 0;
}
.summary-card {
    background: #f7f9fb;
    border: 1px solid #d9e0e7;
    border-radius: 6px;
    padding: 14px 16px;
    min-height: 20px;
}
.summary-card-inline {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.summary-card .summary-label {
    font-size: 12px;
    color: #6c757d;
    font-weight: 600;
    margin: 0;
}
.summary-card .summary-value {
    font-size: 20px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0;
    white-space: nowrap;
}
.summary-card .summary-value.count-value {
    color: #348fe2;
}
.summary-card .summary-value.debt-value {
    color: #ff5b57;
}
@media (max-width: 767px) {
    .summary-card {
        margin-bottom: 5px;
    }

    .summary-card-inline {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>

<div class="row">
    <div class="col-xl-12">
        <div class="panel panel-inverse" data-sortable-id="table-basic-4">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:void(0);" id="exportPdfBtn" class="btn btn-xs btn-warning" title="PDF ga export">
                        <i class="fa fa-file-pdf-o"></i> PDF Export qilish
                    </a>
                </div>
                <h4 class="panel-title">Qarzdorlar ro'yxati</h4>
            </div>

            <div class="panel-body">
                <div class="table-responsive">

                    <div class="row" style="margin-bottom:15px;">
                        <div class="col-md-4">
                            <label style="font-size:14px;" for="client_type"><b>Mijoz turi:</b></label>
                            <?= Select2::widget([
                                'name' => 'client_type',
                                'id' => 'client_type',
                                'data' => $clientTypes,
                                'value' => $selectedClientType,
                                'options' => [
                                    'placeholder' => 'Mijoz turini tanlang',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'width' => '100%',
                                ],
                            ]) ?>
                        </div>

                        <div class="col-md-5">
                            <label style="font-size:14px;" for="debt"><b>Mijozni tanlang:</b></label>
                            <?= Select2::widget([
                                'name' => 'debt',
                                'id' => 'debt',
                                'data' => $clientData,
                                'value' => $selectedClientId,
                                'options' => [
                                    'placeholder' => 'Mijozni tanlang',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'width' => '100%',
                                ],
                            ]) ?>
                        </div>

                        <div class="col-md-3" style="padding-top:24px;">
                            <button type="button" id="clearFilters" class="btn btn-default" disabled>
                                <i class="fa fa-refresh"></i> Filterni tozalash
                            </button>
                        </div>
                    </div>

                    <div class="row summary-cards-wrap">
                        <div class="col-md-6 col-sm-6">
                            <div class="summary-card">
                                <div class="summary-card-inline">
                                    <div class="summary-label">Jami qarzdorlar soni</div>
                                    <div class="summary-value count-value" id="visibleDebtorsCount">0</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-6">
                            <div class="summary-card">
                                <div class="summary-card-inline">
                                    <div class="summary-label">Jami qarz summasi ($)</div>
                                    <div class="summary-value debt-value" id="visibleDebtorsDebt">0.00</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>FIO</th>
                                <th>Mijoz turi</th>
                                <th>Qarzi ($)</th>
                                <th>Oxirgi buyurtma sanasi</th>
                            </tr>
                        </thead>
                        <tbody id="showRes1">
                            <?php $displayIndex = 1; ?>
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

                                $clientTypeText = '';
                                if ($model->client) {
                                    $clientTypeText = $model->client->getTypeView($model->client->type);
                                }

                                $allSumm += $debtValue;
                                $allCount++;
                                ?>
                                <tr class="debt-row"
                                    data-client-id="<?= $model->client ? (int)$model->client->id : '' ?>"
                                    data-client-type="<?= $model->client ? (int)$model->client->type : '' ?>"
                                    data-debt="<?= $debtValue ?>">
                                    <td class="row-number"><?= $displayIndex ?></td>
                                    <td>
                                        <b>
                                            <a href="<?= Url::toRoute(['order-account/products', 'id' => $model->id]) ?>">
                                                <?= Html::encode($model->client ? $model->client->fio : '') ?>
                                            </a>
                                        </b>
                                    </td>
                                    <td>
                                        <b style="color:#348fe2;">
                                            <?= Html::encode($clientTypeText) ?>
                                        </b>
                                    </td>
                                    <td>
                                        <b style="color:red">
                                            <?= Yii::$app->formatter->asDecimal($debtValue, 2) ?>
                                        </b>
                                    </td>
                                    <td>
                                        <b style="color:#f59c1a">
                                            <?= $orderAccountHistory ? Yii::$app->formatter->asDate($orderAccountHistory->date, 'php:d.m.Y') : '' ?>
                                        </b>
                                    </td>
                                </tr>
                                <?php $displayIndex++; ?>
                            <?php endforeach; ?>

                            <tr id="totalRow">
                                <td colspan="3" style="background-color:#2d353c;">
                                    <b style="color:white">Jami:</b>
                                </td>
                                <td style="background-color:#2d353c;">
                                    <b style="color:white" id="totalDebtValue">
                                        <?= Yii::$app->formatter->asDecimal($allSumm, 2) ?>
                                    </b>
                                </td>
                                <td style="background-color:#2d353c;"></td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
$clientMetaJson = json_encode($clientMeta, JSON_UNESCAPED_UNICODE);
$pdfUrl = Url::to(['order-account/debtors-pdf']);

$this->registerJs(<<<JS
const allClientsMeta = $clientMetaJson;
const debtSelect = $('#debt');
const typeSelect = $('#client_type');
const clearBtn = $('#clearFilters');
const rows = $('#showRes1').find('tr.debt-row');
const exportPdfBtn = $('#exportPdfBtn');
const pdfBaseUrl = '$pdfUrl';
const visibleDebtorsCountEl = $('#visibleDebtorsCount');
const visibleDebtorsDebtEl = $('#visibleDebtorsDebt');

let isInternalUpdate = false;

function formatMoney(value) {
    value = parseFloat(value || 0);
    return value.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatCount(value) {
    value = parseInt(value || 0);
    return value.toLocaleString('en-US');
}

function hasActiveFilters() {
    const selectedType = String(typeSelect.val() || '');
    const selectedClientId = String(debtSelect.val() || '');
    return !!(selectedType || selectedClientId);
}

function updateClearButtonState() {
    clearBtn.prop('disabled', !hasActiveFilters());
}

function rebuildClientSelect(typeValue, selectedClientId = '') {
    let html = '<option value=""></option>';

    for (let i = 0; i < allClientsMeta.length; i++) {
        const client = allClientsMeta[i];
        if (!typeValue || client.type === String(typeValue)) {
            const selected = String(selectedClientId) === String(client.id) ? ' selected' : '';
            html += '<option value="' + client.id + '"' + selected + '>' + client.fio + '</option>';
        }
    }

    debtSelect.html(html);
}

function updateSummary(count, total) {
    visibleDebtorsCountEl.text(formatCount(count));
    visibleDebtorsDebtEl.text(formatMoney(total));
}

function applyFilters() {
    const selectedType = String(typeSelect.val() || '');
    const selectedClientId = String(debtSelect.val() || '');

    let visibleIndex = 1;
    let total = 0;
    let visibleCount = 0;

    rows.each(function() {
        const tr = this;
        const rowType = String(tr.getAttribute('data-client-type') || '');
        const rowClientId = String(tr.getAttribute('data-client-id') || '');
        const rowDebt = parseFloat(tr.getAttribute('data-debt') || 0);

        if (rowDebt < 1) {
            tr.style.display = 'none';
            return;
        }

        const typeMatch = !selectedType || rowType === selectedType;
        const clientMatch = !selectedClientId || rowClientId === selectedClientId;

        if (typeMatch && clientMatch) {
            tr.style.display = '';
            tr.querySelector('.row-number').textContent = visibleIndex;
            visibleIndex++;
            visibleCount++;
            total += rowDebt;
        } else {
            tr.style.display = 'none';
        }
    });

    $('#totalDebtValue').text(formatMoney(total));
    updateSummary(visibleCount, total);
    updateClearButtonState();
}

function handleTypeChange() {
    if (isInternalUpdate) return;

    const selectedType = String(typeSelect.val() || '');
    const currentClientId = String(debtSelect.val() || '');

    let clientStillExists = false;

    for (let i = 0; i < allClientsMeta.length; i++) {
        const client = allClientsMeta[i];
        if (
            String(client.id) === currentClientId &&
            (!selectedType || String(client.type) === selectedType)
        ) {
            clientStillExists = true;
            break;
        }
    }

    rebuildClientSelect(selectedType, clientStillExists ? currentClientId : '');
    debtSelect.trigger('change.select2');
    applyFilters();
}

function handleClientChange() {
    if (isInternalUpdate) return;
    applyFilters();
}

function clearFiltersFast() {
    if (!hasActiveFilters()) {
        updateClearButtonState();
        return;
    }

    isInternalUpdate = true;
    typeSelect.val('').trigger('change.select2');
    rebuildClientSelect('', '');
    debtSelect.val('').trigger('change.select2');
    isInternalUpdate = false;

    applyFilters();
}

function buildPdfUrl() {
    const selectedType = String(typeSelect.val() || '');
    const selectedClientId = String(debtSelect.val() || '');

    const params = new URLSearchParams();
    if (selectedType) params.append('client_type', selectedType);
    if (selectedClientId) params.append('debt', selectedClientId);

    return pdfBaseUrl + (params.toString() ? '?' + params.toString() : '');
}

typeSelect.on('change', handleTypeChange);
debtSelect.on('change', handleClientChange);
clearBtn.on('click', clearFiltersFast);

exportPdfBtn.on('click', function () {
    window.open(buildPdfUrl(), '_blank');
});

applyFilters();
updateClearButtonState();
JS
);
?>