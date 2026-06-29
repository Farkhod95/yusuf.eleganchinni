<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use unclead\multipleinput\MultipleInput;
use app\models\ExchangeRate;
use app\models\WarehouseHistory;
use app\models\MyTotalDebt;
use app\models\Brands;
use app\models\ProductCategory;
/* @var $this yii\web\View */
/* @var $model app\models\Warehouse */
/* @var $form yii\widgets\ActiveForm */
$this->title = 'Tovar qo\'shish';
$this->params['breadcrumbs'][] = $this->title;
$exchangeRate = ExchangeRate::findOne(1);

$warehouseHistory = WarehouseHistory::find()->where(['sklad_id' => $model->id])->all();
$data = [];
$categoryIds = [];
$sizeValues = [];
$typeIds = [];
$initialTotal = 0;
foreach ($warehouseHistory as $warehouse_history) {
    $categoryIds[] = (int)$warehouse_history->product_category_id;
    $sizeValues[(string)$warehouse_history->size] = (string)$warehouse_history->size;
    $typeIds[] = (int)$warehouse_history->type;
    $initialTotal += ((int)$warehouse_history->count * (float)$warehouse_history->price);
    $data[] = [
        'brand_id' => $warehouse_history->brand_id,
        'product_category_id' => $warehouse_history->product_category_id,
        'size' => $warehouse_history->size,
        'type' => $warehouse_history->type,
        'count' => $warehouse_history->count,
        'price' => $warehouse_history->price,
    ];
}

$categoryIds = array_values(array_unique($categoryIds));
$typeIds = array_values(array_unique($typeIds));
$brandList = Brands::listActive();
$categoryList = $categoryIds ? ArrayHelper::map(
    ProductCategory::find()
        ->where(['id' => $categoryIds])
        ->orderBy(['sorting' => SORT_ASC, 'name' => SORT_ASC])
        ->all(),
    'id',
    'name'
) : [];
$sizeList = $sizeValues;
$typeList = [];
foreach ($typeIds as $typeId) {
    $typeList[$typeId] = ProductCategory::getTypeView($typeId);
}

$urlCats = Url::to(['sklad/categories-by-brand']);
$urlSizes = Url::to(['sklad/sizes-types-by-category']);
$urlTypes = Url::to(['sklad/types-by-size']);
if ($model->sum_all_pro === null || $model->sum_all_pro === '') {
    $model->sum_all_pro = $initialTotal;
}

?>

