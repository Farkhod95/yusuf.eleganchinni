<?php
use yii\helpers\Html;
use app\models\Warehouse;
use app\models\Prices;
use kartik\select2\Select2;
use app\models\Brands;
use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Warehouse[] $warehouse */

$brands = Brands::find()
    ->orderBy(['sorting' => SORT_ASC])
    ->andWhere(['<>', 'sup_status', 0])
    ->all();

$this->title = "Mahsulotlar ro'yxati";
CrudAsset::register($this);

$catAjaxUrl = Url::to(['product-category/by-brand']);

$brandIds = [];
foreach ($warehouse as $item) {
    if (!empty($item->brand_id)) {
        $brandIds[] = (int)$item->brand_id;
    } elseif (!empty($item->brand) && !empty($item->brand->id)) {
        $brandIds[] = (int)$item->brand->id;
    }
}
$brandIds = array_values(array_unique($brandIds));

$rowsByBrand = [];
$allMarkCount = 0;
$allModelIds = [];
$allWarehouseCount = 0;

if (!empty($brandIds)) {
    $allRows = Warehouse::find()
        ->alias('w')
        ->with(['brand', 'productCategory'])
        ->leftJoin('product_category pc', 'w.product_category_id = pc.id')
        ->andWhere(['w.brand_id' => $brandIds])
        ->orderBy([
            'w.brand_id' => SORT_ASC,
            'pc.sorting' => SORT_ASC,
            'w.id' => SORT_ASC,
        ])
        ->all();

    $warehouseIds = ArrayHelper::getColumn($allRows, 'id');

    $prices = [];
    if (!empty($warehouseIds)) {
        $prices = Prices::find()
            ->where(['warehouse_id' => $warehouseIds])
            ->indexBy('warehouse_id')
            ->all();
    }

    foreach ($allRows as $row) {
        $brandId = (int)$row->brand_id;

        if (!isset($rowsByBrand[$brandId])) {
            $rowsByBrand[$brandId] = [];
        }

        $unitPrice = isset($prices[$row->id]) ? (float)$prices[$row->id]->price : 0.0;

        $rowsByBrand[$brandId][] = [
            'model' => $row,
            'unitPrice' => $unitPrice,
            'count' => (int)$row->count,
            'rowTotal' => ((float)$unitPrice * (int)$row->count),
        ];

        $allMarkCount += (int)$row->count;

        if ($brandId > 0) {
            $allModelIds[$brandId] = true;
        }

        $allWarehouseCount++;
    }
}

$brandRenderOrder = [];
foreach ($brands as $brand) {
    if (isset($rowsByBrand[$brand->id])) {
        $brandRenderOrder[] = $brand;
    }
}

$allModelCount = count($allModelIds);
?>

