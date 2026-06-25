<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset;
use johnitvn\ajaxcrud\BulkButtonWidget;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searchs\HelpsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Mijozlar';
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="panel panel-inverse user-index">
    <div class="panel-heading">
        <div class="panel-heading-btn">
            
            <?php if ((int)Yii::$app->user->identity->permission === 1): ?>
                <a href="<?= Url::to(array_merge(['/client/export-pdf'], Yii::$app->request->queryParams)) ?>" target="_blank" class="btn btn-xs btn-warning client-export-link" data-base-url="<?= Url::to(['/client/export-pdf']) ?>"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
                <a href="<?= Url::to(array_merge(['/client/export-excel'], Yii::$app->request->queryParams)) ?>" class="btn btn-xs btn-info client-export-link" data-base-url="<?= Url::to(['/client/export-excel']) ?>"><i class="fa fa-file-excel-o"></i> Export Excel</a>
            <?php endif; ?>
            <a href="/client/create" role="modal-remote" class="btn btn-xs  btn-success">Qo'shish <i class="fa fa-plus"></i> </a>
            <a href="javascript:;" title="Во весь экран" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
            <a href="javascript:;" title="Обновить" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a>
            <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
            <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-danger" data-click="panel-remove"><i class="fa fa-times"></i></a>
        </div>
        <h4 class="panel-title">Roʻyxat</h4>
    </div>
    <div class="panel-body">
        <div id="ajaxCrudDatatable">
            <?=GridView::widget([
                'id'=>'crud-datatable',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax'=>true,
                'columns' => require(__DIR__.'/_columns.php'),
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'pager' => [
                    'firstPageLabel' => 'Birinchi',
                    'lastPageLabel'  => 'Oxirgi'
                ],
                'responsiveWrap' => false,
                'panelBeforeTemplate' => false /*Html::a('Добавить <i class="fa fa-plus"></i>', ['create'],
                    ['data-pjax'=>'0','title'=> 'Добавить','class'=>'btn btn-success']) . ' ' .
                    Html::a('Назад', ['/references/helps-categories'],
                    ['data-pjax'=>'0','title'=> 'Назад','class'=>'btn btn-inverse']) */,
                'panel' => [
                    'headingOptions' => ['style' => 'display: none;'],
                    'after'=>
                        '<div class="clearfix"></div>',
                ],
            ])?>
        </div>
    </div>
</div>
<?php
// MUHIM: modal ichida yozish uchun focus trapni o‘chiradigan sozlama
Modal::begin([
    'id' => 'ajaxCrudModal',
    'footer' => '',
    'options' => ['tabindex' => false], // MUHIM
]);
Modal::end();

$this->registerJs(<<<'JS'
$(document).on('click', '.client-export-link', function(e) {
    e.preventDefault();

    var baseUrl = $(this).data('base-url');
    var params = {};
    var ids = [];
    var hasFilter = false;

    $('[name^="ClientSearch"]')
        .each(function() {
            var name = this.name;
            var value = $(this).val();

            if (Array.isArray(value)) {
                value.forEach(function(item) {
                    if (item !== null && item !== undefined && String(item).length) {
                        if (!params[name]) {
                            params[name] = [];
                        }
                        params[name].push(item);
                        hasFilter = true;
                    }
                });
                return;
            }

            if (value !== null && value !== undefined && String(value).length) {
                params[name] = value;
                hasFilter = true;
            }
        });

    if (!hasFilter) {
        $('#crud-datatable-container tbody tr[data-key], #crud-datatable tbody tr[data-key]')
            .each(function() {
                var id = $(this).data('key');
                if (id !== null && id !== undefined && String(id).length) {
                    ids.push(id);
                }
        });

        if (ids.length) {
            params.ids = ids.join(',');
        }
    }

    var query = $.param(params);
    var exportUrl = baseUrl + (query ? '?' + query : '');

    if ($(this).attr('target') === '_blank') {
        window.open(exportUrl, '_blank');
        return;
    }

    window.location.href = exportUrl;
});
JS
);