<div class="panel panel-inverse user-index">
    <div class="panel-heading">
        <div class="panel-heading-btn">
            <a href="/sklad/index" class="btn btn-xs  btn-warning"> <i class="fa fa-reply"></i> Orqaga qaytish </a>
            <a href="javascript:;" title="Во весь экран" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
            <a href="javascript:;" title="Обновить" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a>
            <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
            <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-danger" data-click="panel-remove"><i class="fa fa-times"></i></a>
        </div>
        <h4 class="panel-title">Tovar qo'shish</h4>
    </div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin(); ?>
            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger">
                    <?= Html::encode(Yii::$app->session->getFlash('error')) ?>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'consignor_id')->label()->widget(\kartik\select2\Select2::classname(), [
                        'data' => $model->getConsignor(),
                        'options' => [
                            'placeholder' => Yii::t('app','Tanlang...'),
                            'disabled' => true,
                            'onchange'=>'
                                $.post( "/sklad/qarzs?id='.'"+$(this).val(), function( data ){
                                    $( "input#total_debts" ).val( data);
                                    alter(data);
                                });' 
                        ],
                        'pluginOptions' => [
                            'tags' => true,
                            'allowClear' => true,
                        ],
                    ])->label('Yuk jo\'natuvchi <b style="color:red">(Kiritilish majburiy)</b>'); ?> 
                </div>
                <div class="col-md-3">
                    <?php if (\Yii::$app->user->identity->permission == 1) {?>
                        <?= $form->field($model, 'my_total_debt')->textInput(['value' => $my_total_debt, 'id' => 'total_debts', 'readonly' => true,])->label("Mening qarzim ($)") ?>
                    <?php }else{?>
                        <?= $form->field($model, 'my_total_debt')->textInput(['value' => $my_total_debt, 'id' => 'total_debts', 'style' => 'display:none;'])->label("") ?>
                    <?php }?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'cr_date')->widget(DatePicker::classname(), [
                        'options' => ['placeholder' => Yii::t('app','Sanani tanlang...'), 'required'=>True],
                        'removeButton' => false,
                        'pluginOptions' => [
                            'autoclose'=>true, 
                            'format' => 'dd.mm.yyyy',
                        ]
                    ]);
                    ?>
                </div>
                <div class="col-md-1">
                </div>
                <div class="col-md-2">
                    <?php if (\Yii::$app->user->identity->permission == 1) {?>
                        <?= $form->field($model, 'exchange_rate')->textInput(['type' => 'number', 'required'=>True, 'value' => $exchangeRate->dollar])->label("Dollar kursi") ?>
                    <?php }else{?>
                        <?= $form->field($model, 'exchange_rate')->textInput(['type' => 'number', 'required'=>True, 'value' => $exchangeRate->dollar, 'style' => 'display:none;'])->label("") ?>
                    <?php }?>
                </div>
                <div class="col-md-12">
                    <?php echo $form->field($model, 'allValue')->widget(MultipleInput::className(), [
                        'id' => 'my_id',
                        'data' => $data,
                        'allowEmptyList' => true,
                        'enableGuessTitle' => true,
                        'columns' => [
                            [
                                'name' => 'brand_id',
                                'title' => 'Model',
                                'type' => \kartik\select2\Select2::className(),                                
                                'options' => [
                                    'data'  => $brandList,
                                    'options' => [
                                        'placeholder' => 'Tanlang...',
                                        'class' => 'input-priority mi-brand',
                                        'required' => true
                                    ], 
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                                                     
                                ],
                                'headerOptions' => [
                                    'style' => 'width: 370px;',
                                ] 
                            ],
                            [
                                'name' => 'product_category_id',
                                'title' => 'Nomi',
                                'type' => \kartik\select2\Select2::className(),
                                'options' => [
                                    'data'  => $categoryList,
                                    'options' => [
                                        'placeholder' => 'Tanlang...',
                                        'class' => 'input-priority mi-category',
                                        'required' => true
                                    ],   
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                    ],
                                    'headerOptions' => [
                                        'style' => 'width: 370px;',
                                        
                                    ] 
                            ],
                            [
                                'name'  => 'size',
                                'title' => 'O\'lchami <b style="color:red">(Butun sonni nuqta bilan kiriting. Misol: 9.99 )</b>',
                                'type' => \kartik\select2\Select2::className(),
                                'enableError' => true,
                                'defaultValue' => '',
                                'options' => [
                                    'data' => $sizeList,
                                    'options' => [
                                        'placeholder' => 'Tanlang...',
                                        'class' => 'input-priority mi-size',
                                        'required' => true
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ]
                            ],
                            [
                                'name' => 'type',
                                'title' => 'Tip',
                                'type' => \kartik\select2\Select2::className(),
                                'options' => [
                                    'data'  => $typeList,
                                    'options' => [
                                        'placeholder' => 'Tanlang...',
                                        'class' => 'input-priority mi-type',
                                        'required' => true
                                    ],   
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                    ],
                                    'headerOptions' => [
                                        'style' => 'width: 180px;',
                                        
                                    ] 
                            ],
                            [
                                'name'  => 'count',
                                'title' => 'Soni',
                                'enableError' => true,
                                'options' => [
                                    'type' =>'number',
                                    'min' => 1,
                                    'step' => 1,
                                    'class' => 'input-priority target mi-count',
                                    'headerOptions' => [
                                        'style' => 'font-size: 40px',
                                    ] ,
                                ]
                            ],   
                            [
                                'name'  => 'price',
                                'title' => \Yii::$app->user->identity->permission == 1 ? 'Narxi ($)' : '',
                                'enableError' => true,
                                'options' => [ 
                                    'type' =>'number',
                                    'min' => 0,
                                    'step' => '0.01',
                                    'class' => 'input-priority mi-price',
                                    'style' => \Yii::$app->user->identity->permission == 1 ? '' : 'display: none;',
                                    'options' => [
                                        'id' => 'price',
                                    ], 
                                    'id' => 'price',
                                    'headerOptions' => [
                                        'id' => 'price',
                                    ] ,
                                    'pluginOptions' => [
                                        'id' => 'price',
                                    ],  
                                    // 'onchange'=>'
                                    //     price = $(this).val();
                                    //     // alert(price);
                                    // '
                                ]
                            ],
                        ]
                    ])->label('');?>
                </div>
                <div class="row">
                    
                    <div class="col-md-10">
                    </div>
                    <div class="col-md-2">
                        <?php if (\Yii::$app->user->identity->permission == 1) {?>
                            <?= $form->field($model, 'sum_all_pro')->textInput([
                                'readonly' => true, // readonly qilib qo'yamiz
                                'style' => 'margin-top:-30px; margin-left:0px;width:170px',
                                'id' => 'sum_all_pro', // input elementga ID qo'yamiz
                            ]) ?>
                        <?php }else{?>
                            <?= $form->field($model, 'sum_all_pro')->textInput([
                                'readonly' => true, // readonly qilib qo'yamiz
                                'style' => 'margin-top:-30px; margin-left:0px;width:170px;display:none;',
                                'id' => 'sum_all_pro', // input elementga ID qo'yamiz
                            ])->label("") ?>
                        <?php }?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'comments')->textInput(['required' => true])->label("<b style='color:red;'>O'zgargan mahsulotlar bo'yicha izoh kiriting </b>") ?>
                    </div>
                      <div class="col-md-2">
                        <?= $form->field($model, 'car_number')->textInput(['required' => true])->label("<b style='color:#000;'>Avtomobil raqami </b>") ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'given_sum_dollar')->textInput(['style' => 'display:none;'])->label("<b style='color:#1748d3'></b>") ?> 
                    </div>
                    <div class="col-md-2">
                        <?php if (\Yii::$app->user->identity->permission == 1) {?>
                            <?= $form->field($model, 'sum_dollar')->textInput(['type' => 'number'])->label("<b style='color:#31701b'>Berilgan Summa ($) </b>") ?> 
                        <?php }else{?>
                            <?= $form->field($model, 'sum_dollar')->textInput(['type' => 'number', 'style' => 'display:none;'])->label("<b style='color:#31701b'> </b>") ?> 
                        <?php }?>
                    </div>
                    <div class="col-md-2">
                        <?php if (\Yii::$app->user->identity->permission == 1) {?>
                            <?= $form->field($model, 'discount_amount')->textInput(['type' => 'number'])->label("<b style='color:#f59c1a'>Jami chegirma ($) </b>") ?>
                        <?php }else{?>
                            <?= $form->field($model, 'discount_amount')->textInput(['type' => 'number', 'style' => 'display:none;'])->label("<b style='color:#f59c1a'> </b>") ?>
                        <?php }?>
                    </div>
                </div>
            <!-- <div class="row">
                <div class="col-md-3">
                    < ?= $form->field($model, 'order_account_statuses')->checkbox(['checked' => true])->label("<b style='font-size:16px;color:red'>Tasdiqlash: </b>") ?>
                </div>
            </div> -->
            </div>
            <?php if (!Yii::$app->request->isAjax){ ?>
                <div class="form-group">
                    <?= Html::submitButton($model->isNewRecord ? 'Qo\'shish' : 'O\'zgartirish', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'style' => 'width:100%']) ?>
                </div>
            <?php } ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