<div class="row">
    <div class="col-xl-12">
        <div class="panel panel-inverse warehouse-sticky-panel" data-sortable-id="table-basic-4">

            <div class="panel-heading warehouse-panel-heading">
                <div class="panel-heading-btn">
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
                <h4 class="panel-title">Ombordagi barcha mahsulotlar ro'yxati</h4>
            </div>

            <div class="panel-body">
                <div class="table-responsive warehouse-table-wrap">

                    <div class="warehouse-sticky-filters" id="bcFilterRow">
                        <div class="warehouse-filter-inline">

                            <div class="warehouse-filter-item warehouse-filter-brand">
                                <label for="brandFilter">Modelni tanlang:</label>
                                <?= Select2::widget([
                                    'name' => 'brand_id',
                                    'id'   => 'brandFilter',
                                    'data' => ArrayHelper::map($brands, 'id', 'name'),
                                    'options' => [
                                        'placeholder' => 'Modelni tanlang',
                                        'data-url'    => $catAjaxUrl,
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'width' => '100%',
                                    ],
                                ]); ?>
                            </div>

                            <div class="warehouse-filter-item warehouse-filter-category">
                                <label for="categoryFilter">Kategoriya:</label>
                                <?= Select2::widget([
                                    'name' => 'product_category_id',
                                    'id'   => 'categoryFilter',
                                    'data' => [],
                                    'options' => [
                                        'placeholder' => 'Kategoriyani tanlang',
                                        'disabled'    => true,
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'width' => '100%',
                                    ],
                                ]); ?>
                            </div>

                            <div class="warehouse-filter-item warehouse-filter-btn">
                                <button type="button" id="bcClearFilters" class="btn btn-default" title="Filtrlarni tozalash">
                                    <i class="fa fa-eraser"></i> Tozalash
                                </button>
                            </div>

                            <div class="warehouse-filter-item warehouse-filter-btn">
                                <button type="button" id="exportExcelBtn" class="btn btn-success" title="Excelga yuklash">
                                    <i class="fa fa-file-excel-o"></i> Export
                                </button>
                            </div>

                        </div>

                        <div class="warehouse-filter-summary">
                             <div class="warehouse-summary-card">
                                <span class="warehouse-summary-title">Barcha mahsulotlar soni:</span>
                                <span class="warehouse-summary-value">
                                    <span id="statWarehouseCount"><?= (int)$allWarehouseCount ?></span> ta
                                </span>
                            </div>
                            <div class="warehouse-summary-card">
                                <span class="warehouse-summary-title">Modellar soni:</span>
                                <span class="warehouse-summary-value">
                                    <span id="statModelCount"><?= (int)$allModelCount ?></span> ta
                                </span>
                            </div>

                           
                        </div>
                    </div>

                    <div class="warehouse-table-space"></div>

                    <?php Pjax::begin([
                        'id' => 'crud-datatable-pjax',
                        'timeout' => 0,
                        'enablePushState' => false,
                    ]); ?>

                    <table id="warehouseExcelTable" class="table table-bordered table-striped warehouse-main-table">
                        <thead>
                        <tr>
                            <th style="background-color:#90e6e6;"><b>#</b></th>
                            <th style="background-color:#90e6e6;" nowrap><b>Model</b></th>
                            <th style="background-color:#90e6e6;" nowrap><b>Nomi</b></th>
                            <th style="background-color:#90e6e6;" nowrap><b>O'lchami</b></th>
                            <th style="background-color:#90e6e6;" nowrap><b>Tip</b></th>
                            <th style="background-color:#90e6e6;" nowrap><b>Soni</b></th>

                            <?php if (Yii::$app->user->identity->permission == 1): ?>
                                <th style="background-color:#90e6e6;" nowrap><b>Narxi ($)</b></th>
                                <th style="background-color:#90e6e6;" nowrap><b>Jami narxi ($)</b></th>
                            <?php endif; ?>
                        </tr>
                        </thead>

                        <tbody id="showRes">
                        <?php
                        $allPriceSum = 0;
                        $allTotalSum = 0;

                        foreach ($brandRenderOrder as $brand):
                            $brandId = (int)$brand->id;
                            $brandRows = $rowsByBrand[$brandId] ?? [];

                            if (empty($brandRows)) {
                                continue;
                            }

                            $i = 1;
                            $allCount = 0;
                            $priceSum = 0;
                            $totalSum = 0;
                            ?>

                            <tr class="handle-header" data-brand-id="<?= $brandId ?>">
                                <?php if (Yii::$app->user->identity->permission == 1): ?>
                                    <td colspan="7" style="background-color:#a0d9ea;">
                                        <b style="color:red"><?= Html::encode($brand->name) ?></b>
                                    </td>
                                    <td style="background-color:#a0d9ea;"></td>
                                <?php else: ?>
                                    <td colspan="5" style="background-color:#a0d9ea;">
                                        <b style="color:red"><?= Html::encode($brand->name) ?></b>
                                    </td>
                                    <td style="background-color:#a0d9ea;"></td>
                                <?php endif; ?>
                            </tr>

                            <?php foreach ($brandRows as $rowData):
                                /** @var Warehouse $model1 */
                                $model1 = $rowData['model'];
                                $unitPrice = (float)$rowData['unitPrice'];
                                $count = (int)$rowData['count'];
                                $rowTotal = (float)$rowData['rowTotal'];
                                ?>

                                <tr class="handle"
                                    data-brand-id="<?= (int)$model1->brand_id ?>"
                                    data-category-id="<?= (int)$model1->product_category_id ?>"
                                    data-count="<?= (int)$count ?>"
                                    data-row-total="<?= Html::encode(number_format($rowTotal, 2, '.', '')) ?>">

                                    <td class="index-cell" style="border-top:1px solid #bebabaff;border-bottom:1px solid #bebabaff;background-color:#efdfdf;">
                                        <b><?= $i ?></b>
                                    </td>

                                    <td style="border-top:1px solid #bebabaff;border-bottom:1px solid #bebabaff;background-color:#efdfdf;">
                                        <b><?= Html::encode($model1->brand ? $model1->brand->name : '') ?></b>
                                    </td>

                                    <td class="category-name-cell" style="border-top:1px solid #bebabaff;border-bottom:1px solid #bebabaff;background-color:#efdfdf;">
                                        <b><?= $model1->product_category_id && $model1->productCategory ? Html::encode($model1->productCategory->name) : '' ?></b>
                                    </td>

                                    <td style="border-top:1px solid #bebabaff;border-bottom:1px solid #bebabaff;background-color:#efdfdf;">
                                        <b><?= Html::encode($model1->size) ?></b>
                                    </td>

                                    <td style="border-top:1px solid #bebabaff;border-bottom:1px solid #bebabaff;background-color:#efdfdf;">
                                        <b><?= Html::encode($model1->getTypeView($model1->type)) ?></b>
                                    </td>

                                    <td class="count-cell" style="border-top:1px solid #bebabaff;border-bottom:1px solid #bebabaff;background-color:#efdfdf;">
                                        <b><?= $count ?></b>
                                    </td>

                                    <?php if (Yii::$app->user->identity->permission == 1): ?>
                                        <td style="border-top:1px solid #bebabaff;border-bottom:1px solid #bebabaff;background-color:#efdfdf;font-size:14px">
                                            <?= Html::a(
                                                '<b>' . number_format($unitPrice, 2, '.', '') . '</b> <span class="glyphicon glyphicon-usd"></span>',
                                                ['/prices/update', 'warehouse_id' => $model1->id],
                                                [
                                                    'role' => 'modal-remote',
                                                    'data-toggle' => 'tooltip',
                                                    'title' => 'Narxni tahrirlash',
                                                    'class' => 'price-link',
                                                    'data-pjax' => 0,
                                                    'style' => 'color:' . ($unitPrice == 0 ? 'red' : 'green') . '; font-weight:bold;'
                                                ]
                                            ) ?>
                                        </td>

                                        <td class="row-total-cell" style="border-top:1px solid #bebabaff;border-bottom:1px solid #bebabaff;background-color:#efdfdf;font-size:14px;">
                                            <b style="color:#2d6a4f;"><?= number_format($rowTotal, 2, '.', '') ?> $</b>
                                        </td>
                                    <?php endif; ?>
                                </tr>

                                <?php
                                $i++;
                                $allCount += $count;
                                $priceSum += $unitPrice;
                                $totalSum += $rowTotal;
                            endforeach;
                            ?>

                            <tr class="brand-amount" data-brand-id="<?= $brandId ?>">
                                <?php if (Yii::$app->user->identity->permission == 1): ?>
                                    <td colspan="4"></td>
                                    <td><b>Jami:</b></td>
                                    <td class="brand-count-total"><b><?= (int)$allCount ?></b></td>
                                    <td><b></b></td>
                                    <td class="brand-money-total"><b><?= number_format($totalSum, 2, '.', '') ?> $</b></td>
                                <?php else: ?>
                                    <td colspan="4"></td>
                                    <td><b>Jami:</b></td>
                                    <td class="brand-count-total"><b><?= (int)$allCount ?></b></td>
                                <?php endif; ?>
                            </tr>

                            <?php
                            $allPriceSum += $priceSum;
                            $allTotalSum += $totalSum;
                        endforeach;
                        ?>

                        <tr class="all-total-row">
                            <?php if (Yii::$app->user->identity->permission == 1): ?>
                                <td colspan="5" style="background-color:#2d353c;">
                                    <b style="color:white">Jami:</b>
                                </td>
                                <td class="grand-count-total" style="background-color:#2d353c;">
                                    <b style="color:white"><?= (int)$allMarkCount ?></b>
                                </td>
                                <td style="background-color:#2d353c;">
                                    <b style="color:white"></b>
                                </td>
                                <td class="grand-money-total" style="background-color:#2d353c;">
                                    <b style="color:white"><?= number_format($allTotalSum, 2, '.', '') ?> $</b>
                                </td>
                            <?php else: ?>
                                <td colspan="5" style="background-color:#2d353c;">
                                    <b style="color:white">Jami:</b>
                                </td>
                                <td class="grand-count-total" style="background-color:#2d353c;">
                                    <b style="color:white"><?= (int)$allMarkCount ?></b>
                                </td>
                            <?php endif; ?>
                        </tr>
                        </tbody>
                    </table>

                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
