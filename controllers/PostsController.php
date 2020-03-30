<?php

namespace wdmg\blog\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use wdmg\blog\models\Posts;
use wdmg\blog\models\PostsSearch;

/**
 * PostsController implements the CRUD actions for Blog model.
 */
class PostsController extends Controller
{

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
     * Lists of all Blog posts models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PostsSearch();

        if ($cat_id = Yii::$app->request->get('cat_id', null))
            $searchModel->categories = intval($cat_id);

        if ($tag_id = Yii::$app->request->get('tag_id', null))
            $searchModel->tags = intval($tag_id);

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'module' => $this->module
        ]);
    }


    /**
     * Creates a new Posts post model.
     * If creation is successful, the browser will be redirected to the list of pages.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Posts();
        $model->status = $model::POST_STATUS_DRAFT;

        // Autocomplete for tags list
        if (Yii::$app->request->isAjax && ($value = Yii::$app->request->get('value'))) {

            $response = [];
            $list = $model->getAllTags(['like', 'name', $value], ['id', 'name'], true);
            foreach ($list as $id => $item) {
                $response['tag_id:'.$id] = $item['name'];
            }

            return $this->asJson($response);
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

                // Get image thumbnail
                $image = \yii\web\UploadedFile::getInstance($model, 'file');
                if ($src = $model->upload($image))
                    $model->image = $src;

                if($model->save())
                    Yii::$app->getSession()->setFlash(
                        'success',
                        Yii::t('app/modules/blog', 'Blog post has been successfully added!')
                    );
                else
                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t('app/modules/blog', 'An error occurred while add the new post.')
                    );

                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'module' => $this->module,
            'model' => $model
        ]);

    }

    /**
     * Updates an existing Blog post model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Autocomplete for tags list
        if (Yii::$app->request->isAjax && ($value = Yii::$app->request->get('value'))) {

            $response = [];
            $list = $model->getAllTags(['like', 'name', $value], ['id', 'name'], true);
            foreach ($list as $id => $item) {
                $response['tag_id:'.$id] = $item['name'];
            }

            return $this->asJson($response);
        }

        // Get current URL before save this blog post
        $oldPostUrl = $model->getPostUrl(false);

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

                // Get new URL for saved blog post
                $newPostUrl = $model->getPostUrl(false);

                // Get image thumbnail
                $image = \yii\web\UploadedFile::getInstance($model, 'file');
                if ($src = $model->upload($image))
                    $model->image = $src;


                if ($model->save()) {

                    // Set 301-redirect from old URL to new
                    if (isset(Yii::$app->redirects) && ($oldPostUrl !== $newPostUrl) && ($model->status == $model::POST_STATUS_PUBLISHED)) {
                        // @TODO: remove old redirects
                        Yii::$app->redirects->set('blog', $oldPostUrl, $newPostUrl, 301);
                    }

                    // Log activity
                    if (
                        class_exists('\wdmg\activity\models\Activity') &&
                        $this->module->moduleLoaded('activity') &&
                        isset(Yii::$app->activity)
                    ) {
                        Yii::$app->activity->set(
                            'Blog post `' . $model->name . '` with ID `' . $model->id . '` has been successfully updated.',
                            $this->uniqueId . ":" . $this->action->id,
                            'success',
                            1
                        );
                    }

                    Yii::$app->getSession()->setFlash(
                        'success',
                        Yii::t(
                            'app/modules/blog',
                            'OK! Blog post `{name}` successfully updated.',
                            [
                                'name' => $model->name
                            ]
                        )
                    );
                } else {
                    // Log activity
                    if (
                        class_exists('\wdmg\activity\models\Activity') &&
                        $this->module->moduleLoaded('activity') &&
                        isset(Yii::$app->activity)
                    ) {
                        Yii::$app->activity->set(
                            'An error occurred while update the blog post  `' . $model->name . '` with ID `' . $model->id . '`.',
                            $this->uniqueId . ":" . $this->action->id,
                            'danger',
                            1
                        );
                    }
                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t(
                            'app/modules/blog',
                            'An error occurred while update a blog post `{name}`.',
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
     * Displays a single Blog post model.
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
     * Deletes an existing Blog post model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);
        if($model->delete()) {

            // @TODO: remove redirects of deleted pages

            // Log activity
            if (
                class_exists('\wdmg\activity\models\Activity') &&
                $this->module->moduleLoaded('activity') &&
                isset(Yii::$app->activity)
            ) {
                Yii::$app->activity->set(
                    'Blog post `' . $model->name . '` with ID `' . $model->id . '` has been successfully deleted.',
                    $this->uniqueId . ":" . $this->action->id,
                    'success',
                    1
                );
            }

            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t(
                    'app/modules/blog',
                    'OK! Blog post `{name}` successfully deleted.',
                    [
                        'name' => $model->name
                    ]
                )
            );
        } else {

            // Log activity
            if (
                class_exists('\wdmg\activity\models\Activity') &&
                $this->module->moduleLoaded('activity') &&
                isset(Yii::$app->activity)
            ) {
                Yii::$app->activity->set(
                    'An error occurred while deleting the blog post  `' . $model->name . '` with ID `' . $model->id . '`.',
                    $this->uniqueId . ":" . $this->action->id,
                    'danger',
                    1
                );
            }

            Yii::$app->getSession()->setFlash(
                'danger',
                Yii::t(
                    'app/modules/blog',
                    'An error occurred while deleting a blog post `{name}`.',
                    [
                        'name' => $model->name
                    ]
                )
            );
        }

        return $this->redirect(['index']);
    }


    /**
     * Finds the Blog post model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return blog model item
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Posts::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app/modules/blog', 'The requested blog post does not exist.'));
    }
}