$this->registerJsFile('/js/cookie.js');

$this->registerJs(
    'window.__SKLAD_UPDATE_URLS__ = ' . Json::htmlEncode([
        'cats' => $urlCats,
        'sizes' => $urlSizes,
        'types' => $urlTypes,
    ]) . ';',
    \yii\web\View::POS_HEAD
);

$this->registerJs(<<<'JS'

function copyToClipboard(element) {
    element.select(); // Element qiymatini tanlaymiz
    document.execCommand("copy"); // Nusxalaymiz

    // Xabar elementini yaratamiz
    const message = document.createElement("span");
    message.innerText = "Nusxalandi";
    message.style.position = "absolute";
    message.style.bottom = "50%";
    message.style.left = "150px";
    message.style.transform = "translateY(-50%)";
    message.style.backgroundColor = "#83a16ed9";
    message.style.color = "white";
    message.style.padding = "5px 10px";
    message.style.borderRadius = "4px";
    message.style.fontSize = "12px";

    // Xabarni input yoniga qo'shamiz
    element.parentNode.appendChild(message);

    // Xabarni 2 soniyadan keyin o'chiramiz
    setTimeout(() => {
        message.remove();
    }, 2000);
}

document.getElementById("sum_all_pro").onclick = function() {
    copyToClipboard(this); // Input ustiga bosilganda nusxalaymiz
};

function fillSkladSelect($select, items, selectedValue) {
    $select.empty();
    $select.append(new Option('Tanlang...', '', false, false));

    (items || []).forEach(function(item) {
        $select.append(new Option(item.text, item.id, false, false));
    });

    if (selectedValue !== undefined && selectedValue !== null && selectedValue !== '') {
        $select.val(String(selectedValue));
    } else {
        $select.val('');
    }

    $select.trigger('change.select2');
}

function getSkladRow($element) {
    return $element.closest('tr.multiple-input-list__item');
}

function clearAfterBrand($row) {
    fillSkladSelect($row.find('select.mi-category'), [], null);
    fillSkladSelect($row.find('select.mi-size'), [], null);
    fillSkladSelect($row.find('select.mi-type'), [], null);
}

function clearAfterCategory($row) {
    fillSkladSelect($row.find('select.mi-size'), [], null);
    fillSkladSelect($row.find('select.mi-type'), [], null);
}

function clearAfterSize($row) {
    fillSkladSelect($row.find('select.mi-type'), [], null);
}

function recalcSkladTotal() {
    var total = 0;
    $('#my_id').find('tr.multiple-input-list__item').each(function() {
        var count = parseFloat(($(this).find('input.mi-count').val() || '0').toString().replace(',', '.'));
        var price = parseFloat(($(this).find('input.mi-price').val() || '0').toString().replace(',', '.'));
        if (!isNaN(count) && !isNaN(price)) {
            total += count * price;
        }
    });

    $('#sum_all_pro').val(total.toFixed(2).replace(/\.00$/, ''));
}

$(document).on('change', 'select.mi-brand', function() {
    var $row = getSkladRow($(this));
    var brandId = $(this).val();
    clearAfterBrand($row);

    if (!brandId) return;
    $.getJSON(window.__SKLAD_UPDATE_URLS__.cats, {brand_id: brandId}, function(res) {
        fillSkladSelect($row.find('select.mi-category'), res.results || [], null);
    });
});

$(document).on('change', 'select.mi-category', function() {
    var $row = getSkladRow($(this));
    var brandId = $row.find('select.mi-brand').val();
    var categoryId = $(this).val();
    clearAfterCategory($row);

    if (!brandId || !categoryId) return;
    $.getJSON(window.__SKLAD_UPDATE_URLS__.sizes, {
        brand_id: brandId,
        category_id: categoryId
    }, function(res) {
        fillSkladSelect($row.find('select.mi-size'), res.sizes || [], null);
        fillSkladSelect($row.find('select.mi-type'), res.types || [], null);
    });
});

$(document).on('change', 'select.mi-size', function() {
    var $row = getSkladRow($(this));
    var brandId = $row.find('select.mi-brand').val();
    var categoryId = $row.find('select.mi-category').val();
    var size = $(this).val();
    clearAfterSize($row);

    if (!brandId || !categoryId || !size) return;
    $.getJSON(window.__SKLAD_UPDATE_URLS__.types, {
        brand_id: brandId,
        category_id: categoryId,
        size: size
    }, function(res) {
        fillSkladSelect($row.find('select.mi-type'), res.types || [], null);
    });
});

$(document).on('input change', 'input.mi-count, input.mi-price', recalcSkladTotal);
$('#my_id').on('afterAddRow afterDeleteRow', function() {
    setTimeout(recalcSkladTotal, 0);
});
recalcSkladTotal();


$('#cars').on('change', function(e){
    e.preventDefault();
    const value = $(this).val().toUpperCase();
    console.log("value:", value);
    if (value) {
        $("#showRes .handle").filter(function() {
        const td = $(this).children('td').eq(1).text().toUpperCase();
        $(this).toggle(td === value)
        });

        $("#showRes .handle-header").filter(function() {
            const td = $(this).children('td').eq(0).text().toUpperCase();
            $(this).toggle(td === value)
        });

        $("#showRes tr").filter(function() {
            const className = $(this).attr('class').toUpperCase();
            if(className.indexOf('AMOUNT') > -1){
                $(this).toggle(className == value + '-AMOUNT')
            }
        });
    }else{
        $("#showRes tr").filter(function() {
        $(this).show()
        });
    }
});

JS
) ?>