Modal::begin(["id" => "ajaxCrudModal", "footer" => ""]);
Modal::end();

$this->registerCss(<<<CSS
:root{
    --app-header-height: 50px;
    --warehouse-panel-heading-height: 40px;
    --warehouse-filter-row-height: 120px;
}

.warehouse-sticky-panel{
    position: relative;
}

.warehouse-table-wrap{
    overflow: visible !important;
    position: relative;
}

.warehouse-panel-heading{
    position: sticky;
    top: var(--app-header-height);
    z-index: 1035;
    background: #2d8c8c !important;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}

.warehouse-sticky-filters{
    position: sticky;
    top: calc(var(--app-header-height) + var(--warehouse-panel-heading-height));
    z-index: 1030;
    background: #f7f7f7;
    padding: 12px 0;
    border-bottom: 1px solid #dcdcdc;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}

.warehouse-filter-inline{
    display: flex;
    align-items: flex-end;
    gap: 18px;
    flex-wrap: nowrap;
}

.warehouse-filter-item{
    min-width: 0;
}

.warehouse-filter-brand{
    flex: 1 1 40%;
}

.warehouse-filter-category{
    flex: 1 1 40%;
}

.warehouse-filter-btn{
    flex: 0 0 auto;
    padding-bottom: 1px;
}

.warehouse-filter-item label{
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #2d8c8c;
}

.warehouse-filter-btn .btn{
    height: 34px;
    min-width: 100px;
}

.warehouse-filter-summary{
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 8px;
}

.warehouse-summary-card{
    background: #ffffff;
    border: 1px solid #d8eeee;
    border-left: 4px solid #cc8245;
    border-radius: 5px;
    padding: 6px 12px;
    min-width: 220px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    display: flex;
    align-items: center;
    gap: 6px;
    height: 34px;
}

.warehouse-summary-title{
    font-size: 13px;
    font-weight: 600;
    color: #666;
    margin: 0;
    line-height: 1;
}

.warehouse-summary-value{
    font-size: 15px;
    font-weight: bold;
    color: #2d353c;
    line-height: 1;
}

.warehouse-table-space{
    height: 10px;
}

.warehouse-main-table{
    margin-bottom: 0;
}

.warehouse-main-table thead th{
    position: sticky;
    top: calc(var(--app-header-height) + var(--warehouse-panel-heading-height) + var(--warehouse-filter-row-height));
    z-index: 1025;
    background-color: #90e6e6 !important;
    vertical-align: middle !important;
    box-shadow: inset 0 -1px 0 #7ccccc;
}

.warehouse-main-table td,
.warehouse-main-table th{
    white-space: nowrap;
}

.select2-container{
    width: 100% !important;
}

.handle-header td{
    position: relative;
    z-index: 1;
}

.brand-amount td{
    background: #fff;
}

#exportExcelBtn{
    background: #198754;
    color: #fff;
    border-color: #198754;
    font-weight: bold;
}

#exportExcelBtn:hover{
    background: #157347;
    border-color: #157347;
}

#exportExcelBtn:disabled,
#exportExcelBtn.disabled{
    opacity: .65;
    cursor: not-allowed;
    pointer-events: none;
}

