<?php

namespace wdmg\blog\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use wdmg\blog\models\Tags;
use wdmg\blog\models\TagsSearch;

/**
 * TagsController implements the CRUD actions for Blog model.
 */
class TagsController extends Controller
{

    /**
     * @var string|null Selected language (locale)
     */
    private $_locale;

    /**
     * @var string|null Selected id of source
     */
    private $_source_id;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get'],
                    'view' => ['get'],
                    'delete' => ['post'],
                    'create' => ['get', 'post'],
                    'update' => ['get', 'post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['admin'],
                        'allow' => true
                    ],
                ],
            ],
        ];

        // If auth manager not configured use default access control
        if(!Yii::$app->authManager) {
            $behaviors['access'] = [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true
                    ],
                ]
            ];
        }

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $this->_locale = Yii::$app->request->get('locale', null);
        $this->_source_id = Yii::$app->request->get('source_id', null);
        return parent::beforeAction($action);
    }

    /**
     * Lists of all Tag models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TagsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'module' => $this->module
        ]);
    }


    /**
     * Creates a new post Tag model.
     * If creation is successful, the browser will be redirected to the list of tags.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Tags();

        // No language is set for this model, we will use the current user language
        if (is_null($model->locale)) {
            if (is_null($this->_locale)) {

                $model->locale = Yii::$app->language;
                if (!Yii::$app->request->isPost) {

                    $languages = $model->getLanguagesList(false);
                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t(
                            'app/modules/blog',
                            'No display language has been set for this post tag. When saving, the current user language will be selected: {language}',
                            [
                                'language' => (isset($languages[Yii::$app->language])) ? $languages[Yii::$app->language] : Yii::$app->language
                            ]
                        )
                    );
                }
            } else {
                $model->locale = $this->_locale;
            }
        }

        if (!is_null($this->_source_id)) {
            $model->source_id = $this->_source_id;
            if ($source = $model::findOne(['id' => $this->_source_id])) {
                if ($source->id) {
                    $model->source_id = $source->id;
                }
            }
        }

        if (Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate())
                    $success = true;
                else
                    $success = false;

                return $this->asJson(['success' => $success, 'alias' => $model->alias, 'errors' => $model->errors]);
            }
        } else {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {

                if ($model->save()) {

                    // Log activity
                    $this->module->logActivity(
                        'Tag `' . $model->name . '` with ID `' . $model->id . '` has been successfully added.',
                        $this->uniqueId . ":" . $this->action->id,
                        'success',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'success',
                        Yii::t('app/modules/blog', 'Tag has been successfully added!')
                    );
                } else {

                    // Log activity
                    $this->module->logActivity(
                        'An error occurred while add the new tag: ' . $model->name,
                        $this->uniqueId . ":" . $this->action->id,
                        'danger',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t('app/modules/blog', 'An error occurred while add the new tag.')
                    );
                }

                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'module' => $this->module,
            'model' => $model
        ]);

    }

    /**
     * Updates an existing Tag model.
     * If update is successful, the browser will be redirected to the tags list.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // No language is set for this model, we will use the current user language
        if (is_null($model->locale)) {

            $model->locale = Yii::$app->language;
            if (!Yii::$app->request->isPost) {

                $languages = $model->getLanguagesList(false);
                Yii::$app->getSession()->setFlash(
                    'danger',
                    Yii::t(
                        'app/modules/blog',
                        'No display language has been set for this post tag. When saving, the current user language will be selected: {language}',
                        [
                            'language' => (isset($languages[Yii::$app->language])) ? $languages[Yii::$app->language] : Yii::$app->language
                        ]
                    )
                );
            }
        }

        // Get current URL before save this tag
        $oldTagUrl = $model->getTagUrl(false);

        if (Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate())
                    $success = true;
                else
                    $success = false;

                return $this->asJson(['success' => $success, 'alias' => $model->alias, 'errors' => $model->errors]);
            }
        } else {
            if ($model->load(Yii::$app->request->post())) {

                // Get new URL for saved tag
                $newTagUrl = $model->getTagUrl(false);

                if ($model->save()) {

                    // Set 301-redirect from old URL to new
                    if (isset(Yii::$app->redirects) && ($oldTagUrl !== $newTagUrl)) {
                        // @TODO: remove old redirects
                        Yii::$app->redirects->set('blog', $oldTagUrl, $newTagUrl, 301);
                    }

                    // Log activity
                    $this->module->logActivity(
                        'Tag `' . $model->name . '` with ID `' . $model->id . '` has been successfully updated.',
                        $this->uniqueId . ":" . $this->action->id,
                        'success',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'success',
                        Yii::t(
                            'app/modules/blog',
                            'OK! Tag `{name}` successfully updated.',
                            [
                                'name' => $model->name
                            ]
                        )
                    );

                } else {

                    // Log activity
                    $this->module->logActivity(
                        'An error occurred while update the tag  `' . $model->name . '` with ID `' . $model->id . '`.',
                        $this->uniqueId . ":" . $this->action->id,
                        'danger',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t(
                            'app/modules/blog',
                            'An error occurred while update a tag `{name}`.',
                            [
                                'name' => $model->name
                            ]
                        )
                    );

                }
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'module' => $this->module,
            'model' => $model
        ]);
    }

    /**
     * Displays a single Tag model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'module' => $this->module,
            'model' => $model
        ]);
    }

    /**
     * Deletes an existing Tag.
     * If deletion is successful, the browser will be redirected to the tags list.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if($model->delete()) {

            // Log activity
            $this->module->logActivity(
                'Tag `' . $model->name . '` with ID `' . $model->id . '` has been successfully deleted.',
                $this->uniqueId . ":" . $this->action->id,
                'success',
                1
            );

            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t(
                    'app/modules/blog',
                    'OK! Tag `{name}` successfully deleted.',
                    [
                        'name' => $model->name
                    ]
                )
            );
        } else {
            // Log activity
            $this->module->logActivity(
                'An error occurred while deleting the tag  `' . $model->name . '` with ID `' . $model->id . '`.',
                $this->uniqueId . ":" . $this->action->id,
                'danger',
                1
            );

            Yii::$app->getSession()->setFlash(
                'danger',
                Yii::t(
                    'app/modules/blog',
                    'An error occurred while deleting a tag `{name}`.',
                    [
                        'name' => $model->name
                    ]
                )
            );
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Tag model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return tag model item
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {

        if (is_null($this->_locale) && ($model = Tags::findOne($id)) !== null) {
            return $model;
        } else {
            if (($model = Tags::findOne(['source_id' => $id, 'locale' => $this->_locale])) !== null)
                return $model;
        }

        throw new NotFoundHttpException(Yii::t('app/modules/blog', 'The requested tag does not exist.'));
    }

    /**
     * Return current locale for dashboard
     *
     * @return string|null
     */
    public function getLocale() {
        return $this->_locale;
    }

    /**
     * Return current Source ID for dashboard
     *
     * @return string|null
     */
    public function getSourceId() {
        return $this->_source_id;
    }
}
