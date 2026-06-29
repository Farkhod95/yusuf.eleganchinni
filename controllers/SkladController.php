<?php

namespace app\controllers;

use Yii;
use app\models\Sklad;
use app\models\SkladSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;
use app\models\Warehouse;
use app\models\WarehouseHistory;
use yii\filters\AccessControl;
use app\models\ElegantHistoryUpdate;
use app\models\Consignor;
use app\models\MyTotalDebt;
use app\models\PriceProduct;
use app\models\MyTotalDebtHistory;
use app\models\Prices;
use app\models\Brands;
use app\models\ProductCategory;
use app\models\BrandsSize;
/**
 * SkladController implements the CRUD actions for Sklad model.
 */
class SkladController extends Controller
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
                        'actions' => ['all-list'],
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
     * Lists all Sklad models.
     * @return mixed
     */
    public function actionIndex()
    {    
        $searchModel = new SkladSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionProducts($id)
    {    
        $searchModel2 = new SkladSearch(['consignor_id' => $id]);
        $dataProvider = $searchModel2->search2(Yii::$app->request->queryParams);

        return $this->render('index2', [
            'searchModel2' => $searchModel2,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionImport()
    {    
        $orders = Sklad::find()->where(['!=', 'actived', 0])->select(['cr_date'])->groupBy(['cr_date'])->orderBy(['cr_date'=>SORT_DESC])->all();
        return $this->render('import', ['orders' => $orders]);
    }

    public function actionImportOrder($cr_date)
    {    
        $orders = Sklad::find()->andWhere(['!=', 'actived', 0])->andWhere(['cr_date' => $cr_date])->orderBy(['cr_date'=>SORT_DESC])->all();
        $sum_dollars = 0;
        $given_sum_dollars = 0;
        $discount_amounts = 0;
        foreach ($orders as $value) {
            $sum_dollars = $sum_dollars + $value['sum_dollar'];
            $given_sum_dollars = $given_sum_dollars + $value['given_sum_dollar'];
            $discount_amounts = $discount_amounts + $value['discount_amount'];
        }

        $debtRepayments = MyTotalDebtHistory::find()->where(['cr_date' => $cr_date])->orderBy(['cr_date'=>SORT_DESC])->all();
        $deb_all_summ_dollars = 0;
        foreach ($debtRepayments as $value) {
            $deb_all_summ_dollars = $deb_all_summ_dollars + $value['all_summ_dollar'];
        }

        return $this->render('import_order', [
            'orders' => $orders, 
            'cr_date' => $cr_date, 

            'sum_dollars' => $sum_dollars, 
            'given_sum_dollars' => $given_sum_dollars, 
            'discount_amounts' => $discount_amounts, 

            'debtRepayments' => $debtRepayments, 
            'deb_all_summ_dollars' => $deb_all_summ_dollars, 
        ]);
    }
    
    public function actionImportView($id, $cr_date)
    {    
        $model = $this->findModel($id); 
        $warehouse = WarehouseHistory::find()->where(['sklad_id' => $id])->select(['brand_id'])->groupBy(['brand_id'])->all();
        $myTotalDebt = MyTotalDebt::find()->where(['consignor_id' => $model->consignor_id])->one();
        return $this->render('import_view', ['warehouse' => $warehouse, 'cr_date' => $cr_date, 'id' => $id, 'model' => $model, 'myTotalDebt' => $myTotalDebt]);
    }

    public function actionSkladView($id, $cr_date)
    {    
        $model = $this->findModel($id); 
        $warehouse = WarehouseHistory::find()->where(['sklad_id' => $id])->select(['brand_id'])->groupBy(['brand_id'])->all();
        $myTotalDebt = MyTotalDebt::find()->where(['consignor_id' => $model->consignor_id])->one();
        return $this->render('sklad_view', ['warehouse' => $warehouse, 'cr_date' => $cr_date, 'id' => $id, 'model' => $model, 'myTotalDebt' => $myTotalDebt]);
    }   

    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $mpdf = new \Mpdf\Mpdf(['format' => 'A4',]);
        $mpdf->WriteHTML($model->getInvoiceHtmlText($model, $id));
        
        //call watermark content and image
        $mpdf->SetWatermarkText('');
        $mpdf->showWatermarkText = true;
        $mpdf->watermarkTextAlpha = 0.1;

        //save the file put which location you need folder/filname
        $mpdf->Output("ClientOrderList.pdf", 'F');
        //out put in browser below output function
        $mpdf->Output();
    }
    
    public function actionSkladView2($id, $cr_date)
    {    
        $warehouse = WarehouseHistory::find()->where(['sklad_id' => $id])->select(['brand_id'])->groupBy(['brand_id'])->all();
        return $this->render('sklad_view2', ['warehouse' => $warehouse, 'cr_date' => $cr_date, 'id' => $id]);
    }
    /**
     * Displays a single Sklad model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {   
        $request = Yii::$app->request;
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                    'title'=> "Sklad #".$id,
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

    public function actionQarzs($id){
        $request = Yii::$app->request;
        // $client_id = $request->get('client_id');
        $myTotalDebt = MyTotalDebt::find()->where(['consignor_id' => $id])->one();
        
        $qarz_sum = 0;
        if ($myTotalDebt) {
            $qarz_sum = $myTotalDebt->total_debt;
        }
        return $qarz_sum;
    }

    public function actionQarzss($id=17){
        $request = Yii::$app->request;
        // $client_id = $request->get('client_id');
        $myTotalDebt = MyTotalDebt::find()->where(['consignor_id' => $id])->one();
        
        $qarz_sum = 0;
        if ($myTotalDebt) {
            $qarz_sum = $myTotalDebt->total_debt;
        }
        return $qarz_sum;
    }

    public function actionImportCheck($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id); 
        $model->status = 2;
        $model->save(false);

        // $priceProducts = PriceProduct::find()->all();

        // foreach ($priceProducts as $priceProduct) {
        //     // Warehouse jadvaliga yangi yozuv qo'shish
        //     $warehouse = new Warehouse();
        //     // $warehouse->id = $priceProduct->id;
        //     $warehouse->brand_id = $priceProduct->brand_id;
        //     $warehouse->product_category_id = $priceProduct->product_category_id;
        //     $warehouse->size = $priceProduct->size;
        //     $warehouse->price = $priceProduct->real_price;
        //     $warehouse->count = 1000;
        //     $warehouse->cr_date = $priceProduct->cr_date;
        //     $warehouse->type = $priceProduct->type;
            
        //     // Qo'shimcha ustunlarga qiymat berish
        //     $warehouse->created_by = 'admin'; // Misol uchun, admin deb belgilayapmiz
        //     $warehouse->update_by = 'admin';
        //     $warehouse->consignor_id = 1; // Yuk jo'natuvchi ID sini 1 deb belgilayapmiz
        //     $warehouse->all_sum_dollar = 0; // Boshlang'ich qiymat
        //     $warehouse->all_discount_amount = 0; // Boshlang'ich qiymat
        //     $warehouse->all_my_total_debt = 0; // Boshlang'ich qiymat
        //     $warehouse->comment = 'PriceProduct dan kiritildi';

        //     // Warehouse jadvaliga saqlash
        //     $warehouse->save(false);
        // }

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
     * Creates a new Sklad model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Sklad();
        
        if ($model->load(Yii::$app->request->post()) ) {
            $post = Yii::$app->request->post('Sklad', []);
            $importProducts = isset($post['allValue']) ? $post['allValue'] : [];
            $dates = isset($post['dates']) ? $post['dates'] : null;
            $exchange_rates = isset($post['exchange_rates']) ? $post['exchange_rates'] : 0;
            $comments = isset($post['comments']) ? trim($post['comments']) : '';

            if (!$dates || strtotime($dates) === false || !is_array($importProducts) || empty($importProducts)) {
                Yii::$app->session->setFlash('error', 'Mahsulot va sanani kiriting.');
                return $this->render('create', ['model' => $model]);
            }

            if (!is_numeric($exchange_rates) || (float)$exchange_rates <= 0) {
                Yii::$app->session->setFlash('error', 'Dollar kursini togri kiriting.');
                return $this->render('create', ['model' => $model]);
            }

            $normalizedProducts = [];
            $seenProducts = [];
            foreach ($importProducts as $value) {
                $brandId = isset($value['brand_id']) ? (int)$value['brand_id'] : 0;
                $categoryId = isset($value['product_category_id']) ? (int)$value['product_category_id'] : 0;
                $size = isset($value['size']) ? (float)$value['size'] : 0;
                $type = isset($value['type']) ? (int)$value['type'] : 0;
                $price = isset($value['price']) ? (float)$value['price'] : -1;

                $brand = \app\models\Brands::find()->where(['id' => $brandId])->one();
                $category = \app\models\ProductCategory::find()
                    ->where(['id' => $categoryId])
                    ->andWhere(['<>', 'sup_status', 0])
                    ->one();

                if (!$brand || !$category || !$type || $price < 0) {
                    Yii::$app->session->setFlash('error', 'Mahsulot malumotlari notogri.');
                    return $this->render('create', ['model' => $model]);
                }

                $key = implode(':', [$brandId, $categoryId, $size, $type]);
                if (isset($seenProducts[$key])) {
                    Yii::$app->session->setFlash('error', 'Bir xil mahsulot takror kiritilgan.');
                    return $this->render('create', ['model' => $model]);
                }
                $seenProducts[$key] = true;

                $normalizedProducts[] = [
                    'brand_id' => $brandId,
                    'product_category_id' => $categoryId,
                    'size' => $size,
                    'type' => $type,
                    'price' => $price,
                ];
            }

            $transaction = Yii::$app->db->beginTransaction(\yii\db\Transaction::SERIALIZABLE);
            try {
                $sklad = new Sklad();
                $sklad->created_by = Yii::$app->user->identity->id;
                $sklad->comment = $comments;
                $sklad->given_sum_dollar = 0;
                $sklad->sum_dollar = 0;
                $sklad->exchange_rate = (float)$exchange_rates;
                $sklad->discount_amount = 0;
                $sklad->my_total_debt = 0;
                $sklad->cr_date_time = date('Y-m-d H:i:s');
                $sklad->cr_date = date('Y-m-d', strtotime($dates));
                $sklad->consignor_id = null;
                $sklad->status = 1;
                $sklad->actived = 0;
                if (!$sklad->save(false)) {
                    throw new \RuntimeException('Sklad saqlanmadi.');
                }

                foreach ($normalizedProducts as $value) {
                    $warehouse = Warehouse::find()
                        ->andWhere(['brand_id' => $value['brand_id']])
                        ->andWhere(['product_category_id' => $value['product_category_id']])
                        ->andWhere(['type' => $value['type']])
                        ->andWhere(['size' => $value['size']])
                        ->one();

                    if (!$warehouse) {
                        $warehouse = new Warehouse();
                        $warehouse->brand_id = $value['brand_id'];
                        $warehouse->product_category_id = $value['product_category_id'];
                        $warehouse->size = $value['size'];
                        $warehouse->count = 0;
                        $warehouse->type = $value['type'];
                        $warehouse->all_my_total_debt = 0;
                        $warehouse->all_sum_dollar = 0;
                        $warehouse->all_discount_amount = 0;
                        $warehouse->cr_date = date('Y-m-d', strtotime($dates));
                    }

                    $warehouse->price = $value['price'];
                    if (!$warehouse->save(false)) {
                        throw new \RuntimeException('Mahsulot saqlanmadi.');
                    }

                    $relativeHistory = new WarehouseHistory();
                    $relativeHistory->sklad_id = $sklad->id;
                    $relativeHistory->brand_id = $value['brand_id'];
                    $relativeHistory->product_category_id = $value['product_category_id'];
                    $relativeHistory->size = $value['size'];
                    $relativeHistory->type = $value['type'];
                    $relativeHistory->price = $value['price'];
                    $relativeHistory->count = 0;
                    $relativeHistory->cr_date = date('Y-m-d H:i:s');
                    $relativeHistory->cr_date_time = date('Y-m-d H:i:s');
                    if (!$relativeHistory->save(false)) {
                        throw new \RuntimeException('Mahsulot tarixi saqlanmadi.');
                    }

                    $price = Prices::find()->where(['warehouse_id' => $warehouse->id])->one();
                    if (!$price) {
                        $price = new Prices();
                        $price->warehouse_id = $warehouse->id;
                    }
                    $price->price = $value['price'];
                    if (!$price->save(false)) {
                        throw new \RuntimeException('Narx saqlanmadi.');
                    }
                }

                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }

            return $this->redirect(['warehouse/index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionCreateProduct()
    {
        $model = new Sklad();
        
        if ($model->load(Yii::$app->request->post()) ) {
            $post = Yii::$app->request->post();
            
            $importProducts = $post['Sklad']['allValue'];
           
            $dates = $post['Sklad']['dates'];
            $exchange_rates = $post['Sklad']['exchange_rates'];
            $given_sum_dollars = 0;
            $sum_dollars = 0;
            $discount_amounts = 0;
            $comments = $post['Sklad']['comments'];
            
            $sklad = new Sklad();
            $sklad->created_by = Yii::$app->user->identity->id;
            $sklad->comment = $comments;
            $sklad->given_sum_dollar = 0;
            $sklad->sum_dollar = $sum_dollars;
            $sklad->exchange_rate = $exchange_rates;
            $sklad->discount_amount = $discount_amounts;
            $sklad->my_total_debt = 0;
            $sklad->cr_date_time = date('Y-m-d H:i:s');
            $sklad->cr_date = date('Y-m-d',strtotime($dates));
            $sklad->status = 1;
            $sklad->actived = 0;
            $sklad->save(false);


            foreach ($importProducts as $value) {
                

                $warehouse = Warehouse::find()
                                ->andWhere(['brand_id' => (int)$value['brand_id']])
                                ->andWhere(['product_category_id' => (int)$value['product_category_id']])
                                ->andWhere(['type' => $value['type']])
                                ->andWhere(['size' => (float)$value['size']])->one();
                
                if (!$warehouse) {
                    $relative = new Warehouse();
                    $relative->brand_id = $value['brand_id'];
                    $relative->product_category_id = $value['product_category_id'];
                    $relative->size = $value['size'];
                    $relative->count = 0;
                    $relative->price = $value['price'];
                    $relative->type = $value['type'];

                    $relative->all_my_total_debt = 0;
                    $relative->all_sum_dollar = 0;
                    $relative->all_discount_amount = 0;

                    $relative->cr_date = date('Y-m-d',strtotime($dates));
                    $relative->save(false);

                    $relativeHistory = new WarehouseHistory();
                    $relativeHistory->sklad_id = $sklad->id;
                    $relativeHistory->brand_id = $value['brand_id'];
                    $relativeHistory->product_category_id = $value['product_category_id'];
                    $relativeHistory->size = $value['size'];
                    $relativeHistory->type = $value['type'];
                    $relativeHistory->price = $value['price'];
                    $relativeHistory->count = 0;
                    $relativeHistory->cr_date = date('Y-m-d H:i:s');
                    $relativeHistory->cr_date_time = date('Y-m-d H:i:s');
                    $relativeHistory->save(false);
                    $error = $relativeHistory->errors;

                    $modelPrices = new Prices();  
                    $modelPrices->warehouse_id = $relative->id;
                    $modelPrices->price = $value['price'];
                    $modelPrices->save();

                }else {
                    $warehouse->price = $value['price'];
                    $warehouse->all_my_total_debt = 0;
                    $warehouse->all_sum_dollar =0;
                    $warehouse->all_discount_amount = 0;
                    $warehouse->count = $warehouse->count + 0;
                    $warehouse->save(false);
                    // echo '<pre>';
                    // print_r($value['price']);
                    // echo '</pre>';

                    $relativeHistory = new WarehouseHistory();
                    $relativeHistory->sklad_id = $sklad->id;
                    $relativeHistory->brand_id = $value['brand_id'];
                    $relativeHistory->product_category_id = $value['product_category_id'];
                    $relativeHistory->size = $value['size'];
                    $relativeHistory->count = 0;
                    $relativeHistory->type = $value['type'];
                    $relativeHistory->price = $value['price'];
                    $relativeHistory->cr_date = date('Y-m-d H:i:s');
                    $relativeHistory->save(false);
                    $error = $relativeHistory->errors;
                    
                    $price = Prices::find()->where(['warehouse_id' => $warehouse->id])->one();
                    if ($price) {
                        $price->warehouse_id = $warehouse->id;
                        $price->price = $value['price'];
                        $price->save(false);
                    }else {
                        $modelPrices = new Prices();  
                        $modelPrices->warehouse_id = $warehouse->id;
                        $modelPrices->price = $value['price'];
                        $modelPrices->save();
                    }
                    
                }
                    
            }
            return $this->redirect(['warehouse/index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }
    /**
     * Updates an existing Sklad model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $consignor = Consignor::findOne($model->consignor_id);
        $myTotalDebt = $consignor ? MyTotalDebt::find()->where(['consignor_id' => $consignor->id])->one() : null;

        if ($model->load(Yii::$app->request->post())) {
            $post = Yii::$app->request->post('Sklad', []);

            try {
                if (!$consignor) {
                    throw new \RuntimeException('Yuk jo\'natuvchi topilmadi.');
                }

                $normalizedProducts = $this->normalizeSkladProducts(isset($post['allValue']) ? $post['allValue'] : []);
                $date = $this->normalizeSkladDate(isset($post['cr_date']) ? $post['cr_date'] : $model->cr_date);
                $exchangeRate = $this->toFloat(isset($post['exchange_rate']) ? $post['exchange_rate'] : 0);
                $sumDollar = $this->toFloat(isset($post['sum_dollar']) ? $post['sum_dollar'] : 0);
                $discountAmount = $this->toFloat(isset($post['discount_amount']) ? $post['discount_amount'] : 0);
                $updateReason = trim((string)(isset($post['comments']) ? $post['comments'] : ''));
                $carNumber = trim((string)(isset($post['car_number']) ? $post['car_number'] : ''));

                if ($date === false) {
                    throw new \RuntimeException('Sana noto\'g\'ri kiritilgan.');
                }
                if ($exchangeRate <= 0) {
                    throw new \RuntimeException('Dollar kursi 0 dan katta bo\'lishi kerak.');
                }
                if ($sumDollar < 0 || $discountAmount < 0) {
                    throw new \RuntimeException('Summa va chegirma manfiy bo\'lishi mumkin emas.');
                }
                if ($updateReason === '') {
                    throw new \RuntimeException('Izoh kiritilishi kerak.');
                }
                if ($carNumber === '') {
                    throw new \RuntimeException('Avtomobil raqami kiritilishi kerak.');
                }

                $givenSumDollar = 0;
                foreach ($normalizedProducts as $value) {
                    $givenSumDollar += $value['count'] * $value['price'];
                }
                $newDebtImpact = $givenSumDollar - ($sumDollar - $discountAmount);
                $oldDebtImpact = (float)$model->my_total_debt;
                $oldMyTotalDebt = $myTotalDebt ? (float)$myTotalDebt->total_debt : 0;

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $warehouseHistories = WarehouseHistory::find()->where(['sklad_id' => $id])->all();
                    foreach ($warehouseHistories as $history) {
                        $warehouse = Warehouse::find()
                            ->andWhere(['brand_id' => (int)$history->brand_id])
                            ->andWhere(['product_category_id' => (int)$history->product_category_id])
                            ->andWhere(['type' => (int)$history->type])
                            ->andWhere(['size' => (float)$history->size])
                            ->one();

                        if (!$warehouse) {
                            throw new \RuntimeException('Eski importdagi mahsulot ombordan topilmadi.');
                        }

                        $newCount = (int)$warehouse->count - (int)$history->count;
                        if ($newCount < 0) {
                            throw new \RuntimeException('Eski importni o\'zgartirish uchun omborda yetarli mahsulot qolmagan.');
                        }

                        $warehouse->count = $newCount;
                        if (!$warehouse->save(false)) {
                            throw new \RuntimeException('Eski mahsulot qoldig\'i yangilanmadi.');
                        }

                        if ($history->delete() === false) {
                            throw new \RuntimeException('Eski mahsulot tarixi o\'chirilmadi.');
                        }
                    }

                    foreach ($normalizedProducts as $value) {
                        $warehouse = Warehouse::find()
                            ->andWhere(['brand_id' => $value['brand_id']])
                            ->andWhere(['product_category_id' => $value['product_category_id']])
                            ->andWhere(['type' => $value['type']])
                            ->andWhere(['size' => $value['size']])
                            ->one();

                        if (!$warehouse) {
                            $warehouse = new Warehouse();
                            $warehouse->brand_id = $value['brand_id'];
                            $warehouse->product_category_id = $value['product_category_id'];
                            $warehouse->size = $value['size'];
                            $warehouse->count = 0;
                            $warehouse->type = $value['type'];
                            $warehouse->all_my_total_debt = 0;
                            $warehouse->all_sum_dollar = 0;
                            $warehouse->all_discount_amount = 0;
                            $warehouse->cr_date = $date;
                        }

                        $warehouse->count = (int)$warehouse->count + $value['count'];
                        $warehouse->price = $value['price'];
                        if (!$warehouse->save(false)) {
                            throw new \RuntimeException('Mahsulot omborga saqlanmadi.');
                        }

                        $relativeHistory = new WarehouseHistory();
                        $relativeHistory->sklad_id = $id;
                        $relativeHistory->brand_id = $value['brand_id'];
                        $relativeHistory->product_category_id = $value['product_category_id'];
                        $relativeHistory->size = $value['size'];
                        $relativeHistory->type = $value['type'];
                        $relativeHistory->price = $value['price'];
                        $relativeHistory->count = $value['count'];
                        if (!$relativeHistory->save(false)) {
                            throw new \RuntimeException('Mahsulot tarixi saqlanmadi.');
                        }

                        $price = Prices::find()->where(['warehouse_id' => $warehouse->id])->one();
                        if (!$price) {
                            $price = new Prices();
                            $price->warehouse_id = $warehouse->id;
                        }
                        $price->price = $value['price'];
                        if (!$price->save(false)) {
                            throw new \RuntimeException('Narx saqlanmadi.');
                        }
                    }

                    if (!$myTotalDebt) {
                        $myTotalDebt = new MyTotalDebt();
                        $myTotalDebt->consignor_id = $consignor->id;
                        $myTotalDebt->total_debt = 0;
                    }
                    $myTotalDebt->total_debt = $oldMyTotalDebt - $oldDebtImpact + $newDebtImpact;
                    $myTotalDebt->update_by = Yii::$app->user->identity->id;
                    $myTotalDebt->cr_date = date('Y-m-d H:i:s');
                    if (!$myTotalDebt->save(false)) {
                        throw new \RuntimeException('Umumiy qarz saqlanmadi.');
                    }

                    $elegantHistoryUpdate = new ElegantHistoryUpdate();
                    $elegantHistoryUpdate->title = $consignor->name . " dan olingan mahsulotlar " . Yii::$app->formatter->asDatetime(date('Y-m-d'), 'php:d.m.Y ') . " sanada o'zgartirildi...";
                    $elegantHistoryUpdate->comment = $updateReason;
                    $elegantHistoryUpdate->status = 1;
                    $elegantHistoryUpdate->type = 1;
                    if (!$elegantHistoryUpdate->save(false)) {
                        throw new \RuntimeException('O\'zgartirish tarixi saqlanmadi.');
                    }

                    $model->car_number = $carNumber;
                    $model->given_sum_dollar = $givenSumDollar;
                    $model->sum_dollar = $sumDollar;
                    $model->exchange_rate = $exchangeRate;
                    $model->discount_amount = $discountAmount;
                    $model->my_total_debt = $newDebtImpact;
                    $model->old_my_total_debt = $oldMyTotalDebt - $oldDebtImpact;
                    $model->cr_date_time = date('Y-m-d H:i:s');
                    $model->cr_date = $date;
                    $model->consignor_id = $consignor->id;
                    $model->status = 1;
                    if (!$model->save(false)) {
                        throw new \RuntimeException('Import ma\'lumoti saqlanmadi.');
                    }

                    $transaction->commit();
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }

                return $this->redirect(['index']);
            } catch (\Throwable $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
                $myTotalDebt = $consignor ? MyTotalDebt::find()->where(['consignor_id' => $consignor->id])->one() : null;
            }
        }

        return $this->render('update', [
            'model' => $model,
            'my_total_debt' => $myTotalDebt ? $myTotalDebt->total_debt : 0,
        ]);
        
    }

    public function actionCategoriesByBrand($brand_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $rows = ProductCategory::find()
            ->select(['id', 'name'])
            ->where([
                'brand_id' => (int)$brand_id,
                'sup_status' => 1,
            ])
            ->orderBy(['sorting' => SORT_ASC, 'name' => SORT_ASC])
            ->asArray()
            ->all();

        $results = [];
        foreach ($rows as $row) {
            $results[] = [
                'id' => (int)$row['id'],
                'text' => $row['name'],
            ];
        }

        return ['results' => $results];
    }

    public function actionSizesTypesByCategory($brand_id, $category_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $brandId = (int)$brand_id;
        $categoryId = (int)$category_id;
        if (!$brandId || !$categoryId) {
            return ['sizes' => [], 'types' => []];
        }

        $sizeRows = BrandsSize::find()
            ->select(['size'])
            ->where(['brand_id' => $brandId, 'product_category_id' => $categoryId])
            ->distinct()
            ->orderBy(['size' => SORT_ASC])
            ->asArray()
            ->all();

        $typeRows = BrandsSize::find()
            ->select(['type'])
            ->where(['brand_id' => $brandId, 'product_category_id' => $categoryId])
            ->distinct()
            ->orderBy(['type' => SORT_ASC])
            ->asArray()
            ->all();

        return [
            'sizes' => $this->formatSizeRows($sizeRows),
            'types' => $this->formatTypeRows($typeRows),
        ];
    }

    public function actionTypesBySize($brand_id, $category_id, $size)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $rows = BrandsSize::find()
            ->select(['type'])
            ->where([
                'brand_id' => (int)$brand_id,
                'product_category_id' => (int)$category_id,
                'size' => $this->toFloat($size),
            ])
            ->distinct()
            ->orderBy(['type' => SORT_ASC])
            ->asArray()
            ->all();

        return ['types' => $this->formatTypeRows($rows)];
    }

    /**
     * Delete an existing Sklad model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $request = Yii::$app->request;

        $model = $this->findModel($id); 
        $warehouseHistories = WarehouseHistory::find()->where(['sklad_id' => $id])->all();
        foreach ($warehouseHistories as $value) {
            $warehouse = Warehouse::find()->andWhere(['brand_id' => $value->brand_id])
                                ->andWhere(['product_category_id' => $value->product_category_id])
                                ->andWhere(['type' => $value->type])
                                ->andWhere(['size' => $value->size])->one();
            $warehouse->count = $warehouse->count - $value->count;
            $warehouse->save(false);
            $value->delete();
        }
        $myTotalDebt = MyTotalDebt::find()->where(['consignor_id' => $model->consignor_id])->one();
        $myTotalDebt->total_debt = $myTotalDebt->total_debt - $model->my_total_debt;
        $myTotalDebt->save(false);

        $this->findModel($id)->delete();

        $deleteReason = Yii::$app->request->post('delete_reason');
        $elegantHistoryUpdate = new ElegantHistoryUpdate();
        $elegantHistoryUpdate->title = $model->consignor0->name . " ning ". \Yii::$app->formatter->asDatetime(date('Y-m-d'), 'php:d.m.Y ') ." sanadagi import qilingan mahsulotlar o'chirildi...";
        $elegantHistoryUpdate->comment = $deleteReason;
        $elegantHistoryUpdate->status = 2;
        $elegantHistoryUpdate->type = 1;
        $elegantHistoryUpdate->save(false);

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
     * Delete multiple existing Sklad model.
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

    private function normalizeSkladProducts($products)
    {
        if (empty($products) || !is_array($products)) {
            throw new \RuntimeException('Kamida bitta mahsulot kiritilishi kerak.');
        }

        $normalized = [];
        $seen = [];
        $rowNumber = 0;

        foreach ($products as $row) {
            $rowNumber++;
            if (!is_array($row)) {
                throw new \RuntimeException($rowNumber . "-qatordagi mahsulot noto'g'ri.");
            }

            $brandId = (int)(isset($row['brand_id']) ? $row['brand_id'] : 0);
            $categoryId = (int)(isset($row['product_category_id']) ? $row['product_category_id'] : 0);
            $size = $this->toFloat(isset($row['size']) ? $row['size'] : '');
            $type = (int)(isset($row['type']) ? $row['type'] : 0);
            $count = (int)(isset($row['count']) ? $row['count'] : 0);
            $price = $this->toFloat(isset($row['price']) ? $row['price'] : 0);

            if (!$brandId || !Brands::find()->where(['id' => $brandId, 'sup_status' => 1])->exists()) {
                throw new \RuntimeException($rowNumber . "-qatorda model noto'g'ri tanlangan.");
            }
            if (!$categoryId || !ProductCategory::find()->where([
                'id' => $categoryId,
                'brand_id' => $brandId,
                'sup_status' => 1,
            ])->exists()) {
                throw new \RuntimeException($rowNumber . "-qatorda kategoriya modelga mos emas.");
            }
            if ($size <= 0) {
                throw new \RuntimeException($rowNumber . "-qatorda o'lcham noto'g'ri.");
            }
            if (!in_array($type, [1, 3, 4], true)) {
                throw new \RuntimeException($rowNumber . "-qatorda tip noto'g'ri.");
            }
            if (!BrandsSize::find()->where([
                'brand_id' => $brandId,
                'product_category_id' => $categoryId,
                'size' => $size,
                'type' => $type,
            ])->exists()) {
                throw new \RuntimeException($rowNumber . "-qatorda model, kategoriya, o'lcham va tip bir-biriga mos emas.");
            }
            if ($count <= 0) {
                throw new \RuntimeException($rowNumber . "-qatorda soni 0 dan katta bo'lishi kerak.");
            }
            if ($price < 0) {
                throw new \RuntimeException($rowNumber . "-qatorda narx manfiy bo'lishi mumkin emas.");
            }

            $key = $brandId . ':' . $categoryId . ':' . $size . ':' . $type;
            if (isset($seen[$key])) {
                throw new \RuntimeException($rowNumber . "-qatorda bir xil mahsulot takror kiritilgan.");
            }
            $seen[$key] = true;

            $normalized[] = [
                'brand_id' => $brandId,
                'product_category_id' => $categoryId,
                'size' => $size,
                'type' => $type,
                'count' => $count,
                'price' => $price,
            ];
        }

        return $normalized;
    }

    private function normalizeSkladDate($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return false;
        }

        $date = \DateTime::createFromFormat('d.m.Y', $value);
        if ($date && $date->format('d.m.Y') === $value) {
            return $date->format('Y-m-d');
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);
        if ($date && $date->format('Y-m-d') === $value) {
            return $date->format('Y-m-d');
        }

        return false;
    }

    private function toFloat($value)
    {
        if (is_string($value)) {
            $value = str_replace(' ', '', str_replace(',', '.', trim($value)));
        }

        return is_numeric($value) ? (float)$value : 0;
    }

    private function formatSizeRows($rows)
    {
        $sizes = [];
        foreach ($rows as $row) {
            $size = (string)$row['size'];
            $sizes[] = [
                'id' => $size,
                'text' => $size,
            ];
        }

        return $sizes;
    }

    private function formatTypeRows($rows)
    {
        $types = [];
        foreach ($rows as $row) {
            $typeId = (int)$row['type'];
            $types[] = [
                'id' => $typeId,
                'text' => ProductCategory::getTypeView($typeId),
            ];
        }

        return $types;
    }

    /**
     * Finds the Sklad model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Sklad the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Sklad::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