@media (max-width: 991px){
    .warehouse-filter-inline{
        flex-wrap: wrap;
        align-items: stretch;
    }

    .warehouse-filter-brand,
    .warehouse-filter-category{
        flex: 1 1 100%;
    }

    .warehouse-filter-btn{
        flex: 0 0 100%;
    }

    .warehouse-filter-btn .btn{
        width: 100%;
    }

    .warehouse-filter-summary{
        display: block;
    }

    .warehouse-summary-card{
        width: 100%;
        margin-bottom: 8px;
    }
}
CSS
);

$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);

$this->registerJs(<<<'JS'
var $brand = $('#brandFilter');
var $cat   = $('#categoryFilter');

var warehouseRowsCache = [];
var warehouseHeadersCache = {};
var warehouseAmountsCache = {};
var categoryAjaxRequest = null;
var exportExcelLock = false;

function getHeaderHeight() {
    var selectors = [
        '.header',
        '#header',
        '.main-header',
        '.app-header',
        '.navbar-fixed-top',
        '.top-navbar',
        '.page-header-fixed',
        '.navbar',
        '.header.navbar-default'
    ];

    for (var i = 0; i < selectors.length; i++) {
        var el = document.querySelector(selectors[i]);
        if (el) {
            var style = window.getComputedStyle(el);
            var isFixed = style.position === 'fixed' || style.position === 'sticky';
            if (isFixed || el.classList.contains('header')) {
                return Math.ceil(el.getBoundingClientRect().height);
            }
        }
    }

    return 50;
}

function updateWarehouseStickyOffsets() {
    var appHeaderHeight = getHeaderHeight();
    var panelHeading = document.querySelector('.warehouse-panel-heading');
    var filterRow = document.querySelector('.warehouse-sticky-filters');

    var panelHeadingHeight = panelHeading ? Math.ceil(panelHeading.getBoundingClientRect().height) : 40;
    var filterRowHeight = filterRow ? Math.ceil(filterRow.getBoundingClientRect().height) : 120;

    document.documentElement.style.setProperty('--app-header-height', appHeaderHeight + 'px');
    document.documentElement.style.setProperty('--warehouse-panel-heading-height', panelHeadingHeight + 'px');
    document.documentElement.style.setProperty('--warehouse-filter-row-height', filterRowHeight + 'px');
}

function formatMoney(num){
    num = parseFloat(num || 0);
    return num.toFixed(2);
}

function buildWarehouseFilterCache(){
    warehouseRowsCache = [];
    warehouseHeadersCache = {};
    warehouseAmountsCache = {};

    $('#showRes .handle-header').each(function(){
        var brandId = String(this.getAttribute('data-brand-id') || '');
        warehouseHeadersCache[brandId] = this;
    });

    $('#showRes .brand-amount').each(function(){
        var brandId = String(this.getAttribute('data-brand-id') || '');
        warehouseAmountsCache[brandId] = this;
    });

    $('#showRes .handle').each(function(){
        var row = this;
        var $row = $(row);
        var brandId = String(row.getAttribute('data-brand-id') || '');
        var categoryId = String(row.getAttribute('data-category-id') || '');
        var categoryText = ($row.find('.category-name-cell').text() || '').toLowerCase().trim();

        warehouseRowsCache.push({
            el: row,
            $el: $row,
            brandId: brandId,
            categoryId: categoryId,
            categoryText: categoryText,
            count: parseInt(row.getAttribute('data-count') || '0', 10) || 0,
            rowTotal: parseFloat(row.getAttribute('data-row-total') || '0') || 0
        });
    });
}

function setRowVisible(el, visible){
    var newDisplay = visible ? '' : 'none';
    if (el.style.display !== newDisplay) {
        el.style.display = newDisplay;
    }
}

function bcApplyFilter(){
    var bId = String($brand.val() || '');
    var cId = String($cat.val() || '');
    var cText = ($cat.find('option:selected').text() || '').toLowerCase().trim();

    var visibleByBrand = {};
    var countByBrand = {};
    var moneyByBrand = {};
    var indexByBrand = {};
    var visibleWarehouseCount = 0;

    var grandCount = 0;
    var grandMoney = 0;

    for (var i = 0; i < warehouseRowsCache.length; i++) {
        var item = warehouseRowsCache[i];

        var matchBrand = (!bId || item.brandId === bId);
        var matchCat = (!cId || item.categoryId === cId || (cText && item.categoryText === cText));

        var show = matchBrand && matchCat;
        setRowVisible(item.el, show);

        if (show) {
            visibleByBrand[item.brandId] = true;
            visibleWarehouseCount++;

            countByBrand[item.brandId] = (countByBrand[item.brandId] || 0) + item.count;
            moneyByBrand[item.brandId] = (moneyByBrand[item.brandId] || 0) + item.rowTotal;

            indexByBrand[item.brandId] = (indexByBrand[item.brandId] || 0) + 1;
            item.$el.find('.index-cell b').text(indexByBrand[item.brandId]);

            grandCount += item.count;
            grandMoney += item.rowTotal;
        }
    }

    for (var headerBrandId in warehouseHeadersCache) {
        if (warehouseHeadersCache.hasOwnProperty(headerBrandId)) {
            setRowVisible(warehouseHeadersCache[headerBrandId], !!visibleByBrand[headerBrandId]);
        }
    }

    for (var amountBrandId in warehouseAmountsCache) {
        if (warehouseAmountsCache.hasOwnProperty(amountBrandId)) {
            var amountRow = warehouseAmountsCache[amountBrandId];
            var showAmount = !!visibleByBrand[amountBrandId];

            setRowVisible(amountRow, showAmount);

            if (showAmount) {
                var $amountRow = $(amountRow);
                $amountRow.find('.brand-count-total b').text(countByBrand[amountBrandId] || 0);

                if ($amountRow.find('.brand-money-total b').length) {
                    $amountRow.find('.brand-money-total b').text(formatMoney(moneyByBrand[amountBrandId] || 0) + ' $');
                }
            }
        }
    }

    $('.grand-count-total b').text(grandCount);

    if ($('.grand-money-total b').length) {
        $('.grand-money-total b').text(formatMoney(grandMoney) + ' $');
    }

    $('#statModelCount').text(Object.keys(visibleByBrand).length);
    $('#statWarehouseCount').text(visibleWarehouseCount);

    updateWarehouseStickyOffsets();
}

function bcLoadCategories(brandId){
    var url = $brand.data('url');

    if (categoryAjaxRequest && categoryAjaxRequest.readyState !== 4) {
        categoryAjaxRequest.abort();
    }

    if(!brandId){
        $cat.prop('disabled', true).empty().val(null).trigger('change.select2');
        bcApplyFilter();
        return;
    }

    categoryAjaxRequest = $.get(url, {brand_id: brandId})
        .done(function(res){
            var items = (res && res.results) ? res.results : [];

            $cat.prop('disabled', items.length === 0).empty();

            var fragment = document.createDocumentFragment();

            for (var i = 0; i < items.length; i++) {
                fragment.appendChild(new Option(items[i].text, items[i].id, false, false));
            }

            $cat.append(fragment);
            $cat.val(null).trigger('change.select2');

            bcApplyFilter();
            setTimeout(updateWarehouseStickyOffsets, 30);
        })
        .fail(function(xhr){
            if (xhr && xhr.statusText === 'abort') {
                return;
            }

            $cat.prop('disabled', true).empty().val(null).trigger('change.select2');
            bcApplyFilter();
        });
}

function cleanCellText(text){
    return String(text || '')
        .replace(/\s+/g, ' ')
        .replace(/\$/g, '')
        .trim();
}

function getCellValue(ws, r, c){
    var addr = XLSX.utils.encode_cell({ r: r, c: c });
    return ws[addr] ? String(ws[addr].v || '').trim() : '';
}

function setCellStyle(ws, r, c, style){
    var cellAddress = XLSX.utils.encode_cell({ r: r, c: c });

    if (!ws[cellAddress]) {
        ws[cellAddress] = { t: 's', v: '' };
    }

    ws[cellAddress].s = style;
}

function exportVisibleWarehouseTableToExcel(){
    if (typeof XLSX === 'undefined') {
        alert('Excel kutubxonasi yuklanmadi. Internetni tekshiring yoki xlsx-js-style faylini lokal ulang.');
        return;
    }

    bcApplyFilter();

    var data = [];
    var merges = [];

    var htmlColumnCount = $('#warehouseExcelTable thead th').length;
    var excelColumnCount = htmlColumnCount + 1;

    var headers = [];
    $('#warehouseExcelTable thead th').each(function(){
        headers.push(cleanCellText($(this).text()));
    });

    headers.push('Rasmi');
    data.push(headers);

    $('#showRes tr').each(function(){
        if (this.style.display === 'none') {
            return;
        }

        var $tr = $(this);
        var row = [];
        var currentCol = 0;
        var excelRowIndex = data.length;

        $tr.children('td').each(function(){
            var $td = $(this);
            var colspan = parseInt($td.attr('colspan') || '1', 10);
            var text = cleanCellText($td.text());

            row.push(text);

            if (colspan > 1) {
                merges.push({
                    s: { r: excelRowIndex, c: currentCol },
                    e: { r: excelRowIndex, c: currentCol + colspan - 1 }
                });

                for (var i = 1; i < colspan; i++) {
                    row.push('');
                }
            }

            currentCol += colspan;
        });

        while (row.length < htmlColumnCount) {
            row.push('');
        }

        row.push('');

        while (row.length < excelColumnCount) {
            row.push('');
        }

        if (row.length > 0) {
            data.push(row);
        }
    });

    if (data.length <= 1) {
        alert('Excelga chiqarish uchun maʼlumot topilmadi.');
        return;
    }

    var ws = XLSX.utils.aoa_to_sheet(data);
    ws['!merges'] = merges;

    var range = XLSX.utils.decode_range(ws['!ref']);

    var headerStyle = {
        font: { bold: true, color: { rgb: "000000" }, sz: 12 },
        fill: { patternType: "solid", fgColor: { rgb: "90E6E6" } },
        alignment: { horizontal: "center", vertical: "center" },
        border: {
            top: { style: "thin", color: { rgb: "777777" } },
            bottom: { style: "thin", color: { rgb: "777777" } },
            left: { style: "thin", color: { rgb: "777777" } },
            right: { style: "thin", color: { rgb: "777777" } }
        }
    };

    var brandHeaderStyle = {
        font: { bold: true, color: { rgb: "FF0000" }, sz: 12 },
        fill: { patternType: "solid", fgColor: { rgb: "A0D9EA" } },
        alignment: { horizontal: "left", vertical: "center" },
        border: {
            top: { style: "thin", color: { rgb: "777777" } },
            bottom: { style: "thin", color: { rgb: "777777" } },
            left: { style: "thin", color: { rgb: "777777" } },
            right: { style: "thin", color: { rgb: "777777" } }
        }
    };

    var bodyStyle = {
        font: { bold: true, color: { rgb: "000000" }, sz: 11 },
        fill: { patternType: "solid", fgColor: { rgb: "EFDFDF" } },
        alignment: { horizontal: "center", vertical: "center" },
        border: {
            top: { style: "thin", color: { rgb: "BEBABA" } },
            bottom: { style: "thin", color: { rgb: "BEBABA" } },
            left: { style: "thin", color: { rgb: "BEBABA" } },
            right: { style: "thin", color: { rgb: "BEBABA" } }
        }
    };

    var amountStyle = {
        font: { bold: true, color: { rgb: "000000" }, sz: 11 },
        fill: { patternType: "solid", fgColor: { rgb: "FFFFFF" } },
        alignment: { horizontal: "center", vertical: "center" },
        border: {
            top: { style: "thin", color: { rgb: "999999" } },
            bottom: { style: "thin", color: { rgb: "999999" } },
            left: { style: "thin", color: { rgb: "999999" } },
            right: { style: "thin", color: { rgb: "999999" } }
        }
    };

    var grandTotalStyle = {
        font: { bold: true, color: { rgb: "FFFFFF" }, sz: 12 },
        fill: { patternType: "solid", fgColor: { rgb: "2D353C" } },
        alignment: { horizontal: "center", vertical: "center" },
        border: {
            top: { style: "thin", color: { rgb: "2D353C" } },
            bottom: { style: "thin", color: { rgb: "2D353C" } },
            left: { style: "thin", color: { rgb: "2D353C" } },
            right: { style: "thin", color: { rgb: "2D353C" } }
        }
    };

    for (var C = range.s.c; C <= range.e.c; C++) {
        setCellStyle(ws, 0, C, headerStyle);
    }

    for (var R = 1; R <= range.e.r; R++) {
        var firstValue = getCellValue(ws, R, 0);
        var secondValue = getCellValue(ws, R, 1);
        var thirdValue = getCellValue(ws, R, 2);

        var rowValues = [];
        for (var CC = range.s.c; CC <= range.e.c; CC++) {
            rowValues.push(getCellValue(ws, R, CC));
        }

        var fullRowText = rowValues.join(' ').trim();

        var isGrandTotal = firstValue === 'Jami:';
        var isBrandHeader = firstValue !== '' && secondValue === '' && thirdValue === '';
        var isAmountRow = fullRowText.indexOf('Jami:') !== -1 && !isGrandTotal && !isBrandHeader;

        for (var C2 = range.s.c; C2 <= range.e.c; C2++) {
            if (isGrandTotal) {
                setCellStyle(ws, R, C2, grandTotalStyle);
            } else if (isBrandHeader) {
                setCellStyle(ws, R, C2, brandHeaderStyle);
            } else if (isAmountRow) {
                setCellStyle(ws, R, C2, amountStyle);
            } else {
                setCellStyle(ws, R, C2, bodyStyle);
            }
        }
    }

    ws['!cols'] = [
        {wch: 6},
        {wch: 25},
        {wch: 25},
        {wch: 15},
        {wch: 15},
        {wch: 12},
        {wch: 15},
        {wch: 18},
        {wch: 22}
    ];

    ws['!rows'] = [];
    for (var rr = 0; rr <= range.e.r; rr++) {
        ws['!rows'][rr] = { hpt: 24 };
    }

    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Mahsulotlar');

    var today = new Date();
    var yyyy = today.getFullYear();
    var mm = String(today.getMonth() + 1).padStart(2, '0');
    var dd = String(today.getDate()).padStart(2, '0');

    XLSX.writeFile(wb, 'mahsulotlar_royxati_' + yyyy + '-' + mm + '-' + dd + '.xlsx');
}

$(document).on('click', '#exportExcelBtn', function(e){
    e.preventDefault();
    e.stopPropagation();

    var $btn = $(this);

    if (exportExcelLock || $btn.prop('disabled')) {
        return false;
    }

    exportExcelLock = true;

    var oldHtml = $btn.html();

    $btn.prop('disabled', true)
        .addClass('disabled')
        .html('<i class="fa fa-spinner fa-spin"></i> Yuklanmoqda...');

    setTimeout(function(){
        try {
            exportVisibleWarehouseTableToExcel();
        } catch (error) {
            console.log(error);
            alert('Excel export qilishda xatolik yuz berdi.');
        }

        setTimeout(function(){
            exportExcelLock = false;

            $btn.prop('disabled', false)
                .removeClass('disabled')
                .html(oldHtml);
        }, 4000);

    }, 50);

    return false;
});

$brand.on('change', function(){
    bcLoadCategories($(this).val());
});

$cat.on('change', function(){
    bcApplyFilter();
});

$('#bcClearFilters').on('click', function(e){
    e.preventDefault();
    e.stopPropagation();

    window.location.reload();

    return false;
});

$(window).on('load resize', function(){
    updateWarehouseStickyOffsets();
});

$(document).ready(function(){
    buildWarehouseFilterCache();
    bcApplyFilter();
    setTimeout(updateWarehouseStickyOffsets, 50);
});

$(document).on('pjax:end', function(e){
    if(e.target.id === 'crud-datatable-pjax'){
        buildWarehouseFilterCache();
        bcApplyFilter();
        setTimeout(updateWarehouseStickyOffsets, 50);
    }
});
JS
);
?>