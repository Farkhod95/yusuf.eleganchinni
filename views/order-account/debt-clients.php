<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $debtClients array */
/* @var $selectedDebtPeriod string|null */

$this->title = 'Qarzdor mijozlar hisobi';

$allSumm = 0;
$sumOrder = 0;

$pdfUrl = Url::toRoute([
    'debt-clients-pdf',
    'debt_period' => $selectedDebtPeriod,
]);
?>

<div class="row">
	<div class="col-xl-6">
        <div class="panel panel-inverse" data-sortable-id="table-basic-4">

            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="<?= $pdfUrl ?>" target="_blank" class="btn btn-xs btn-warning">
                        <i class="fa fa-file-pdf-o"></i> PDF Export
                    </a>
                </div>
                <h4 class="panel-title">Qarzdor mijozlar hisobi</h4>
            </div>

            <div class="panel-body">

                <form method="get" action="<?= Url::toRoute(['debt-clients']) ?>" style="margin-bottom:15px;">
                    <div class="row">
                        <div class="col-md-3">
                            <label><b>Filter:</b></label>
                            <select name="debt_period" class="form-control" onchange="this.form.submit()">
                                <option value="" <?= empty($selectedDebtPeriod) ? 'selected' : '' ?>>
                                    Barchasi
                                </option>

                                <option value="10" <?= $selectedDebtPeriod == '10' ? 'selected' : '' ?>>
                                    10 kundan oshganlar
                                </option>

                                <option value="30" <?= $selectedDebtPeriod == '30' ? 'selected' : '' ?>>
                                    1 oydan oshganlar
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3" style="padding-top:25px;">
                            <a href="<?= Url::toRoute(['debt-clients']) ?>" class="btn btn-default">
                                <i class="fa fa-refresh"></i> Tozalash
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th nowrap>FIO</th>
                                <th nowrap>Telefon raqami</th>
                                <th nowrap>Umumiy qarzi ($)</th>
                                <th nowrap>Oxirgi buyurtma summasi ($)</th>
                                <th nowrap>Oxirgi buyurtma sanasi</th>
                                <th nowrap>Savdo qilmagan kuni</th>
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

                                        $textColor = '#337ab7';
                                        $moneyColor = 'green';
                                        $dayText = '—';

                                        if ($lastOrder == null) {
                                            $textColor = '#d9534f';
                                            $moneyColor = '#d9534f';
                                            $dayText = 'Savdo yo‘q';
                                        } elseif ($daysAfterLastOrder > 30) {
                                            $textColor = '#d9534f';
                                            $moneyColor = '#d9534f';
                                            $dayText = $daysAfterLastOrder . ' kun';
                                        } elseif ($daysAfterLastOrder > 10) {
                                            $textColor = '#f0ad4e';
                                            $moneyColor = '#f0ad4e';
                                            $dayText = $daysAfterLastOrder . ' kun';
                                        } else {
                                            $textColor = '#337ab7';
                                            $moneyColor = 'green';
                                            $dayText = $daysAfterLastOrder . ' kun';
                                        }

                                        $allSumm = $allSumm + $totalDebt;
                                        $sumOrder = $sumOrder + $lastOrderSum;
                                    ?>

                                    <tr>
                                        <td style="width:50px; color:<?= $textColor ?>;">
                                            <?= $i ?>
                                        </td>

                                        <td>
                                            <b>
                                                <a style="color:<?= $textColor ?>;" href="<?= Url::toRoute(['order-account/products', 'id' => $orderAccount->id]) ?>">
                                                    <?= $client ? Html::encode($client->fio) : '—' ?>
                                                </a>
                                            </b>
                                        </td>

                                        <td>
                                            <b style="color:<?= $textColor ?>">
                                                <?= $client && $client->phone ? Html::encode($client->phone) : '—' ?>
                                            </b>
                                        </td>

                                        <td>
                                            <b style="color:<?= $moneyColor ?>">
                                                <?= Yii::$app->formatter->asDecimal($totalDebt, 2) ?>
                                            </b>
                                        </td>

                                        <td>
                                            <b style="color:<?= $textColor ?>">
                                                <?= $lastOrder ? Yii::$app->formatter->asDecimal($lastOrderSum, 2) : '—' ?>
                                            </b>
                                        </td>

                                        <td>
                                            <b style="color:<?= $textColor ?>">
                                                <?= $lastOrder ? date('d.m.Y', strtotime($lastOrder->date)) : '—' ?>
                                            </b>
                                        </td>

                                        <td>
                                            <b style="color:<?= $textColor ?>">
                                                <?= $dayText ?>
                                            </b>
                                        </td>
                                    </tr>

                                    <?php $i = $i + 1; ?>

                                <?php } ?>

                                <tr>
                                    <td colspan="3" style="background-color:#2d353c;">
                                        <b style="color:white">Jami:</b>
                                    </td>

                                    <td style="background-color:#2d353c;">
                                        <b style="color:white">
                                            <?= Yii::$app->formatter->asDecimal($allSumm, 2) ?>
                                        </b>
                                    </td>

                                    <td style="background-color:#2d353c;">
                                        <b style="color:white">
                                            <?= Yii::$app->formatter->asDecimal($sumOrder, 2) ?>
                                        </b>
                                    </td>

                                    <td style="background-color:#2d353c;">
                                        <b style="color:white">—</b>
                                    </td>

                                    <td style="background-color:#2d353c;">
                                        <b style="color:white">—</b>
                                    </td>
                                </tr>

                            <?php } else { ?>

                                <tr>
                                    <td colspan="7" style="text-align:center;">
                                        <b>Ma'lumot topilmadi</b>
                                    </td>
                                </tr>

                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>