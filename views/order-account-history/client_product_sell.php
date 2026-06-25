<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $orders */
/** @var int $brand_id */
/** @var int $product_category_id */
/** @var string $size */
/** @var int $type */
/** @var string $brand_name */
/** @var string $product_name */
/** @var string $type_name */
/** @var string $start_date */
/** @var string $end_date */
/** @var int $totalSoldCount */
/** @var int $totalSkladCount */
/** @var int $totalDukonCount */

$this->title = 'Mahsulot sotuv tarixi';
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="/order-account-history/product-sell" class="btn btn-xs btn-info">
                        <i class="fa fa-reply"></i> Orqaga qaytish
                    </a>

                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand">
                        <i class="fa fa-expand"></i>
                    </a>

                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse">
                        <i class="fa fa-minus"></i>
                    </a>

                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-danger" data-click="panel-remove">
                        <i class="fa fa-times"></i>
                    </a>
                </div>

                <h4 class="panel-title" style="font-size:18px; color:#fff; font-weight:bold; line-height:24px;">
                    <?= Html::encode($brand_name) ?> /
                    <?= Html::encode($product_name) ?>

                    <?php if ($size !== ''): ?>
                        / Size: <?= Html::encode($size) ?>
                    <?php endif; ?>

                    <?php if ($type_name): ?>
                        / Type: <?= Html::encode($type_name) ?>
                    <?php endif; ?>

                    / <?= Html::encode($start_date) ?> dan <?= Html::encode($end_date) ?> gacha sotuv tarixi
                </h4>
            </div>

            <div class="panel-body">

                <div class="alert alert-info" style="font-size:16px; margin-bottom:20px;">
                    <b style="color:#d9534f;">Jami sotilgan soni:</b>
                    <b style="color:#337ab7; font-size:18px;">
                        <?= (int)$totalSoldCount ?>
                    </b>

                    &nbsp;&nbsp;&nbsp;

                    <b style="color:#5cb85c;">Omborda:</b>
                    <b style="color:#337ab7; font-size:18px;">
                        <?= (int)$totalSkladCount ?>
                    </b>

                    &nbsp;&nbsp;&nbsp;

                    <b style="color:#7d6009;">Do'konda:</b>
                    <b style="color:#337ab7; font-size:18px;">
                        <?= (int)$totalDukonCount ?>
                    </b>
                </div>

                <div class="row row-space-10">
                    <?php if (empty($orders)): ?>
                        <div class="col-md-12">
                            <div class="alert alert-warning">
                                Bu oraliqda ushbu filter bo‘yicha sotuv topilmadi.
                            </div>
                        </div>
                    <?php else: ?>

                        <?php foreach ($orders as $model): ?>
                            <?php
                                $soldCount = isset($model['sold_count']) ? (int)$model['sold_count'] : 0;
                                $skladCount = isset($model['sklad_count']) ? (int)$model['sklad_count'] : 0;
                                $dukonCount = isset($model['dukon_count']) ? (int)$model['dukon_count'] : 0;
                            ?>

                            <div class="col-md-3">
                                <div class="alert alert-warning show m-b-10">
                                    <i class="fa fa-calendar fa-2x"></i>

                                    <a href="<?= Url::toRoute([
                                        'order-account-history/client-product-sell-view',
                                        'brand_id' => $brand_id,
                                        'product_category_id' => $product_category_id,
                                        'cr_date' => $model['cr_date'],
                                        'start_date' => $start_date,
                                        'end_date' => $end_date,
                                        'size' => $size,
                                        'type' => $type,
                                    ]) ?>"
                                       class="alert-link"
                                       style="font-size:18px; margin-left:20px;">
                                        <?= Yii::$app->formatter->asDate($model['cr_date'], 'php:d.m.Y') ?>
                                    </a>

                                    <div style="margin-top:8px; margin-left:42px;">

                                        <div>
                                            <b style="color:#d9534f;">Sotilgan soni:</b>
                                            <b style="color:#337ab7; font-size:16px;">
                                                <?= $soldCount ?>
                                            </b>
                                        </div>

                                        <div style="margin-top:4px;">
                                            <span style="display:inline-block; margin-right:15px;">
                                                <b style="color:#5cb85c;">Omborda:</b>
                                                <b style="color:#337ab7; font-size:16px;">
                                                    <?= $skladCount ?>
                                                </b>
                                            </span>
                                            / &nbsp;
                                            <span style="display:inline-block;">
                                                <b style="color:#7d6009;">Do'konda:</b>
                                                <b style="color:#337ab7; font-size:16px;">
                                                    <?= $dukonCount ?>
                                                </b>
                                            </span>
                                        </div>

                                        <!-- <?php if ($type_name): ?>
                                            <div style="margin-top:4px;">
                                                <b style="color:#5cb85c;">Type:</b>
                                                <b><?= Html::encode($type_name) ?></b>
                                            </div>
                                        <?php endif; ?> -->

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>