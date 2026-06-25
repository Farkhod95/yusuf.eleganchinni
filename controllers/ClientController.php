<?php

namespace app\controllers;

use Yii;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use app\models\Client;
use app\models\ClientSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;
use yii\filters\AccessControl;
use app\models\Regions;
use app\models\Districts;
use app\models\ExchangeRate;
use app\models\OrderAccount;
use app\models\OrderAccountHistory;
use yii\db\Transaction;
/**
 * ClientController implements the CRUD actions for Client model.
 */
class ClientController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return \app\models\Users::isMenejerRight(Yii::$app->user->identity->id);
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'bulk-delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Client models.
     * @return mixed
     */
    public function actionIndex()
    {    
        $searchModel = new ClientSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExportPdf()
    {
        $clients = $this->getExportClients();
        $html = $this->renderClientExportTable($clients, 'Mijozlar ro\'yxati');

        $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
        $mpdf->WriteHTML($html);
        return $mpdf->Output('mijozlar-royxati.pdf', 'I');
    }

    public function actionExportExcel()
    {
        $clients = $this->getExportClients();
        $content = $this->renderClientExportXlsx($clients);
        $fileName = 'mijozlar-royxati-' . date('Y-m-d') . '.xlsx';

        Yii::$app->response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        return $content;
    }

    private function getExportClients()
    {
        $clientSearch = Yii::$app->request->get('ClientSearch', []);
        $hasFilter = false;

        foreach ($clientSearch as $value) {
            if ($value !== null && $value !== '') {
                $hasFilter = true;
                break;
            }
        }

        if (!$hasFilter) {
            $ids = Yii::$app->request->get('ids');
            if ($ids) {
                $ids = array_filter(array_map('intval', explode(',', $ids)));
                if ($ids) {
                    return Client::find()
                        ->where(['id' => $ids])
                        ->orderBy([new Expression('FIELD(id, ' . implode(',', $ids) . ')')])
                        ->all();
                }
            }
        }

        $searchModel = new ClientSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;

        return $dataProvider->getModels();
    }

    private function renderClientExportTable($clients, $title)
    {
        $rows = '';
        $i = 1;
        foreach ($clients as $client) {
            $workerName = '';
            if ($client->worker_user_id && $client->workerUser) {
                $workerName = trim($client->workerUser->surname . ' ' . $client->workerUser->name);
            }

            $rows .= '<tr>'
                . '<td style="border:1px solid #000;text-align:center;">' . $i . '</td>'
                . '<td style="border:1px solid #000;">' . Html::encode($client->fio) . '</td>'
                . '<td style="border:1px solid #000;">' . Html::encode($client->phone) . '</td>'
                . '<td style="border:1px solid #000;">' . Html::encode($workerName) . '</td>'
                . '</tr>';
            $i++;
        }

        return '<h3 style="text-align:center;">' . Html::encode($title) . '</h3>'
            . '<table style="width:100%;border-collapse:collapse;font-size:12px;">'
            . '<thead><tr>'
            . '<th style="border:1px solid #000;width:40px;">#</th>'
            . '<th style="border:1px solid #000;">Mijoz FIO</th>'
            . '<th style="border:1px solid #000;">Telefon nomer</th>'
            . '<th style="border:1px solid #000;">Mas\'ul xodim</th>'
            . '</tr></thead><tbody>'
            . $rows
            . '</tbody></table>';
    }

    private function renderClientExportXlsx($clients)
    {
        $tmpFile = tempnam(Yii::getAlias('@runtime'), 'clients_xlsx_');
        $zip = new \ZipArchive();
        $zip->open($tmpFile, \ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Mijozlar" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            . '</styleSheet>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->renderClientExportWorksheet($clients));
        $zip->close();

        $content = file_get_contents($tmpFile);
        @unlink($tmpFile);
        return $content;
    }

    private function renderClientExportWorksheet($clients)
    {
        $rows = '<row r="1">'
            . $this->xlsxTextCell('A1', '#')
            . $this->xlsxTextCell('B1', 'Mijoz FIO')
            . $this->xlsxTextCell('C1', 'Telefon nomer')
            . $this->xlsxTextCell('D1', 'Mas\'ul xodim')
            . '</row>';

        $rowNumber = 2;
        $i = 1;
        foreach ($clients as $client) {
            $workerName = '';
            if ($client->worker_user_id && $client->workerUser) {
                $workerName = trim($client->workerUser->surname . ' ' . $client->workerUser->name);
            }

            $rows .= '<row r="' . $rowNumber . '">'
                . '<c r="A' . $rowNumber . '"><v>' . $i . '</v></c>'
                . $this->xlsxTextCell('B' . $rowNumber, $client->fio)
                . $this->xlsxTextCell('C' . $rowNumber, $client->phone)
                . $this->xlsxTextCell('D' . $rowNumber, $workerName)
                . '</row>';
            $rowNumber++;
            $i++;
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<cols><col min="1" max="1" width="8" customWidth="1"/><col min="2" max="2" width="35" customWidth="1"/><col min="3" max="3" width="24" customWidth="1"/><col min="4" max="4" width="30" customWidth="1"/></cols>'
            . '<sheetData>' . $rows . '</sheetData>'
            . '</worksheet>';
    }

    private function xlsxTextCell($cell, $value)
    {
        return '<c r="' . $cell . '" t="inlineStr"><is><t>' . htmlspecialchars((string)$value, ENT_QUOTES | ENT_XML1, 'UTF-8') . '</t></is></c>';
    }

    public function actionClientKeshbekHisob($id)
    {
        $client = Client::findOne($id);

        // 1. POST bo‘lsa: hisobla va sessionga yoz
        if (Yii::$app->request->isPost) {
            $startDate = Yii::$app->request->post('start_date');
            $endDate = Yii::$app->request->post('end_date');

            $query = \app\models\OrderAccountHistory::find()
                ->where(['client_id' => $id])
                ->andWhere(['or',
        ['!=', 'is_worker', 1],
        ['is', 'is_worker', null]
    ]);


            if ($startDate && $endDate) {
                $query->andWhere(['between', 'cr_date', $startDate, $endDate]);
            }

            Yii::$app->session->setFlash('totalProductSum', $query->sum('all_product_sum'));
            Yii::$app->session->setFlash('totalSummDollar', $query->sum('all_summ_dollar'));
            Yii::$app->session->setFlash('startDate', $startDate);
            Yii::$app->session->setFlash('endDate', $endDate);

            return $this->redirect(['client/client-keshbek-hisob', 'id' => $id]);
        }

        // 2. GET bo‘lsa: sessiondan o‘qib ol
        $totalProductSum = Yii::$app->session->getFlash('totalProductSum');
        $totalSummDollar = Yii::$app->session->getFlash('totalSummDollar');
        $startDate = Yii::$app->session->getFlash('startDate');
        $endDate = Yii::$app->session->getFlash('endDate');

        return $this->render('client_keshbek_hisob', [
            'client_id' => $id,
            'clients' => $client,
            'totalProductSum' => $totalProductSum,
            'totalSummDollar' => $totalSummDollar,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }


    /**
     * Displays a single Client model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {   
        $request = Yii::$app->request;
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                    'title'=> "Client #".$id,
                    'content'=>$this->renderAjax('view', [
                        'model' => $this->findModel($id),
                    ]),
                    'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                            Html::a('Edit',['update','id'=>$id],['class'=>'btn btn-primary','role'=>'modal-remote'])
                ];    
        }else{
            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
        }
    }
    public function actionDistricts($id)
    {
        $datas = Regions::find()->where(['id' => $id])->one();
        $district = Districts::find()->where(['region_id' => $datas->id])->all();
        foreach ($district as $value) {
            echo "<option value = '".$value->id."'>".$value->name."</option>" ;
        }
    }
    /**
     * Creates a new Client model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
        public function actionCreateOne()
    {
        $request = Yii::$app->request;
        $model = new Client();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->isGet) {
                return [
                    'title'   => "Mijoz qo'shish",
                    'content' => $this->renderAjax('create_one', [
                        'model' => $model,
                    ]),
                    'footer'  =>
                        Html::button('Yopish', [
                            'class' => 'btn btn-default pull-left',
                            'data-dismiss' => 'modal',
                        ]) .
                        Html::button('Saqlash', [
                            'class' => 'btn btn-primary',
                            'type'  => 'submit',
                        ]),
                ];
            }

            if ($model->load($request->post()) && $model->validate()) {
                $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
                try {
                    $model->save(false);

                    $exchangeRate = ExchangeRate::find()->orderBy(['id' => SORT_ASC])->one();

                    $orderAccountCr = new OrderAccount();
                    $orderAccountCr->client_id = $model->id;
                    $orderAccountCr->last_order_date = date('Y-m-d');
                    $orderAccountCr->total_debt = $model->total_debt ?: 0;
                    $orderAccountCr->date = date('Y-m-d');
                    $orderAccountCr->exchange_rate = $exchangeRate ? $exchangeRate->dollar : 0;
                    $orderAccountCr->number_of_orders = 1;
                    $orderAccountCr->cr_date = date('Y-m-d');
                    $orderAccountCr->cr_date_time = date('Y-m-d H:i:s');
                    $orderAccountCr->save(false);

                    $transaction->commit();

                    return [
                        'forceClose' => true,
                        'message'    => 'Mijoz muvaffaqiyatli qo‘shildi',
                    ];
                } catch (\Throwable $e) {
                    $transaction->rollBack();

                    return [
                        'title'   => "Mijoz qo'shish",
                        'content' => '<div class="alert alert-danger">' . $e->getMessage() . '</div>' .
                            $this->renderAjax('create_one', [
                                'model' => $model,
                            ]),
                        'footer'  =>
                            Html::button('Yopish', [
                                'class' => 'btn btn-default pull-left',
                                'data-dismiss' => 'modal',
                            ]) .
                            Html::button('Saqlash', [
                                'class' => 'btn btn-primary',
                                'type'  => 'submit',
                            ]),
                    ];
                }
            }

            return [
                'title'   => "Mijoz qo'shish",
                'content' => $this->renderAjax('create_one', [
                    'model' => $model,
                ]),
                'footer'  =>
                    Html::button('Yopish', [
                        'class' => 'btn btn-default pull-left',
                        'data-dismiss' => 'modal',
                    ]) .
                    Html::button('Saqlash', [
                        'class' => 'btn btn-primary',
                        'type'  => 'submit',
                    ]),
            ];
        }

        if ($model->load($request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create_one', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new Client();  

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> "Qo'shish",
                    'content'=>$this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Yopish',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Saqlash',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
            }else if($model->load($request->post()) && $model->validate() && $model->save(false)){
                $model->save(false);

                $exchangeRate = ExchangeRate::find()->orderBy(['id' => SORT_ASC])->one();
                $orderAccountCr = new OrderAccount();
                $orderAccountCr->client_id = $model->id;
                $orderAccountCr->last_order_date = date('Y-m-d');
                $orderAccountCr->total_debt = $model->total_debt;
                $orderAccountCr->date = date('Y-m-d');
                $orderAccountCr->exchange_rate = $exchangeRate->dollar;
                $orderAccountCr->number_of_orders = 1;
                $orderAccountCr->cr_date = date('Y-m-d');
                $orderAccountCr->cr_date_time = date('Y-m-d H:i:s');
                $orderAccountCr->save(false);
                
                return [
                    'forceReload'=>'#crud-datatable-pjax',
                    'title'=> "Qo'shish",
                    'content'=>'<span class="text-success">Model muvaffaqiyatini yaratildi</span>',
                    'footer'=> Html::button('Yopish',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                            Html::a('Koʻproq yaratish',['create'],['class'=>'btn btn-primary','role'=>'modal-remote'])
        
                ];         
            }else{           
                return [
                    'title'=> "Qo'shish",
                    'content'=>$this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Yopish',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Saqlash',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
            }
        }else{
            /*
            *   Process for non-ajax request
            */
            if ($model->load($request->post()) && $model->save(false)) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
       
    }

    /**
     * Updates an existing Client model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);       

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> "Yangilash",
                    'content'=>$this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Yopish',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Saqlash',['class'=>'btn btn-primary','type'=>"submit"])
                ];         
            }else if($model->load($request->post()) && $model->save(false)){
                return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];  
            }else{
                 return [
                    'title'=> "Yangilash",
                    'content'=>$this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Yopish',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Saqlash',['class'=>'btn btn-primary','type'=>"submit"])
                ];        
            }
        }else{
            /*
            *   Process for non-ajax request
            */
            if ($model->load($request->post()) && $model->save(false)) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Delete an existing Client model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $request = Yii::$app->request;
        $this->findModel($id)->delete();

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }


    }

     /**
     * Delete multiple existing Client model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionBulkDelete()
    {        
        $request = Yii::$app->request;
        $pks = explode(',', $request->post( 'pks' )); // Array or selected records primary keys
        foreach ( $pks as $pk ) {
            $model = $this->findModel($pk);
            $model->delete();
        }

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
       
    }

    /**
     * Finds the Client model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Client the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Client::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
