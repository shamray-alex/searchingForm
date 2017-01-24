<?php

namespace app\controllers;

use app\models\SearchForm;
use Yii;
use yii\data\SqlDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
    /**
     * Search a things
     *
     * @return string
     */
    public function actionSearch()
    {
        $result = [];
        $model = new SearchForm();
        if(Yii::$app->request->post('SearchForm')){
            $search_form = Yii::$app->request->post('SearchForm');
            $model->load(Yii::$app->request->post());

            $query = new Query();
            $query = $query->select('*')
                ->from('thing')
                ->where('thing.thingName like :text or thing.thingDescription like :text')
                ->andWhere('thing.thing_categoryId = :cat');

            if(isset($search_form['is_image']) && empty($search_form['is_image'])){
                $query->join('left join', 'thingimage', 'thingimage.thingimage_thingId = thing.thingId')->andWhere('thingimage.thingimageId is null');
            }else{
                $query->join('join', 'thingimage', 'thingimage.thingimage_thingId = thing.thingId');
            }

            if($model->validate()) {
                $count = Yii::$app->db->createCommand('select count(*) from thing')->queryScalar();

                $result = new SqlDataProvider([
                    'sql' => $query->createCommand()->sql,
                    'params' => [':text' => $search_form['text'], ':cat' => $search_form['meta_category']],
                    'totalCount' => $count
                ]);
            }
        }

        $list_category = \Yii::$app->db->createCommand('select * from metacategorylang where metacategorylang_languageId')->query();

        return $this->render('search', [
            'list_category' => ArrayHelper::map($list_category, 'metacategorylang_metacategoryId', 'metacategorylangName'),
            'model' => $model,
            'result' => $result
        ]);
    }
}
